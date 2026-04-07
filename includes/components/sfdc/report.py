import os
import re
import io
import urllib.parse  # <--- ADD THIS LINE HERE
import pandas as pd
from sqlalchemy import create_engine, text
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseDownload

# --- CONFIGURATION ---
SERVICE_ACCOUNT_FILE = 'personal-test-api.json'  # Ensure path is correct
SCOPES = ['https://www.googleapis.com/auth/drive.readonly']
# --- CONFIGURATION ---
DB_USER = 'danielne_app'
DB_PASS = urllib.parse.quote_plus(
    'Piedone1976!!')  # <--- Wrap the password here
DB_HOST = 'localhost'
DB_NAME = 'danielne_crm3'
TABLE_MAIN = 'sfdc_main'
TABLE_LOG = 'sfdc_log'


def get_drive_service():
    creds = service_account.Credentials.from_service_account_file(
        SERVICE_ACCOUNT_FILE, scopes=SCOPES)
    return build('drive', 'v3', credentials=creds)

# --- ENGINE CREATION ---


def get_db_engine():
    # This creates the string: mysql+pymysql://danielne_app:Piedone1976%21%21@localhost/danielne_crm3
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


def clean_currency(x):
    if pd.isna(x) or str(x).strip() == '':
        return 0.0
    # Removes currency symbols and commas
    clean = re.sub(r'[^\d.]', '', str(x))
    return float(clean) if clean else 0.0


def sanitize_columns(df):
    new_cols = []
    for c in df.columns:
        # Standardize names
        clean = c.replace('Probability (%)',
                          'Probability_Percent')  # Explicit fix
        clean = re.sub(r'[\s/:]+', '_', clean.strip())
        clean = re.sub(r'[^a-zA-Z0-9_]', '', clean)
        new_cols.append(clean)
    df.columns = new_cols
    return df


def process_data(df):
    if 'Probability_Percent' in df.columns:
        df['Probability_Percent'] = df['Probability_Percent'].astype(
            str).str.replace('%', '', regex=False)
        df['Probability_Percent'] = pd.to_numeric(
            df['Probability_Percent'], errors='coerce').fillna(0)
    # 1. Sanitize Names
    df = sanitize_columns(df)

    # 2. Handle Dates (The "Nuclear" Fix)
    date_patterns = ['Date', 'Close', 'Modified', 'Change']
    for col in df.columns:
        if any(p in col for p in date_patterns):
            df[col] = pd.to_datetime(
                df[col], dayfirst=True, errors='coerce').dt.strftime('%Y-%m-%d')
            df[col] = df[col].replace('NaT', None)

    # 3. Handle Currencies
    curr_cols = ['Amount', 'Expected_Revenue', 'Annual_Order_Value_Multi']
    for col in curr_cols:
        if col in df.columns:
            df[col] = df[col].apply(clean_currency)

    # 4. Handle Percentages & Numbers
    if 'Probability_Percent' in df.columns:
        df['Probability_Percent'] = df['Probability_Percent'].str.replace(
            '%', '', regex=False).apply(pd.to_numeric, errors='coerce').fillna(0)
    if 'Age' in df.columns:
        df['Age'] = pd.to_numeric(df['Age'], errors='coerce').fillna(0)
    if 'Contract_Term_Months' in df.columns:
        df['Contract_Term_Months'] = pd.to_numeric(
            df['Contract_Term_Months'], errors='coerce').fillna(0)

    # 5. Static Columns
    df['Type'] = None
    df['Real_Flag'] = False

    return df


def sync_to_db(df, engine):
    if df.empty:
        return

    with engine.begin() as conn:
        # Create staging table for the 2k row batch
        df.to_sql('staging_report', conn, if_exists='replace', index=False)

        # --- STEP 1: LOG CHANGES ---
        # We only insert into sfdc_log if the ID is new OR specific fields have changed
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

        # --- STEP 2: UPSERT MAIN TABLE ---
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
    service = get_drive_service()
    engine = get_db_engine()

    print("Downloading report.html...")
    buffer = download_from_drive(service, 'report.html')
    if not buffer:
        return

    df = pd.read_html(buffer)[0]

    print(f"Processing {len(df)} rows...")
    df = process_data(df)

    print("Syncing to Database (Main + Log)...")
    sync_to_db(df, engine)
    print("Process Complete.")


if __name__ == "__main__":
    main()
