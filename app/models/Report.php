<?php

/**
 * Report Model
 * Data layer for all report queries
 * Returns structured arrays ready for display/export
 */

class Report
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * REPORT 1: Agent Performance
     * Shows each active agent with project metrics and revenue
     * 
     * @param array $filters ['team' => [], 'dateFrom' => '2025-01-01', 'dateTo' => '2025-10-31', 'status' => []]
     * @return array Agent performance data
     */
    public function getAgentPerformance($filters = [])
    {
        // Build WHERE clause for filters
        $whereTeam = '';
        $whereDate = '';
        $whereStatus = '';

        if (!empty($filters['team'])) {
            $placeholders = implode(',', array_fill(0, count($filters['team']), '?'));
            $whereTeam = "AND a.current_team IN ($placeholders)";
        }

        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $whereDate = "AND p.createDate_project BETWEEN ? AND ?";
        }

        if (!empty($filters['status'])) {
            $placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $whereStatus = "AND latest.status_name IN ($placeholders)";
        }

        $sql = "
            SELECT 
                a.id_agent,
                a.nume_agent as agent_name,
                a.current_team,
                a.status_agent,
                
                COUNT(DISTINCT p.id_project) as total_projects,
                SUM(CASE WHEN latest.status_name IN ('New', 'Qualifying', 'Design', 'Pending') THEN 1 ELSE 0 END) as active_projects,
                SUM(CASE WHEN latest.status_name = 'Contract Signed' THEN 1 ELSE 0 END) as signed_projects,
                SUM(CASE WHEN latest.status_name IN ('Completed', 'No Solution', 'Offer Refused') THEN 1 ELSE 0 END) as completed_projects,
                SUM(CASE WHEN latest.status_name = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_projects,
                
                COALESCE(SUM(p.tcv_project), 0) as total_tcv,
                COALESCE(SUM(CASE WHEN latest.status_name = 'Contract Signed' THEN p.tcv_project ELSE 0 END), 0) as signed_tcv,
                COALESCE(ROUND(AVG(p.tcv_project), 2), 0) as avg_project_value,
                
                DATE_FORMAT(MAX(latest.changed_at), '%Y-%m-%d') as last_activity_date,
                SUM(CASE WHEN MONTH(p.createDate_project) = MONTH(CURDATE()) AND YEAR(p.createDate_project) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as projects_this_month,
                SUM(CASE WHEN YEAR(p.createDate_project) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as projects_this_year,
                COALESCE(ROUND(AVG(DATEDIFF(CURDATE(), latest.changed_at))), 0) as avg_days_in_status
                
            FROM agents a
            LEFT JOIN projects p ON a.id_agent = p.agent_project
            LEFT JOIN (
                SELECT h1.project_id, h1.status_name, h1.changed_at
                FROM project_status_history h1
                INNER JOIN (
                    SELECT project_id, MAX(id_status) as last_id
                    FROM project_status_history
                    GROUP BY project_id
                ) h2 ON h1.id_status = h2.last_id
            ) latest ON p.id_project = latest.project_id
            WHERE a.status_agent = 1
            $whereTeam
            $whereDate
            $whereStatus
            GROUP BY a.id_agent
            ORDER BY total_tcv DESC
        ";

        // Build parameters array
        $params = [];
        if (!empty($filters['team'])) {
            $params = array_merge($params, $filters['team']);
        }
        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $params[] = $filters['dateFrom'];
            $params[] = $filters['dateTo'];
        }
        if (!empty($filters['status'])) {
            $params = array_merge($params, $filters['status']);
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * REPORT 2: Projects Since April 1st
     * Shows all projects with company, agent, financial, and reference details
     * Hardcoded: Regiune='Craiova', Presales='Daniel Neamu'
     * 
     * @param array $filters ['fiscalYear' => 'current'|'previous', 'team' => []]
     * @return array Projects data
     */
    public function getProjectsSinceApril($filters = [])
    {
        // Determine fiscal year dates
        $currentYear = date('Y');
        $currentMonth = date('n');

        if ($filters['fiscalYear'] === 'previous') {
            if ($currentMonth < 4) {
                $dateFrom = ($currentYear - 2) . '-04-01';
                $dateTo = ($currentYear - 1) . '-03-31';
            } else {
                $dateFrom = ($currentYear - 1) . '-04-01';
                $dateTo = $currentYear . '-03-31';
            }
        } else {
            // 'current'
            if ($currentMonth < 4) {
                $dateFrom = ($currentYear - 1) . '-04-01';
                $dateTo = $currentYear . '-03-31';
            } else {
                $dateFrom = $currentYear . '-04-01';
                $dateTo = date('Y-m-d');
            }
        }

        // Build WHERE clause for team filter only
        $whereTeam = '';

        if (!empty($filters['team'])) {
            $placeholders = implode(',', array_fill(0, count($filters['team']), '?'));
            $whereTeam = "AND a.current_team IN ($placeholders)";
        }

        $sql = "
            SELECT 
                c.name_companies as firma,
                'Craiova' as regiune,
                'Daniel Neamu' as presales,
                a.current_team as team,
                a.nume_agent as agent,
                p.name_project as proiect,
                
                CASE 
                    WHEN pp.partner_id IS NOT NULL THEN pt.name_parteneri
                    WHEN p.comment_project IS NOT NULL AND p.comment_project != '' 
                        THEN SUBSTRING_INDEX(p.comment_project, '#', 1)
                    ELSE ''
                END as comments_partner,
                
                CASE 
                    WHEN p.contract_project <= 12 THEN p.tcv_project
                    WHEN p.contract_project = 24 THEN p.tcv_project / 2
                    WHEN p.contract_project = 36 THEN p.tcv_project / 3
                    ELSE (p.tcv_project / p.contract_project) * 12
                END as aov,
                
                p.eft_command as eft,
                p.solution_dev_number as sd,
                p.eft_case as pt,
                p.sfdc_opp as sfdc,
                DATE_FORMAT(latest.changed_at, '%Y-%m-%d') as last_update_date,
                latest.status_name as last_status
                
            FROM projects p
            INNER JOIN companies c ON p.company_project = c.id_companies
            INNER JOIN agents a ON p.agent_project = a.id_agent
            LEFT JOIN (
                SELECT h1.project_id, h1.status_name, h1.changed_at
                FROM project_status_history h1
                INNER JOIN (
                    SELECT project_id, MAX(id_status) as last_id
                    FROM project_status_history
                    GROUP BY project_id
                ) h2 ON h1.id_status = h2.last_id
            ) latest ON p.id_project = latest.project_id
            LEFT JOIN project_partners pp ON p.id_project = pp.project_id AND pp.is_active = 1
            LEFT JOIN parteneri pt ON pp.partner_id = pt.id_parteneri
            
            WHERE p.createDate_project BETWEEN ? AND ?
            $whereTeam
            
            GROUP BY p.id_project
            ORDER BY p.createDate_project DESC
        ";

        // Build parameters array (only team filter)
        $params = [$dateFrom, $dateTo];
        if (!empty($filters['team'])) {
            $params = array_merge($params, $filters['team']);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * REPORT 3: Project Timeline
     * Detailed project list with status journey and current state
     * 
     * @param array $filters ['dateFrom' => '2025-01-01', 'dateTo' => '2025-10-31', 'team' => [], 'status' => [], 'projectType' => []]
     * @return array Projects timeline data
     */
    public function getProjectTimeline($filters = [])
    {
        // Build WHERE clause for filters
        $whereDate = '';
        $whereTeam = '';
        $whereStatus = '';
        $whereType = '';

        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $whereDate = "AND p.createDate_project BETWEEN ? AND ?";
        }

        if (!empty($filters['team'])) {
            $placeholders = implode(',', array_fill(0, count($filters['team']), '?'));
            $whereTeam = "AND a.current_team IN ($placeholders)";
        }

        if (!empty($filters['status'])) {
            $placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $whereStatus = "AND latest.status_name IN ($placeholders)";
        }

        if (!empty($filters['projectType'])) {
            $placeholders = implode(',', array_fill(0, count($filters['projectType']), '?'));
            $whereType = "AND p.project_type IN ($placeholders)";
        }

        $sql = "
            SELECT 
                p.id_project,
                p.name_project as project_name,
                c.name_companies as company_name,
                c.id_companies as company_id,
                a.nume_agent as agent_name,
                a.id_agent,
                a.current_team,
                
                DATE_FORMAT(p.createDate_project, '%Y-%m-%d') as created_date,
                DATE_FORMAT(latest.changed_at, '%Y-%m-%d') as last_updated,
                DATEDIFF(CURDATE(), latest.changed_at) as days_in_current_status,
                
                latest.status_name as current_status,
                previous.status_name as previous_status,
                
                (SELECT COUNT(*) FROM project_status_history WHERE project_id = p.id_project) as status_change_count,
                
                p.tcv_project,
                p.contract_project as contract_months,
                CASE 
                    WHEN p.contract_project <= 12 THEN p.tcv_project
                    WHEN p.contract_project = 24 THEN p.tcv_project / 2
                    WHEN p.contract_project = 36 THEN p.tcv_project / 3
                    ELSE (p.tcv_project / p.contract_project) * 12
                END as aov,
                
                p.eft_command,
                p.solution_dev_number,
                p.eft_case,
                p.sfdc_opp,
                
                p.project_type,
                GROUP_CONCAT(DISTINCT pt.name_parteneri SEPARATOR ', ') as partner_names,
                latest.responsible_party
                
            FROM projects p
            INNER JOIN companies c ON p.company_project = c.id_companies
            INNER JOIN agents a ON p.agent_project = a.id_agent
            LEFT JOIN (
                SELECT h1.project_id, h1.status_name, h1.changed_at, h1.responsible_party
                FROM project_status_history h1
                INNER JOIN (
                    SELECT project_id, MAX(id_status) as last_id
                    FROM project_status_history
                    GROUP BY project_id
                ) h2 ON h1.id_status = h2.last_id
            ) latest ON p.id_project = latest.project_id
            LEFT JOIN (
                SELECT h1.project_id, h1.status_name
                FROM project_status_history h1
                INNER JOIN (
                    SELECT project_id, MAX(id_status) - 1 as second_last_id
                    FROM project_status_history
                    WHERE id_status > 0
                    GROUP BY project_id
                    HAVING second_last_id > 0
                ) h2 ON h1.id_status = h2.second_last_id
            ) previous ON p.id_project = previous.project_id
            LEFT JOIN project_partners pp ON p.id_project = pp.project_id AND pp.is_active = 1
            LEFT JOIN parteneri pt ON pp.partner_id = pt.id_parteneri
            
            WHERE 1=1
            $whereDate
            $whereTeam
            $whereStatus
            $whereType
            
            GROUP BY p.id_project
            ORDER BY p.id_project DESC
        ";

        // Build parameters array
        $params = [];
        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $params[] = $filters['dateFrom'];
            $params[] = $filters['dateTo'];
        }
        if (!empty($filters['team'])) {
            $params = array_merge($params, $filters['team']);
        }
        if (!empty($filters['status'])) {
            $params = array_merge($params, $filters['status']);
        }
        if (!empty($filters['projectType'])) {
            $params = array_merge($params, $filters['projectType']);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** NEW
     * REPORT 4: Contract Signed Analysis
     * Projects where latest status = "Contract Signed"
     * 
     * @param array $filters ['dateRange' => 'april'|'last3months', 'sfdc' => 'all'|'has'|'empty', 'aov' => 'all'|'has'|'empty', 'active' => 'all'|'1'|'0']
     * @return array Projects data
     */
    public function getContractSignedAnalysis($filters = [])
    {
        $whereDate = '';
        $whereSfdc = '';
        $whereAov = '';
        $whereActive = '';

        // Date filter
        if (!empty($filters['dateRange'])) {
            if ($filters['dateRange'] === 'april') {
                $whereDate = "AND latest.changed_at >= DATE_FORMAT(CURDATE(), '%Y-04-01')";
            } elseif ($filters['dateRange'] === 'last3months') {
                $whereDate = "AND latest.changed_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            }
        }

        // SFDC filter
        if (!empty($filters['sfdc'])) {
            if ($filters['sfdc'] === 'has') {
                $whereSfdc = "AND p.sfdc_opp IS NOT NULL AND p.sfdc_opp != ''";
            } elseif ($filters['sfdc'] === 'empty') {
                $whereSfdc = "AND (p.sfdc_opp IS NULL OR p.sfdc_opp = '')";
            }
        }

        // AOV filter (check if TCV is zero/null after calculation)
        if (!empty($filters['aov'])) {
            if ($filters['aov'] === 'has') {
                $whereAov = "AND p.tcv_project > 0 AND p.tcv_project IS NOT NULL";
            } elseif ($filters['aov'] === 'empty') {
                $whereAov = "AND (p.tcv_project = 0 OR p.tcv_project IS NULL)";
            }
        }

        // Active filter
        if (!empty($filters['active']) && $filters['active'] !== 'all') {
            $active = $filters['active'] === '1' ? 1 : 0;
            $whereActive = "AND p.active_project = $active";
        }

        $sql = "
            SELECT 
                p.id_project as id,
                c.name_companies as firma,
                p.name_project as proiect,
                a.nume_agent as agent,
                p.eft_case as pt,
                p.solution_dev_number as sd,
                p.eft_command as eft,
                p.sfdc_opp,
                DATE_FORMAT(latest.changed_at, '%Y-%m-%d') as signed_date,
                CASE 
                    WHEN p.contract_project <= 12 THEN p.tcv_project
                    WHEN p.contract_project = 24 THEN p.tcv_project / 2
                    WHEN p.contract_project = 36 THEN p.tcv_project / 3
                    ELSE (p.tcv_project / p.contract_project) * 12
                END as aov,
                p.active_project
                
            FROM projects p
            INNER JOIN companies c ON p.company_project = c.id_companies
            INNER JOIN agents a ON p.agent_project = a.id_agent
            LEFT JOIN (
                SELECT h1.project_id, h1.status_name, h1.changed_at
                FROM project_status_history h1
                INNER JOIN (
                    SELECT project_id, MAX(id_status) as last_id
                    FROM project_status_history
                    GROUP BY project_id
                ) h2 ON h1.id_status = h2.last_id
            ) latest ON p.id_project = latest.project_id
            
            WHERE latest.status_name = 'Contract Signed'
            $whereDate
            $whereSfdc
            $whereAov
            $whereActive
            
            ORDER BY latest.changed_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Get filter options for dropdowns
     * Returns unique values for team, status, project type
     * 
     * @return array Filter options
     */
    public function getFilterOptions()
    {
        return [
            'teams' => $this->conn->query(
                "SELECT DISTINCT current_team FROM agents WHERE status_agent = 1 ORDER BY current_team"
            )->fetchAll(PDO::FETCH_COLUMN),

            'statuses' => ['New', 'Qualifying', 'Design', 'Pending', 'Contract Signed', 'Completed', 'Cancelled', 'Offer Refused', 'No Solution'],

            'projectTypes' => $this->conn->query(
                "SELECT DISTINCT project_type FROM projects WHERE project_type IS NOT NULL ORDER BY project_type"
            )->fetchAll(PDO::FETCH_COLUMN)
        ];
    }
}
