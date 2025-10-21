-- ============================================
-- Import Data from Production Backup
-- Source: danielne_app_temp (imported backup)
-- Target: danielne_crm3 (new structure)
-- ============================================

-- Import Companies
INSERT INTO
    danielne_crm3.companies (
        id_companies,
        name_companies,
        city_companies,
        fiscal_code,
        created_at
    )
SELECT
    id_companies,
    name_companies,
    city_companies,
    contact_companies AS fiscal_code,
    NOW()
FROM danielne_app_temp.companies;

-- Import Agents
INSERT INTO
    danielne_crm3.agents (
        id_agent,
        nume_agent,
        cod_agent,
        current_team,
        status_agent,
        created_at
    )
SELECT
    id_agent,
    nume_agent,
    cod_agent,
    team_agent AS current_team,
    status_agent,
    NOW()
FROM danielne_app_temp.agents;

-- Agent team history
INSERT INTO
    danielne_crm3.agent_team_history (
        agent_id,
        team_name,
        start_date,
        end_date
    )
SELECT id_agent, team_agent, CURDATE(), NULL
FROM danielne_app_temp.agents;

-- Import Partners
INSERT INTO
    danielne_crm3.parteneri (
        id_parteneri,
        name_parteneri,
        type_parteneri
    )
SELECT id, nume, detalii
FROM danielne_app_temp.parteneri
WHERE
    active = 1;

-- Partner tables
INSERT IGNORE INTO
    danielne_crm3.partcontacts
SELECT *
FROM danielne_app_temp.partcontacts;

INSERT IGNORE INTO
    danielne_crm3.partags
SELECT *
FROM danielne_app_temp.partags;

INSERT IGNORE INTO
    danielne_crm3.partagmap
SELECT *
FROM danielne_app_temp.partagmap;

-- Import Projects
INSERT INTO
    danielne_crm3.projects (
        id_project,
        name_project,
        project_type,
        createDate_project,
        tcv_project,
        contract_project,
        agent_project,
        company_project,
        partner_project,
        eft_command,
        solution_dev_number,
        eft_case,
        sfdc_opp,
        comment_project,
        active_project,
        created_at,
        updated_at
    )
SELECT
    id_project,
    name_project,
    'ICT/IOT',
    createDate_project,
    tcv_project,
    contract_project,
    agent_project,
    company_project,
    NULL,
    b2b,
    sd,
    pt,
    sfdc,
    comment_project,
    active_project,
    NOW(),
    NOW()
FROM danielne_app_temp.projects;

-- Import Status History
INSERT INTO
    danielne_crm3.project_status_history (
        project_id,
        status_name,
        responsible_party,
        changed_at,
        comment
    )
SELECT
    projectID_status,
    CASE
        WHEN subprojects_status IN (
            'New',
            'Qualifying',
            'Design',
            'Completed',
            'Pending',
            'Contract Signed',
            'Cancelled',
            'Offer Refused',
            'No Solution'
        ) THEN subprojects_status
        ELSE 'New'
    END,
    CASE
        WHEN responsible_status IN (
            'Presales',
            'Sales',
            'Engineer',
            'Partner',
            'Customer'
        ) THEN responsible_status
        ELSE 'Presales'
    END,
    createDate_status,
    comments_status
FROM danielne_app_temp.status;

-- Verification
SELECT 'Companies:' AS Info, COUNT(*)
FROM danielne_crm3.companies
UNION ALL
SELECT 'Agents:', COUNT(*)
FROM danielne_crm3.agents
UNION ALL
SELECT 'Partners:', COUNT(*)
FROM danielne_crm3.parteneri
UNION ALL
SELECT 'Projects:', COUNT(*)
FROM danielne_crm3.projects
UNION ALL
SELECT 'Status history:', COUNT(*)
FROM danielne_crm3.project_status_history;