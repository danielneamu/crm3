import os
import re
import io
import urllib.parse
import pandas as pd
from bs4 import BeautifulSoup  # Added for link extraction
from sqlalchemy import create_engine, text
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseDownload
from datetime import datetime  # Add this to your imports at the top

# --- CONFIGURATION ---
SERVICE_ACCOUNT_FILE = 'personal-test-api.json'
SCOPES = ['https://www.googleapis.com/auth/drive.readonly']
DB_USER = 'danielne_app'
DB_PASS = urllib.parse.quote_plus('Piedone1976!!')
DB_HOST = 'localhost'
DB_NAME = 'danielne_crm3'
TABLE_MAIN = 'sfdc_main'
TABLE_LOG = 'sfdc_log'


def get_drive_service():
    creds = service_account.Credentials.from_service_account_file(
        SERVICE_ACCOUNT_FILE, scopes=SCOPES)
    return build('drive', 'v3', credentials=creds)


def get_db_engine():
    return create_engine(f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}")


def download_from_drive(service, filename):
    results = service.files().list(
        q=f"name='{filename}'", fields="files(id, name)").execute()
    items = results.get('files', [])
    if not items:
        return None
    file_id = items[0]['id']
    request = service.files().get_media(fileId=file_id)
    fh = io.BytesIO()
    downloader = MediaIoBaseDownload(fh, request)
    done = False
    while not done:
        _, done = downloader.next_chunk()
    fh.seek(0)
    return fh

# --- LINK EXTRACTION (Imported from won.py logic) ---


def extract_links(buffer):
    buffer.seek(0)
    soup = BeautifulSoup(buffer, 'html.parser')
    links_map = {}
    for a in soup.find_all('a', href=True):
        match = re.search(r'(006[a-zA-Z0-9]{12,15})', a['href'])
        if match:
            # Map the visible text (Opp Name) to the Salesforce URL
            links_map[a.get_text().strip(
            )] = f"https://onesf.lightning.force.com/lightning/r/{match.group(1)}/view"
    return links_map


def clean_currency(x):
    if pd.isna(x) or str(x).strip() == '':
        return 0.0

    # 1. Convert to string and remove currency codes/spaces
    s = str(x).strip().upper()
    s = re.sub(r'[A-Z\s]', '', s)  # Removes "EUR", "USD", and spaces

    # 2. Handle European formatting: 21.600,00 -> 21600.00
    # We remove the dot (thousands) and replace the comma (decimal) with a dot
    if ',' in s and '.' in s:
        s = s.replace('.', '').replace(',', '.')
    elif ',' in s:
        # Case where it's just "21,60" (no thousands separator)
        s = s.replace(',', '.')

    # 3. Final safety: remove anything that isn't a digit or a single dot
    clean = re.sub(r'[^\d.]', '', s)

    try:
        return float(clean) if clean else 0.0
    except ValueError:
        return 0.0


def sanitize_columns(df):
    new_cols = []
    for c in df.columns:
        clean = c.replace('Probability (%)', 'Probability_Percent')
        clean = re.sub(r'[\s/:]+', '_', clean.strip())
        clean = re.sub(r'[^a-zA-Z0-9_]', '', clean)
        new_cols.append(clean)
    df.columns = new_cols
    return df


def process_data(df, links_map):
    # 1. Sanitize Names FIRST so we can use 'Opportunity_Name' safely
    df = sanitize_columns(df)

    # 2. Map the Links
    if 'Opportunity_Name' in df.columns:
        df['Link'] = df['Opportunity_Name'].str.strip().map(links_map)

    # 3. Handle Dates (European format: DD.MM.YYYY)
    date_patterns = ['Date', 'Close', 'Modified', 'Change']
    for col in df.columns:
        if any(p in col for p in date_patterns):
            # Parse with dayfirst=True for European format (01.04.2026 = 1 April 2026)
            # infer_datetime_format=True speeds up parsing for consistent formats
            df[col] = pd.to_datetime(
                df[col],
                dayfirst=True,
                errors='coerce',
                cache=True
            )
            # Convert to YYYY-MM-DD format for database
            df[col] = df[col].dt.strftime('%Y-%m-%d')
            # Replace 'NaT' strings with None (NULL in database)
            df[col] = df[col].replace('NaT', None)

    # 4. Handle Currencies
    curr_cols = ['Amount', 'Expected_Revenue', 'Annual_Order_Value_Multi']
    for col in curr_cols:
        if col in df.columns:
            df[col] = df[col].apply(clean_currency)

    # 5. Handle Percentages & Numbers
    if 'Probability_Percent' in df.columns:
        # Check if it's already numeric or needs string cleaning
        df['Probability_Percent'] = df['Probability_Percent'].astype(
            str).str.replace('%', '', regex=False)
        df['Probability_Percent'] = pd.to_numeric(
            df['Probability_Percent'], errors='coerce').fillna(0)

    if 'Age' in df.columns:
        df['Age'] = pd.to_numeric(df['Age'], errors='coerce').fillna(0)
    if 'Contract_Term_Months' in df.columns:
        df['Contract_Term_Months'] = pd.to_numeric(
            df['Contract_Term_Months'], errors='coerce').fillna(0)

    # 6. Static Columns
    df['Type'] = None
    df['Real_Flag'] = False

    return df


def sync_to_db(df, engine):
    if df.empty:
        return

    with engine.begin() as conn:
        df.to_sql('staging_report', conn, if_exists='replace', index=False)

        # Log changes (Note: Link is typically NOT logged, just upserted to Main)
        log_query = f"""
            INSERT INTO {TABLE_LOG} (
                Opportunity_Reference_ID, Opportunity_Name, Fiscal_Period, Amount, 
                Expected_Revenue, Annual_Order_Value_Multi, Description, Stage, 
                Probability_Percent, Close_Date, Last_Modified_Date, 
                Last_Stage_Change_Date, Contract_Term_Months
            )
            SELECT s.Opportunity_Reference_ID, s.Opportunity_Name, s.Fiscal_Period, s.Amount, 
                   s.Expected_Revenue, s.Annual_Order_Value_Multi, s.Description, s.Stage, 
                   s.Probability_Percent, s.Close_Date, s.Last_Modified_Date, 
                   s.Last_Stage_Change_Date, s.Contract_Term_Months
            FROM staging_report s
            LEFT JOIN {TABLE_MAIN} m ON s.Opportunity_Reference_ID = m.Opportunity_Reference_ID
            WHERE m.Opportunity_Reference_ID IS NULL 
               OR s.Stage != m.Stage 
               OR s.Amount != m.Amount
               OR s.Close_Date != m.Close_Date
               OR s.Last_Modified_Date != m.Last_Modified_Date;
        """
        conn.execute(text(log_query))

        # Upsert Main Table (This will now include the `Link` column automatically)
        quoted_cols = [f"`{c}`" for c in df.columns]
        update_cols = [
            f"`{c}` = VALUES(`{c}`)" for c in df.columns if c != 'Opportunity_Reference_ID']

        upsert_query = f"""
            INSERT INTO {TABLE_MAIN} ({', '.join(quoted_cols)})
            SELECT * FROM staging_report
            ON DUPLICATE KEY UPDATE {', '.join(update_cols)}
        """
        conn.execute(text(upsert_query))
        conn.execute(text("DROP TABLE staging_report"))


def main():
    # Get current timestamp for the start of the process
    start_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    service = get_drive_service()
    engine = get_db_engine()

    buffer = download_from_drive(service, 'report.html')
    if not buffer:
        print(f"[{start_time}] Error: report.html not found.")
        return

    # Extract links and data
    links = extract_links(buffer)
    buffer.seek(0)
    df = pd.read_html(buffer, decimal=',', thousands='.')[0]
    
    # Line 1: Log the start and row count
    print(
        f"[{start_time}] Starting Sync: Processing {len(df)} rows from report.html...")

    # Process and Sync
    df = process_data(df, links)
    sync_to_db(df, engine)

    # Line 2: Log the completion with a fresh timestamp
    end_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{end_time}] Process Complete: Database synced (Main + Log).")


if __name__ == "__main__":
    main()
