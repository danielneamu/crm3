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
#           Save test


import os
import io
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseDownload
from google.oauth2 import service_account
import pandas as pd

# Get the directory where the script is located
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))

SCOPES = ['https://www.googleapis.com/auth/drive.readonly']
SERVICE_ACCOUNT_FILE = os.path.join(SCRIPT_DIR, 'personal-test-api.json')
SFDC_FOLDER_ID = '18lKLBBGlfPlqKqPRR_L3PQRT9-1yWaLZ'
FILE_NAME = 'Raport_all_Opps.csv'

# Authenticate
creds = service_account.Credentials.from_service_account_file(
    SERVICE_ACCOUNT_FILE, scopes=SCOPES
)
service = build('drive', 'v3', credentials=creds)

# Find the latest file by name in the folder
results = service.files().list(
    q=f"name='{FILE_NAME}' and '{SFDC_FOLDER_ID}' in parents and trashed=false",
    orderBy="createdTime desc",
    pageSize=1,
    fields="files(id, name, createdTime)"
).execute()

files = results.get('files', [])
if not files:
    raise FileNotFoundError(f"'{FILE_NAME}' not found in SFDC_Reports folder")

file_id = files[0]['id']
print(f"Found: {files[0]['name']} | Created: {files[0]['createdTime']}")

# Download into memory
request = service.files().get_media(fileId=file_id)
buffer = io.BytesIO()
downloader = MediaIoBaseDownload(buffer, request)

done = False
while not done:
    status, done = downloader.next_chunk()
    print(f"Download: {int(status.progress() * 100)}%")

# Read into pandas
buffer.seek(0)
df = pd.read_csv(buffer, index_col=False)
print(f"Loaded {len(df)} rows, {len(df.columns)} columns")
print(df.head())

# Print Refference ID values
print("\nOpportunity refference ID values:")
print(df['Opportunity Reference ID'].tolist())
