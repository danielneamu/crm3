Step 1: Create New Database Structure
bash
# SSH into production server
ssh user@production-server

# Create new database
mysql -u danielne_app -p -e "CREATE DATABASE danielne_crm3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u danielne_app -p danielne_crm3 < /path/to/crm3/migrations/01_create_schema.sql

# Verify tables created
mysql -u danielne_app -p -e "USE danielne_crm3; SHOW TABLES;"
Step 2: Create Temporary Database and Import Old Data
bash
# Create temp database
mysql -u danielne_app -p -e "CREATE DATABASE danielne_app_temp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import old backup into temp database
mysql -u danielne_app -p danielne_app_temp < danielne_app_backup.sql

# Verify import
mysql -u danielne_app -p -e "USE danielne_app_temp; SHOW TABLES;"
Step 3: Clean Duplicate Data
bash
mysql -u danielne_app -p danielne_app_temp << 'EOF'
-- Remove company name duplicates (keep lowest ID)
DELETE c1 FROM companies c1
INNER JOIN companies c2 
WHERE c1.id_companies > c2.id_companies 
AND c1.name_companies = c2.name_companies;

-- Remove fiscal code duplicates
DELETE c1 FROM companies c1
INNER JOIN companies c2 
WHERE c1.id_companies > c2.id_companies 
AND c1.contact_companies = c2.contact_companies;
EOF
Step 4: Import Data into New Structure
bash
mysql -u danielne_app -p << 'EOF'
-- Import Companies
INSERT INTO danielne_crm3.companies (id_companies, name_companies, city_companies, fiscal_code, created_at)
SELECT id_companies, name_companies, city_companies, contact_companies, NOW()
FROM danielne_app_temp.companies;

-- Import Agents
INSERT INTO danielne_crm3.agents (id_agent, nume_agent, cod_agent, current_team, status_agent, created_at)
SELECT id_agent, nume_agent, cod_agent, team_agent, status_agent, NOW()
FROM danielne_app_temp.agents;

-- Import Agent Team History
INSERT INTO danielne_crm3.agent_team_history (agent_id, team_name, start_date)
SELECT id_agent, team_agent, CURDATE()
FROM danielne_app_temp.agents;

-- Import Partners (if exists)
INSERT IGNORE INTO danielne_crm3.parteneri (id_parteneri, name_parteneri, type_parteneri)
SELECT id, nume, detalii
FROM danielne_app_temp.parteneri
WHERE active = 1;

-- Import Partner tables (if exist)
INSERT IGNORE INTO danielne_crm3.partcontacts SELECT * FROM danielne_app_temp.partcontacts;
INSERT IGNORE INTO danielne_crm3.partags SELECT * FROM danielne_app_temp.partags;
INSERT IGNORE INTO danielne_crm3.partagmap SELECT * FROM danielne_app_temp.partagmap;

-- Import Projects (only with valid references)
INSERT INTO danielne_crm3.projects (
    id_project, name_project, project_type, createDate_project,
    tcv_project, contract_project, agent_project, company_project,
    partner_project, eft_command, solution_dev_number, eft_case,
    sfdc_opp, comment_project, active_project, created_at, updated_at
)
SELECT 
    p.id_project, p.name_project, 'ICT/IOT', p.createDate_project,
    p.tcv_project, p.contract_project, p.agent_project, p.company_project,
    NULL, p.b2b, p.sd, p.pt, p.sfdc, p.comment_project, p.active_project, NOW(), NOW()
FROM danielne_app_temp.projects p
WHERE p.agent_project IN (SELECT id_agent FROM danielne_crm3.agents)
  AND p.company_project IN (SELECT id_companies FROM danielne_crm3.companies);

-- Import Status History (only for imported projects)
INSERT INTO danielne_crm3.project_status_history (project_id, status_name, responsible_party, changed_at, comment)
SELECT 
    s.projectID_status,
    COALESCE(CASE WHEN s.subprojects_status IN ('New','Qualifying','Design','Completed','Pending','Contract Signed','Cancelled','Offer Refused','No Solution') THEN s.subprojects_status END, 'New'),
    CASE WHEN s.responsible_status IN ('Presales','Sales','Engineer','Partner','Customer') THEN s.responsible_status ELSE NULL END,
    s.createDate_status,
    s.comments_status
FROM danielne_app_temp.status s
WHERE s.projectID_status IN (SELECT id_project FROM danielne_crm3.projects);
EOF
Step 5: Verify Import
bash
mysql -u danielne_app -p danielne_crm3 << 'EOF'
SELECT 'Companies' as Table_Name, COUNT(*) as Count FROM companies
UNION ALL SELECT 'Agents', COUNT(*) FROM agents
UNION ALL SELECT 'Agent History', COUNT(*) FROM agent_team_history
UNION ALL SELECT 'Partners', COUNT(*) FROM parteneri
UNION ALL SELECT 'Projects', COUNT(*) FROM projects
UNION ALL SELECT 'Status History', COUNT(*) FROM project_status_history;
EOF
Step 6: Cleanup
bash
# Drop temporary database
mysql -u danielne_app -p -e "DROP DATABASE danielne_app_temp;"

# Backup new database
mysqldump -u danielne_app -p danielne_crm3 > danielne_crm3_migrated_$(date +%Y%m%d).sql
Step 7: Update Production Config
Ensure /path/to/crm3/config/config.prod.php points to danielne_crm3:

php
define('DB_NAME', 'danielne_crm3');