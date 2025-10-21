-- ============================================
-- CRM3 Database Schema Creation
-- Step 1: Create fresh database structure
-- ============================================

-- Create database (run separately if needed)
-- CREATE DATABASE danielne_crm3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE danielne_crm3;

-- ============================================
-- Core Tables
-- ============================================

-- Users table
CREATE TABLE users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    is_active BOOLEAN DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Companies table
CREATE TABLE companies (
    id_companies SMALLINT PRIMARY KEY AUTO_INCREMENT,
    name_companies VARCHAR(255) NOT NULL UNIQUE,
    city_companies VARCHAR(100) DEFAULT NULL,
    fiscal_code VARCHAR(50) UNIQUE NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name_companies),
    INDEX idx_fiscal (fiscal_code)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Agents table
CREATE TABLE agents (
    id_agent TINYINT PRIMARY KEY AUTO_INCREMENT,
    nume_agent VARCHAR(100) NOT NULL,
    cod_agent VARCHAR(20) NOT NULL,
    current_team VARCHAR(50) NOT NULL,
    status_agent BOOLEAN DEFAULT 1 COMMENT 'Active/Inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_team (current_team),
    INDEX idx_status (status_agent)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Agent Team History table
CREATE TABLE agent_team_history (
    id_history INT PRIMARY KEY AUTO_INCREMENT,
    agent_id TINYINT NOT NULL,
    team_name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL COMMENT 'NULL = current assignment',
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (agent_id) REFERENCES agents (id_agent) ON DELETE CASCADE,
    INDEX idx_agent (agent_id),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_team (team_name)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Partners table (keep existing structure)
-- Note: Run this only if starting fresh, otherwise skip
CREATE TABLE IF NOT EXISTS parteneri (
    id_parteneri SMALLINT PRIMARY KEY AUTO_INCREMENT,
    name_parteneri VARCHAR(255) NOT NULL,
    type_parteneri VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Projects table (main entity)
CREATE TABLE projects (
    id_project INT PRIMARY KEY AUTO_INCREMENT,
    name_project VARCHAR(255) NOT NULL,
    project_type ENUM('ICT/IOT', 'Fixed', 'Other') DEFAULT 'ICT/IOT',
    createDate_project DATE NOT NULL,
    tcv_project DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT 'Total Contract Value in EUR',
    contract_project TINYINT NOT NULL DEFAULT 24 COMMENT 'Contract duration in months',
    agent_project TINYINT NOT NULL,
    company_project SMALLINT NOT NULL,
    partner_project SMALLINT NULL,
    eft_command VARCHAR(50) DEFAULT NULL COMMENT 'Formerly b2b',
    solution_dev_number VARCHAR(50) DEFAULT NULL COMMENT 'Formerly sd',
    eft_case VARCHAR(50) DEFAULT NULL COMMENT 'Formerly pt',
    sfdc_opp VARCHAR(50) DEFAULT NULL COMMENT 'Salesforce.com Opportunity number',
    comment_project TEXT DEFAULT NULL COMMENT 'Current/main comment',
    active_project BOOLEAN DEFAULT 1 COMMENT 'Active/Inactive filter (not delete)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_project) REFERENCES agents (id_agent),
    FOREIGN KEY (company_project) REFERENCES companies (id_companies),
    FOREIGN KEY (partner_project) REFERENCES parteneri (id_parteneri),
    INDEX idx_active (active_project),
    INDEX idx_agent (agent_project),
    INDEX idx_company (company_project),
    INDEX idx_create_date (createDate_project),
    INDEX idx_type (project_type)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Project Status History table
CREATE TABLE project_status_history (
    id_status INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    status_name ENUM(
        'New',
        'Qualifying',
        'Design',
        'Completed',
        'Pending',
        'Contract Signed',
        'Cancelled',
        'Offer Refused',
        'No Solution'
    ) NOT NULL,
    responsible_party ENUM(
        'Presales',
        'Sales',
        'Engineer',
        'Partner',
        'Customer'
    )  NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comment TEXT DEFAULT NULL COMMENT 'Optional note about status change',
    FOREIGN KEY (project_id) REFERENCES projects (id_project) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_date (changed_at),
    INDEX idx_status (status_name),
    INDEX idx_responsible (responsible_party)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Project Comments History table (optional)
CREATE TABLE project_comments (
    id_comment INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects (id_project) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_date (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create default admin user (password: admin123 - CHANGE IMMEDIATELY)
INSERT INTO
    users (
        username,
        email,
        password_hash,
        full_name
    )
VALUES (
        'admin',
        'admin@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Administrator'
    );