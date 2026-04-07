# File uses personal-test-api-52a795390336.json file for logging in to Google Drive
# Json file was generated in Google Drive API  for Personal Test API project
#  Service Account: sfdc-reports@personal-test-api.iam.gserviceaccount.com
#
# STEPS for setup :
# Step 1: Set Up Google Cloud Credentials
#           Go to the Google Cloud Console and create (or select) a project
#           Navigate to APIs & Services → Library and enable the Google Drive API
#           Go to IAM & Admin → Service Accounts → Create Service Account
#           After creating the account, go to its Keys tab → Add Key → Create new key → JSON
#           Save the downloaded service-account.json securely — this is your only copy
# Step 2: Share the Google Sheet with the Service Account
#           Copy the service account email (e.g., [EMAIL_ADDRESS])
#           Open your Google Sheet  - in our case we share the entire folder SFDC_Reports
#           Click Share
#           Paste the service account email and give it Editor access
#           Save


import io
import os
import re
import pandas as pd
from bs4 import BeautifulSoup
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseDownload
from google.oauth2 import service_account
from sqlalchemy import create_engine, text

# --- CONFIGURATION ---
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
SERVICE_ACCOUNT_FILE = os.path.join(SCRIPT_DIR, 'personal-test-api.json')
SCOPES = ['https://www.googleapis.com/auth/drive.readonly']
FOLDER_ID = '18lKLBBGlfPlqKqPRR_L3PQRT9-1yWaLZ'

# MySQL Config - Updated with your details
DB_USER = 'danielne_app'
DB_PASS = 'Piedone1976!!'  # Make sure to put your actual password here
DB_HOST = 'localhost'
DB_NAME = 'danielne_crm3'
TABLE_NAME = 'sfdc_won'


def get_db_engine():
    # Change 'mysqlconnector' to 'pymysql'
    return create_engine(f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}")


def get_drive_service():
    creds = service_account.Credentials.from_service_account_file(
        SERVICE_ACCOUNT_FILE, scopes=SCOPES)
    return build('drive', 'v3', credentials=creds)


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
            links_map[a.get_text().strip(
            )] = f"https://onesf.lightning.force.com/lightning/r/{match.group(1)}/view"
    return links_map


def clean_currency(value):
    if pd.isna(value) or not isinstance(value, str):
        return value
    # Handles "EUR 1.234,56" (case-insensitive) -> 1234.56
    clean_val = value.upper().replace('EUR', '').replace(
        '.', '').replace(',', '.').strip()
    try:
        return float(clean_val)
    except ValueError:
        return 0.0


def upsert_data(df, engine):
    if df.empty:
        return

    # 1. Clean columns: Remove arrows, special chars, keep only Alphanumeric and Underscore
    # This will turn 'Date↑' into 'Date' and 'Term (months)' into 'Term_months'
    new_cols = []
    for c in df.columns:
        clean = c.strip()
        # Replace spaces/slashes/colons with underscore
        clean = re.sub(r'[\s/:]+', '_', clean)
        # Remove everything that isn't a letter, number, or underscore (removes the ↑)
        clean = re.sub(r'[^a-zA-Z0-9_]', '', clean)
        new_cols.append(clean)

    df.columns = new_cols

    with engine.begin() as conn:
        # Create staging table
        df.to_sql('staging_won', conn, if_exists='replace', index=False)

        # Use backticks for safety (especially for the parentheses columns)
        quoted_cols = [f"`{c}`" for c in df.columns]
        update_cols = [
            f"`{c}` = VALUES(`{c}`)" for c in df.columns if c != 'Opportunity_Reference_ID']

        upsert_query = f"""
            INSERT INTO {TABLE_NAME} ({', '.join(quoted_cols)})
            SELECT * FROM staging_won
            ON DUPLICATE KEY UPDATE {', '.join(update_cols)}
        """
        conn.execute(text(upsert_query))
        conn.execute(text("DROP TABLE staging_won"))


def main():
    service = get_drive_service()
    engine = get_db_engine()

    buffer = download_from_drive(service, 'won.html')
    if not buffer:
        print("File won.html not found.")
        return

    tables = pd.read_html(buffer)
    if not tables:
        print("No tables found in won.html")
        return

    df = tables[0]

    # --- THE FIX: Convert Dates IMMEDIATELY ---
    # We use a loop to find any column with 'Date' in the name to be safe
    for col in df.columns:
        if 'Date' in col:
            # 1. Parse European format
            df[col] = pd.to_datetime(df[col], dayfirst=True, errors='coerce')
            # 2. Force to YYYY-MM-DD string immediately
            df[col] = df[col].dt.strftime('%Y-%m-%d')
            # 3. Replace 'NaT' (Not a Time) with None for MySQL NULL
            df[col] = df[col].replace('NaT', None)

    # 1. Map Links
    buffer.seek(0)
    links = extract_links(buffer)
    if 'Opportunity Name' in df.columns:
        df['Link'] = df['Opportunity Name'].str.strip().map(links)

    # 2. Clean Currencies
    curr_cols = ['Annual Order Value Multi',
                 'Product Annual Recurring Order Value', 'Product TCV']
    for col in curr_cols:
        if col in df.columns:
            df[col] = df[col].apply(clean_currency)

    # 3. Add Extra Static Columns
    df['Type'] = None

    # 4. Sync to DB
    print(f"Syncing {len(df)} rows to MySQL...")
    upsert_data(df, engine)
    print("Process Complete.")


if __name__ == "__main__":
    main()
