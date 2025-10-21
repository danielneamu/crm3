# Database Migration Guide

## Development Setup

1. Create database:
mysql -u danielneamu -p -e "CREATE DATABASE danielne_crm3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

2. Import schema:
mysql -u danielneamu -p danielne_crm3 < 01_create_schema.sql

3. Verify:
mysql -u danielneamu -p -e "USE danielne_crm3; SHOW TABLES;"

## Production Setup (Later)

1. **Backup existing database** (if applicable):
mysqldump -u danielne_app -p danielne_crm3 > backup_$(date +%Y%m%d).sql

2. Create database:
mysql -u danielne_app -p -e "CREATE DATABASE danielne_crm3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

3. Import schema:
mysql -u danielne_app -p danielne_crm3 < 01_create_schema.sql

4. Verify:
mysql -u danielne_app -p -e "USE danielne_crm3; SHOW TABLES;"

## Default Login
- Username: `admin`
- Password: `admin123`
- **CHANGE THIS IMMEDIATELY AFTER FIRST LOGIN**