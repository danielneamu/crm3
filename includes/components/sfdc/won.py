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
GMAIL_APP_PASS = 'gerehxqmffrrczih'
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
    results = service.files().list(
        q=query, orderBy="createdTime desc", pageSize=1).execute()
    files = results.get('files', [])
    if not files:
        return None

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
            links_map[a.get_text().strip()] = (
                f"https://onesf.lightning.force.com/lightning/r/{match.group(1)}/view"
            )
    return links_map


def clean_currency(value):
    if pd.isna(value):
        return None

    if isinstance(value, (int, float)):
        return float(value)

    if not isinstance(value, str):
        return None

    clean_val = value.upper().replace('EUR', '').replace(' ', '').strip()

    if ',' in clean_val and '.' in clean_val:
        clean_val = clean_val.replace('.', '').replace(',', '.')
    else:
        clean_val = clean_val.replace(',', '.')

    try:
        return float(clean_val)
    except ValueError:
        return None


def parse_npv_from_description(value):
    if pd.isna(value) or not isinstance(value, str):
        return None
    text = value.strip()
    if not text:
        return None

    # Handle decimals first: 46.78, 1.742,40
    raw = text.replace(' ', '')
    if '.' in raw or ',' in raw:
        raw = raw.replace('.', '')  # Remove dots as thousand sep
        raw = raw.replace(',', '.')  # Comma as decimal
    try:
        return float(raw)
    except ValueError:
        return None


# Usage (your new block):
if npv_source in df.columns:
    parsed_npv = df[npv_source].apply(parse_npv_from_description)
    df['Revised NPV'] = pd.to_numeric(parsed_npv, errors='coerce')


def apply_revised_defaults(df):
    aov_source = 'Product Annual Recurring Order Value'
    npv_source = 'Description'

    if 'Revised AOV' not in df.columns:
        df['Revised AOV'] = pd.Series([None] * len(df), dtype='float64')
    else:
        df['Revised AOV'] = pd.to_numeric(df['Revised AOV'], errors='coerce')

    if 'Revised NPV' not in df.columns:
        df['Revised NPV'] = pd.Series([None] * len(df), dtype='float64')
    else:
        df['Revised NPV'] = pd.to_numeric(df['Revised NPV'], errors='coerce')

    if aov_source in df.columns:
        df[aov_source] = pd.to_numeric(df[aov_source], errors='coerce')
        revised_aov = df['Revised AOV'].copy()
        mask_aov = revised_aov.isna() | (revised_aov == 0)
        revised_aov = revised_aov.where(~mask_aov, df[aov_source])
        df['Revised AOV'] = pd.to_numeric(
            revised_aov, errors='coerce').fillna(0)

    if npv_source in df.columns:
        # Parse → NaN on failure (no auto-0)
        parsed_npv = df[npv_source].apply(parse_npv_from_description)
    
        # Revised NPV: ALWAYS uses parsed (NaN where no data)
        df['Revised NPV'] = pd.to_numeric(parsed_npv, errors='coerce')
    
        print(f"Revised NPV populated: {(df['Revised NPV'].notna()).sum()} rows, "
              f"NaN: {(df['Revised NPV'].isna()).sum()} rows")

    return df


def reconcile_and_sync(df, engine):
    if df.empty:
        return

    new_cols = []
    for c in df.columns:
        clean = re.sub(r'[\s/:]+', '_', c.strip())
        clean = re.sub(r'[^a-zA-Z0-9_]', '', clean)
        new_cols.append(clean)
    df.columns = new_cols

    with engine.begin() as conn:
        df.to_sql('staging_won', conn, if_exists='replace', index=False)

        result = conn.execute(text(f"SHOW COLUMNS FROM {TABLE_NAME}"))
        db_cols = [row[0] for row in result]
        core_cols = [c for c in df.columns if c in db_cols]

        check_query = text(f"""
            SELECT s.Opportunity_Reference_ID, s.Product_Name, s.Annual_Order_Value_Multi
            FROM staging_won s
            WHERE NOT EXISTS (
                SELECT 1
                FROM {TABLE_NAME} m
                WHERE m.Opportunity_Reference_ID = s.Opportunity_Reference_ID
                  AND m.Product_Name = s.Product_Name
                  AND m.Annual_Order_Value_Multi = s.Annual_Order_Value_Multi
            )
        """)
        new_rows = conn.execute(check_query).fetchall()

        insert_query = f"""
            INSERT INTO {TABLE_NAME} ({', '.join([f'`{c}`' for c in core_cols])})
            SELECT {', '.join([f's.`{c}`' for c in core_cols])}
            FROM staging_won s
            WHERE NOT EXISTS (
                SELECT 1
                FROM {TABLE_NAME} m
                WHERE m.Opportunity_Reference_ID = s.Opportunity_Reference_ID
                  AND m.Product_Name = s.Product_Name
                  AND m.Annual_Order_Value_Multi = s.Annual_Order_Value_Multi
            )
        """
        conn.execute(text(insert_query))
        conn.execute(text("DROP TABLE staging_won"))

        if new_rows:
            details = "\n".join(
                [f"ID: {r[0]} | Product: {r[1]} | Amount: {r[2]}" for r in new_rows]
            )
            subject = f"CRM Alert: {len(new_rows)} New/Modified Won Rows"
            body = (
                f"Added/updated rows in sfdc_won:\n\n{details}\n\n"
                f"Check Type assignments."
            )
            send_notification(subject, body)
            print(f"Notification sent for {len(new_rows)} rows.")

        print(f"Sync complete: {len(new_rows)} new rows inserted.")


def main():
    start_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    service = get_drive_service()
    engine = get_db_engine()

    buffer = download_from_drive(service, 'won.html')
    if not buffer:
        print(f"[{start_time}] Error: won.html not found.")
        return

    df = pd.read_html(buffer)[0]
    print(f"[{start_time}] Won Sync: Processing {len(df)} rows...")

    for col in df.columns:
        if 'Date' in col:
            df[col] = pd.to_datetime(
                df[col], dayfirst=True, errors='coerce').dt.strftime('%Y-%m-%d')
            df[col] = df[col].replace('NaT', None)

    buffer.seek(0)
    links = extract_links(buffer)
    if 'Opportunity Name' in df.columns:
        df['Link'] = df['Opportunity Name'].str.strip().map(links)

    curr_cols = ['Annual Order Value Multi',
                 'Product Annual Recurring Order Value', 'Product TCV']
    for col in curr_cols:
        if col in df.columns:
            df[col] = df[col].apply(clean_currency)

    df = apply_revised_defaults(df)

    print(
        f"Pre-populated Revised AOV for {(df['Revised AOV'] > 0).sum()} rows "
        f"and Revised NPV for {(df['Revised NPV'] > 0).sum()} rows."
    )

    reconcile_and_sync(df, engine)

    end_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{end_time}] Complete: Revised fields pre-populated and data synced.")


if __name__ == "__main__":
    main()
