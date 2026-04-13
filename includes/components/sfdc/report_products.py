import os
import re
import io
import urllib.parse
import pandas as pd
from bs4 import BeautifulSoup
from sqlalchemy import create_engine, text
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseDownload
from datetime import datetime

# --- CONFIGURATION ---
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
SERVICE_ACCOUNT_FILE = os.path.join(SCRIPT_DIR, 'personal-test-api.json')
SCOPES = ['https://www.googleapis.com/auth/drive.readonly']
DB_USER = 'danielne_app'
DB_PASS = urllib.parse.quote_plus('Piedone1976!!')
DB_HOST = 'localhost'
DB_NAME = 'danielne_crm3'
TABLE_PRODUCTS = 'sfdc_product_pipeline'


def get_drive_service():
    """Authenticate and return Google Drive service"""
    creds = service_account.Credentials.from_service_account_file(
        SERVICE_ACCOUNT_FILE, scopes=SCOPES)
    return build('drive', 'v3', credentials=creds)


def get_db_engine():
    """Create and return SQLAlchemy engine for database"""
    return create_engine(f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}")


def download_from_drive(service, filename):
    """Download file from Google Drive by filename"""
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


def extract_links(buffer):
    """Extract Salesforce links from HTML buffer"""
    buffer.seek(0)
    soup = BeautifulSoup(buffer, 'html.parser')
    links_map = {}

    # Look for all links and extract Salesforce record IDs
    for a in soup.find_all('a', href=True):
        # Match Salesforce 18-character record IDs in URLs
        match = re.search(
            r'(006[a-zA-Z0-9]{12,15}|001[a-zA-Z0-9]{12,15}|01t[a-zA-Z0-9]{12,15})', a['href'])
        if match:
            # Map the visible text to the Salesforce URL
            text = a.get_text().strip()
            if text:
                links_map[
                    text] = f"https://onesf.lightning.force.com/lightning/r/{match.group(1)}/view"

    return links_map


def clean_currency(x):
    """Clean and parse currency values (European format support)"""
    if pd.isna(x) or str(x).strip() == '':
        return 0.0

    s = str(x).strip().upper()
    # Remove currency codes and spaces
    s = re.sub(r'[A-Z\s]', '', s)

    # Handle European formatting: 21.600,00 -> 21600.00
    if ',' in s and '.' in s:
        # Both present: if comma is last, it's decimal
        s = s.replace('.', '').replace(',', '.')
    elif ',' in s:
        # Only comma: if followed by 1-2 digits, it's decimal
        parts = s.split(',')
        if len(parts) == 2 and len(parts[1]) <= 2:
            s = s.replace(',', '.')
        else:
            s = s.replace(',', '')
    else:
        # Only dots: if multiple, keep last one as potential decimal
        dot_parts = s.split('.')
        if len(dot_parts) > 2:
            last = dot_parts[-1]
            rest = ''.join(dot_parts[:-1])
            s = rest + '.' + last

    # Final safety: remove anything that isn't digit or dot
    clean = re.sub(r'[^\d.]', '', s)

    try:
        return float(clean) if clean else 0.0
    except ValueError:
        return 0.0


def sanitize_columns(df):
    """Sanitize column names for database compatibility"""
    new_cols = []
    for c in df.columns:
        # Handle specific column renames
        clean = c.replace('Probability (%)', 'Probability_Percent')
        clean = clean.replace('Fiscal/VAT Number', 'Fiscal_VAT_Number')
        clean = clean.replace('Opportunity Reference ID',
                              'Opportunity_Reference_ID')
        clean = clean.replace('Account Name', 'Account_Name')
        clean = clean.replace('Opportunity Name', 'Opportunity_Name')
        clean = clean.replace('Opportunity Owner', 'Opportunity_Owner')
        clean = clean.replace('Owner Role', 'Owner_Role')
        clean = clean.replace('Fiscal Period', 'Fiscal_Period')
        clean = clean.replace(
            'Product Annual Recurring Order Value', 'Product_Annual_Recurring_Order_Value')
        clean = clean.replace('Annual Order Value Multi',
                              'Annual_Order_Value_Multi')
        clean = clean.replace('Product Family', 'Product_Family')
        clean = clean.replace('Product Name', 'Product_Name')
        clean = clean.replace('Product Code', 'Product_Code')
        clean = clean.replace('Created Date', 'Created_Date')
        clean = clean.replace('Close Date', 'Close_Date')
        clean = clean.replace('Last Modified Date', 'Last_Modified_Date')
        clean = clean.replace('Last Stage Change Date',
                              'Last_Stage_Change_Date')
        clean = clean.replace('Contract Term (Months)', 'Contract_Term_Months')

        # Generic replacements for any remaining spaces/special chars
        clean = re.sub(r'[\s/:]+', '_', clean.strip())
        clean = re.sub(r'[^a-zA-Z0-9_]', '', clean)
        new_cols.append(clean)

    df.columns = new_cols
    return df


def process_data(df, links_map):
    """Process and normalize product pipeline data"""

    # 1. Sanitize column names first
    df = sanitize_columns(df)

    # 2. Map the Links from Opportunity_Name
    if 'Opportunity_Name' in df.columns:
        df['Link'] = df['Opportunity_Name'].astype(
            str).str.strip().map(links_map)
    else:
        df['Link'] = None

    # 3. Handle Dates (European format: DD.MM.YYYY)
    date_patterns = ['Date', 'Close', 'Modified', 'Change', 'Created']
    for col in df.columns:
        if any(p in col for p in date_patterns):
            df[col] = df[col].astype(str).str.strip()

            # Pad with leading zeros
            df[col] = df[col].str.zfill(8)

            # Format: DDMMYYYY → DD.MM.YYYY
            df[col] = df[col].str[:2] + '.' + \
                df[col].str[2:4] + '.' + df[col].str[4:8]

            # Parse with explicit format
            df[col] = pd.to_datetime(
                df[col], format='%d.%m.%Y', errors='coerce')

            # Convert to YYYY-MM-DD for database
            df[col] = df[col].dt.strftime('%Y-%m-%d')
            # Replace NaT strings with None
            df[col] = df[col].replace('NaT', None)

    # 4. Handle Currencies (Product ARROV, Annual Order Value Multi)
    curr_cols = ['Product_Annual_Recurring_Order_Value',
                 'Annual_Order_Value_Multi']
    for col in curr_cols:
        if col in df.columns:
            df[col] = df[col].apply(clean_currency)

    # 5. Handle Percentages
    if 'Probability_Percent' in df.columns:
        df['Probability_Percent'] = df['Probability_Percent'].astype(
            str).str.replace('%', '', regex=False)
        df['Probability_Percent'] = pd.to_numeric(
            df['Probability_Percent'], errors='coerce').fillna(0)

    # 6. Handle Numeric columns
    if 'Age' in df.columns:
        df['Age'] = pd.to_numeric(df['Age'], errors='coerce').fillna(0)
    if 'Contract_Term_Months' in df.columns:
        df['Contract_Term_Months'] = pd.to_numeric(
            df['Contract_Term_Months'], errors='coerce').fillna(0)

    # 7. Ensure all required columns exist
    required_cols = [
        'Opportunity_Reference_ID', 'Opportunity_Owner', 'Owner_Role',
        'Account_Name', 'Opportunity_Name', 'Fiscal_VAT_Number',
        'Fiscal_Period', 'Stage', 'Probability_Percent', 'Age',
        'Created_Date', 'Close_Date', 'Last_Modified_Date',
        'Last_Stage_Change_Date', 'Contract_Term_Months', 'Description',
        'Annual_Order_Value_Multi', 'Product_Family', 'Product_Name',
        'Product_Code', 'Product_Annual_Recurring_Order_Value', 'Link'
    ]

    for col in required_cols:
        if col not in df.columns:
            df[col] = None

    return df


def sync_to_db(df, engine):
    """
    Product pipeline: full-table overwrite strategy.
    
    No DUPLICATE KEY UPDATE logic.
    Simply: DELETE old data, INSERT fresh.
    Auto-increment PK handles row IDs.
    """
    if df.empty:
        print(
            f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Product pipeline: no data.")
        return

    with engine.begin() as conn:
        # 1. Create staging table
        df.to_sql('staging_products', conn, if_exists='replace', index=False)

        # 2. TRUNCATE existing data (full overwrite)
        try:
            conn.execute(text(f"TRUNCATE TABLE {TABLE_PRODUCTS}"))
        except Exception as e:
            print(f"Warning: TRUNCATE failed: {e}")
            # Fallback: DELETE all rows
            conn.execute(text(f"DELETE FROM {TABLE_PRODUCTS}"))

        # 3. INSERT fresh product pipeline data
        insert_query = f"""
            INSERT INTO {TABLE_PRODUCTS} (
                Opportunity_Reference_ID,
                Opportunity_Owner,
                Owner_Role,
                Account_Name,
                Opportunity_Name,
                Fiscal_VAT_Number,
                Fiscal_Period,
                Stage,
                Probability_Percent,
                Age,
                Created_Date,
                Close_Date,
                Last_Modified_Date,
                Last_Stage_Change_Date,
                Contract_Term_Months,
                Description,
                Annual_Order_Value_Multi,
                Product_Family,
                Product_Name,
                Product_Code,
                Product_Annual_Recurring_Order_Value,
                Link
            )
            SELECT 
                Opportunity_Reference_ID,
                Opportunity_Owner,
                Owner_Role,
                Account_Name,
                Opportunity_Name,
                Fiscal_VAT_Number,
                Fiscal_Period,
                Stage,
                Probability_Percent,
                Age,
                Created_Date,
                Close_Date,
                Last_Modified_Date,
                Last_Stage_Change_Date,
                Contract_Term_Months,
                Description,
                Annual_Order_Value_Multi,
                Product_Family,
                Product_Name,
                Product_Code,
                Product_Annual_Recurring_Order_Value,
                Link
            FROM staging_products
        """
        conn.execute(text(insert_query))
        conn.execute(text("DROP TABLE staging_products"))

        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(
            f"[{timestamp}] Product Pipeline: {len(df)} rows synced (FULL OVERWRITE).")


def main():
    """Main sync orchestration"""
    start_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    try:
        # 1. Initialize services
        service = get_drive_service()
        engine = get_db_engine()

        # 2. Download product report
        buffer = download_from_drive(service, 'prodPipeline.html')
        if not buffer:
            print(
                f"[{start_time}] Error: prodPipeline.html not found on Google Drive.")
            return

        # 3. Extract links
        links = extract_links(buffer)

        # 4. Parse HTML table into DataFrame
        buffer.seek(0)
        df = pd.read_html(buffer, decimal=',', thousands='.')[0]

        row_count = len(df)
        print(
            f"[{start_time}] Starting Product Sync: Processing {row_count} rows from prodPipeline.html...")

        # 5. Process data
        df = process_data(df, links)

        # 6. Sync to database
        sync_to_db(df, engine)

        # 7. Log completion
        end_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{end_time}] Process Complete: Product pipeline synced to database.")

    except Exception as e:
        error_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{error_time}] ERROR: {str(e)}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()
