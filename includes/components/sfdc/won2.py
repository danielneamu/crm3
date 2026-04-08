import io
import os
import re
import pandas as pd
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from bs4 import BeautifulSoup
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseDownload
from google.oauth2 import service_account
from sqlalchemy import create_engine, text
from datetime import datetime

# --- CONFIGURATION ---
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
SERVICE_ACCOUNT_FILE = os.path.join(SCRIPT_DIR, 'personal-test-api.json')
SCOPES = ['https://www.googleapis.com/auth/drive.readonly']
FOLDER_ID = '18lKLBBGlfPlqKqPRR_L3PQRT9-1yWaLZ'

DB_USER = 'danielne_app'
DB_PASS = 'Piedone1976!!'
DB_HOST = 'localhost'
DB_NAME = 'danielne_crm3'
TABLE_NAME = 'sfdc_won'

# Gmail SMTP Config
GMAIL_USER = 'danielneamu@gmail.com'
GMAIL_APP_PASS = 'gerehxqmffrrczih' # Paste your App Password without spaces
NOTIFY_EMAIL = 'danielneamu@gmail.com'

def get_db_engine():
    return create_engine(f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}")

def get_drive_service():
    creds = service_account.Credentials.from_service_account_file(
        SERVICE_ACCOUNT_FILE, scopes=SCOPES)
    return build('drive', 'v3', credentials=creds)

def send_notification(subject, body):
    try:
        msg = MIMEMultipart()
        msg['From'] = GMAIL_USER
        msg['To'] = NOTIFY_EMAIL
        msg['Subject'] = subject
        msg.attach(MIMEText(body, 'plain'))

        server = smtplib.SMTP_SSL('smtp.gmail.com', 465)
        server.login(GMAIL_USER, GMAIL_APP_PASS)
        server.send_message(msg)
        server.quit()
    except Exception as e:
        print(f"Failed to send email: {e}")

def download_from_drive(service, file_name):
    query = f"name='{file_name}' and '{FOLDER_ID}' in parents and trashed=false"
    results = service.files().list(q=query, orderBy="createdTime desc", pageSize=1).execute()
    files = results.get('files', [])
    if not files: return None

    buffer = io.BytesIO()
    request = service.files().get_media(fileId=files[0]['id'])
    downloader = MediaIoBaseDownload(buffer, request)
    done = False
    while not done:
        _, done = downloader.next_chunk()
    buffer.seek(0)
    return buffer

def extract_links(buffer):
    soup = BeautifulSoup(buffer, 'html.parser')
    links_map = {}
    for a in soup.find_all('a', href=True):
        match = re.search(r'(006[a-zA-Z0-9]{12,15})', a['href'])
        if match:
            links_map[a.get_text().strip()] = f"https://onesf.lightning.force.com/lightning/r/{match.group(1)}/view"
    return links_map

def clean_currency(value):
    if pd.isna(value) or not isinstance(value, str): return value
    clean_val = value.upper().replace('EUR', '').replace('.', '').replace(',', '.').strip()
    try: return float(clean_val)
    except ValueError: return 0.0

def reconcile_and_sync(df, engine):
    if df.empty: return

    # Clean incoming columns
    new_cols = []
    for c in df.columns:
        clean = re.sub(r'[\s/:]+', '_', c.strip())
        clean = re.sub(r'[^a-zA-Z0-9_]', '', clean)
        new_cols.append(clean)
    df.columns = new_cols

    changes_detected = []

    with engine.begin() as conn:
        # Load into staging
        df.to_sql('staging_won', conn, if_exists='replace', index=False)
        
        # Get DB column list
        result = conn.execute(text(f"SHOW COLUMNS FROM {TABLE_NAME}"))
        db_cols = [row[0] for row in result]
        
        # Columns to sync from HTML (Core fields)
        # Excludes: id, Type, Revised_AOV, Revised_NPV
        core_cols = [c for c in df.columns if c in db_cols]
        
        # Build Update SQL (protects manual fields)
        update_clause = ", ".join([f"`{c}` = VALUES(`{c}`)" for c in core_cols if c != 'Opportunity_Reference_ID'])

        # Identify new rows via Count Comparison on OppID + Product + Amount
        # (This handles the identical rows scenario)
        sync_query = f"""
            INSERT INTO {TABLE_NAME} ({', '.join([f'`{c}`' for c in core_cols])})
            SELECT {', '.join([f's.`{c}`' for c in core_cols])}
            FROM staging_report s
            ON DUPLICATE KEY UPDATE {update_clause}
        """
        # Note: Because we use Auto-Increment ID, strict DUPLICATE KEY 
        # usually fails unless we have a unique index. 
        # Since we want to allow identical rows, we use an INSERT logic:
        
        # 1. Fetch existing counts to identify if we need to add a row
        # (Simplified for 2k rows: we use the OppID+Product+Amount count check)
        
        # For simplicity and manual column safety, we use the Staging -> Main transfer
        # but avoid overwriting the manual columns
        conn.execute(text(f"INSERT INTO {TABLE_NAME} ({', '.join([f'`{c}`' for c in core_cols])}) "
                          f"SELECT {', '.join([f'`{c}`' for c in core_cols])} FROM staging_won s "
                          f"WHERE NOT EXISTS (SELECT 1 FROM {TABLE_NAME} m "
                          f"WHERE m.Opportunity_Reference_ID = s.Opportunity_Reference_ID "
                          f"AND m.Product = s.Product "
                          f"AND m.Annual_Order_Value_Multi = s.Annual_Order_Value_Multi)"))
        
        conn.execute(text("DROP TABLE staging_won"))

def main():
    start_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    service = get_drive_service()
    engine = get_db_engine()

    buffer = download_from_drive(service, 'won.html')
    if not buffer:
        print(f"[{start_time}] Error: won.html not found.")
        return

    df = pd.read_html(buffer)[0]
    print(f"[{start_time}] Starting Won Sync: Processing {len(df)} rows...")

    # Date conversion
    for col in df.columns:
        if 'Date' in col:
            df[col] = pd.to_datetime(df[col], dayfirst=True, errors='coerce').dt.strftime('%Y-%m-%d')
            df[col] = df[col].replace('NaT', None)

    # Link Mapping
    buffer.seek(0)
    links = extract_links(buffer)
    if 'Opportunity Name' in df.columns:
        df['Link'] = df['Opportunity Name'].str.strip().map(links)

    # Currency Cleaning
    curr_cols = ['Annual Order Value Multi', 'Product Annual Recurring Order Value', 'Product TCV']
    for col in curr_cols:
        if col in df.columns:
            df[col] = df[col].apply(clean_currency)

    # Sync
    reconcile_and_sync(df, engine)
    
    end_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{end_time}] Process Complete: Data synced and manual columns protected.")

if __name__ == "__main__":
    main()