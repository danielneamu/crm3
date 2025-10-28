<?php

/**
 * Dashboard Controller
 * Handles all dashboard statistics and chart data
 */
class DashboardController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ===== STATISTICS =====

    /**
     * Get all dashboard statistics in one call
     * @return array All statistics with comparisons
     */
    public function getStatistics()
    {
        return [
            'total_projects' => $this->getTotalProjects(),
            'completed_projects' => $this->getCompletedProjects(),
            'signed_projects' => $this->getSignedProjects(),
            'ongoing_projects' => $this->getOngoingProjects(),
            'opened_this_month' => $this->getOpenedThisMonth(),
            'opened_last_month' => $this->getOpenedLastMonth(),
            'completed_this_month' => $this->getCompletedThisMonth(),
            'completed_last_month' => $this->getCompletedLastMonth(),
            'signed_this_month' => $this->getSignedThisMonth(),
            'signed_last_month' => $this->getSignedLastMonth(),
        ];
    }

    /**
     * Get total count of all projects
     */
    private function getTotalProjects()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as count FROM projects");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get completed projects (Contract Signed, Completed, No Solution, Offer Refused)
     */
    private function getCompletedProjects()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(DISTINCT p.id_project) as count
            FROM projects p
            INNER JOIN (
                SELECT project_id, status_name
                FROM project_status_history psh1
                WHERE changed_at = (
                    SELECT MAX(changed_at)
                    FROM project_status_history psh2
                    WHERE psh2.project_id = psh1.project_id
                )
            ) latest ON p.id_project = latest.project_id
            WHERE latest.status_name IN ('Contract Signed', 'Completed', 'No Solution', 'Offer Refused')
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get signed projects (Contract Signed only)
     */
    private function getSignedProjects()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(DISTINCT p.id_project) as count
            FROM projects p
            INNER JOIN (
                SELECT project_id, status_name
                FROM project_status_history psh1
                WHERE changed_at = (
                    SELECT MAX(changed_at)
                    FROM project_status_history psh2
                    WHERE psh2.project_id = psh1.project_id
                )
            ) latest ON p.id_project = latest.project_id
            WHERE latest.status_name = 'Contract Signed'
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get ongoing projects (Design, New, Qualifying, Pending)
     */
    private function getOngoingProjects()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(DISTINCT p.id_project) as count
            FROM projects p
            INNER JOIN (
                SELECT project_id, status_name
                FROM project_status_history psh1
                WHERE changed_at = (
                    SELECT MAX(changed_at)
                    FROM project_status_history psh2
                    WHERE psh2.project_id = psh1.project_id
                )
            ) latest ON p.id_project = latest.project_id
            WHERE latest.status_name IN ('Design', 'New', 'Qualifying', 'Pending')
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get projects opened this month
     */
    private function getOpenedThisMonth()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*) as count
            FROM projects
            WHERE MONTH(createDate_project) = MONTH(CURRENT_DATE())
            AND YEAR(createDate_project) = YEAR(CURRENT_DATE())
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get projects opened last month
     */
    private function getOpenedLastMonth()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*) as count
            FROM projects
            WHERE MONTH(createDate_project) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
            AND YEAR(createDate_project) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get projects completed this month
     */
    private function getCompletedThisMonth()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(DISTINCT psh.project_id) as count
            FROM project_status_history psh
            WHERE psh.status_name IN ('Contract Signed', 'Completed', 'No Solution', 'Offer Refused')
            AND MONTH(psh.changed_at) = MONTH(CURRENT_DATE())
            AND YEAR(psh.changed_at) = YEAR(CURRENT_DATE())
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get projects completed last month
     */
    private function getCompletedLastMonth()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(DISTINCT psh.project_id) as count
            FROM project_status_history psh
            WHERE psh.status_name IN ('Contract Signed', 'Completed', 'No Solution', 'Offer Refused')
            AND MONTH(psh.changed_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
            AND YEAR(psh.changed_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get projects signed this month
     */
    private function getSignedThisMonth()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(DISTINCT psh.project_id) as count
            FROM project_status_history psh
            WHERE psh.status_name = 'Contract Signed'
            AND MONTH(psh.changed_at) = MONTH(CURRENT_DATE())
            AND YEAR(psh.changed_at) = YEAR(CURRENT_DATE())
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get projects signed last month
     */
    private function getSignedLastMonth()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(DISTINCT psh.project_id) as count
            FROM project_status_history psh
            WHERE psh.status_name = 'Contract Signed'
            AND MONTH(psh.changed_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
            AND YEAR(psh.changed_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ");
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }



    /**
     * Get total companies count
     * @return int Total companies
     */
    private function getTotalCompanies()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as count FROM companies");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get total partners count
     * @return int Total partners
     */
    private function getTotalPartners()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as count FROM parteneri");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get count of active agents (status_agent = 1)
     * @return int Active agents count
     */
    private function getActiveAgents()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as count FROM agents WHERE status_agent = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    // ===== CHARTS DATA =====


    /**
     * CHART: Monthly Status History (Full Width)
     * Get NEW projects opened each month (all-time data)
     * @return array [{month: "2024-01", count: 12}, ...]
     */
    public function getMonthlyNewProjects()
    {
        $stmt = $this->conn->query("
            SELECT 
                DATE_FORMAT(createDate_project, '%Y-%m') as month,
                COUNT(*) as count
            FROM projects
            GROUP BY DATE_FORMAT(createDate_project, '%Y-%m')
            ORDER BY month ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * CHART: Monthly Status History (Full Width)
     * Get COMPLETED projects each month (reached final status)
     * @return array [{month: "2024-01", count: 8}, ...]
     */
    public function getMonthlyCompletedProjects()
    {
        $stmt = $this->conn->query("
            SELECT 
                DATE_FORMAT(psh.changed_at, '%Y-%m') AS month,
                COUNT(DISTINCT psh.project_id) AS count
            FROM project_status_history psh
            WHERE psh.status_name IN ('Contract Signed', 'Completed', 'No Solution', 'Offer Refused')
            GROUP BY DATE_FORMAT(psh.changed_at, '%Y-%m')
            ORDER BY month ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**
     * CHART: Monthly Status History (Full Width)
     * Get SIGNED projects each month (Contract Signed status)
     * @return array [{month: "2024-01", count: 5}, ...]
     */
    public function getMonthlySignedProjects()
    {
        $stmt = $this->conn->query("
            SELECT 
                DATE_FORMAT(psh.changed_at, '%Y-%m') as month,
                COUNT(DISTINCT psh.project_id) as count
            FROM project_status_history psh
            WHERE psh.status_name = 'Contract Signed'
            GROUP BY DATE_FORMAT(psh.changed_at, '%Y-%m')
            ORDER BY month ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * CHART: Fiscal Year Comparison (Projects Timeline)
     * Compare current fiscal year (Apr-now) vs previous fiscal year (Apr-Mar)
     * Fiscal year starts April 1st
     * @return array ['current_fy' => [...], 'previous_fy' => [...]]
     */
    public function getFiscalYearComparison()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        // Determine fiscal year boundaries (April–March)
        if ($currentMonth < 4) {
            // We are in Jan–Mar → fiscal year started last April (previous calendar year)
            $currentFYStart = ($currentYear - 1) . '-04-01';
            $currentFYEnd = $currentYear . '-03-31';
            $previousFYStart = ($currentYear - 2) . '-04-01';
            $previousFYEnd = ($currentYear - 1) . '-03-31';
            $currentFYLabel = 'FY ' . ($currentYear - 1) . '-' . substr($currentYear, 2, 2);
            $previousFYLabel = 'FY ' . ($currentYear - 2) . '-' . substr($currentYear - 1, 2, 2);
        } else {
            // We are in Apr–Dec → current FY starts this April
            $currentFYStart = $currentYear . '-04-01';
            $currentFYEnd = date('Y-m-d'); // up to now
            $previousFYStart = ($currentYear - 1) . '-04-01';
            $previousFYEnd = $currentYear . '-03-31'; // FIX: go until March next year
            $currentFYLabel = 'FY ' . $currentYear . '-' . substr($currentYear + 1, 2, 2);
            $previousFYLabel = 'FY ' . ($currentYear - 1) . '-' . substr($currentYear, 2, 2);
        }

        // Query for current FY (Apr–now)
        $stmtCurrent = $this->conn->prepare("
            SELECT 
                MONTH(createDate_project) AS month,
                COUNT(*) AS count
            FROM projects
            WHERE createDate_project BETWEEN ? AND ?
            GROUP BY MONTH(createDate_project)
            ORDER BY MONTH(createDate_project)
        ");
        $stmtCurrent->execute([$currentFYStart, $currentFYEnd]);
        $currentFY = $stmtCurrent->fetchAll(PDO::FETCH_ASSOC);

        // Query for previous FY (Apr–Mar)
        $stmtPrevious = $this->conn->prepare("
            SELECT 
                MONTH(createDate_project) AS month,
                COUNT(*) AS count
            FROM projects
            WHERE createDate_project BETWEEN ? AND ?
            GROUP BY MONTH(createDate_project)
            ORDER BY MONTH(createDate_project)
        ");
        $stmtPrevious->execute([$previousFYStart, $previousFYEnd]);
        $previousFY = $stmtPrevious->fetchAll(PDO::FETCH_ASSOC);

        return [
            'current_fy' => $currentFY,
            'previous_fy' => $previousFY,
            'current_fy_label' => $currentFYLabel,
            'previous_fy_label' => $previousFYLabel
        ];
    }


    /**
     * CHART: Projects Timeline (Last N Months - existing chart)
     * Get projects grouped by month for timeline bar chart
     * @param int $months Number of months to look back (default: 12)
     * @return array [{month: "2025-01", count: 12}, ...]
     */
    public function getProjectsByMonth($months = 12)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE_FORMAT(createDate_project, '%Y-%m') as month,
                COUNT(*) as count
            FROM projects
            WHERE createDate_project >= DATE_SUB(CURRENT_DATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(createDate_project, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get projects grouped by type for pie/donut chart
     * @return array [{type: "Type Name", count: 15}, ...]
     */
    public function getProjectsByType()
    {
        $stmt = $this->conn->query("
            SELECT 
                COALESCE(project_type, 'Unknown') as type,
                COUNT(*) as count
            FROM projects
            GROUP BY project_type
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top agents by project count for bar chart
     * @param int $limit Number of top agents to return (default: 10)
     * @return array [{agent: "Agent Name", count: 25}, ...]
     */
    public function getProjectsByAgent($limit = 10)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                a.nume_agent as agent,
                COUNT(p.id_project) as count
            FROM agents a
            LEFT JOIN projects p ON a.id_agent = p.agent_project
            WHERE a.status_agent = 1
            GROUP BY a.id_agent
            HAVING count > 0
            ORDER BY count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get projects grouped by team for pie chart
     * @return array [{team: "Team Name", count: 18}, ...]
     */
    public function getProjectsByTeam()
    {
        $stmt = $this->conn->query("
            SELECT 
                a.current_team as team,
                COUNT(p.id_project) as count
            FROM agents a
            LEFT JOIN projects p ON a.id_agent = p.agent_project
            WHERE a.current_team IS NOT NULL AND a.current_team != ''
            GROUP BY a.current_team
            HAVING count > 0
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get most recent projects for dashboard table
     * @param int $limit Number of projects to return (default: 10)
     * @return array Recent projects with company and agent info
     */
    public function getRecentProjects()
    {
        $stmt = $this->conn->prepare("
        SELECT 
            p.id_project,
            p.name_project,
            c.name_companies,
            a.nume_agent,
            p.createDate_project,
            latest.status_name AS current_status,
            p.tcv_project
        FROM projects p
        LEFT JOIN companies c ON p.company_project = c.id_companies
        LEFT JOIN agents a ON p.agent_project = a.id_agent
        LEFT JOIN (
            SELECT h1.project_id, h1.status_name
            FROM project_status_history h1
            INNER JOIN (
                SELECT project_id, MAX(id_status) AS last_status_id
                FROM project_status_history
                GROUP BY project_id
            ) h2 ON h1.id_status = h2.last_status_id
        ) latest 
            ON p.id_project = latest.project_id
        WHERE latest.status_name IN ('New', 'Design', 'Qualifying')
        ORDER BY p.createDate_project DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get recent project status changes from history table
     * @param int $limit Number of activities to return (default: 10)
     * @return array Recent status changes with project details
     */
    public function getRecentActivity($limit = 10)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                psh.id_status,
                psh.project_id,
                p.name_project,
                c.name_companies,
                psh.status_name,
                psh.responsible_party,
                psh.changed_at,
                psh.comment
            FROM project_status_history psh
            LEFT JOIN projects p ON psh.project_id = p.id_project
            LEFT JOIN companies c ON p.company_project = c.id_companies
            ORDER BY psh.changed_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**
     * CHART: Projects by Team (from agents.current_team) for current fiscal year
     * Counts projects grouped by the agent's team.
     * 
     * Relationship:
     *   projects.agent_project → agents.id_agent → agents.current_team
     */
    public function getProjectsByTeamFiscalYear()
    {
        // Fiscal year start (April 1)
        $currentYear = date('Y');
        $currentMonth = date('n');
        $fiscalYearStart = ($currentMonth < 4) ? ($currentYear - 1) . '-04-01' : $currentYear . '-04-01';

        $stmt = $this->conn->prepare("
            SELECT 
                IFNULL(NULLIF(a.current_team, ''), 'Unassigned') AS team,
                COUNT(p.id_project) AS count
            FROM projects p
            LEFT JOIN agents a ON p.agent_project = a.id_agent
            WHERE p.createDate_project >= ?
            GROUP BY a.current_team
            ORDER BY count DESC
        ");
        $stmt->execute([$fiscalYearStart]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * CHART: Agent Status Summary (Top 10 Agents)
     * Uses the latest id_status for each project to determine its final state.
     * Filters by active agents only (status_agent = 1).
     * 
     * @param string $period "fiscal" or "all"
     * @return array
     */
    public function getAgentStatusSummary($period = 'fiscal')
    {
        $params = [];
        $where = "";

        // Filter by fiscal year (April 1st)
        if ($period === 'fiscal') {
            $currentYear = date('Y');
            $currentMonth = date('n');
            $fiscalYearStart = ($currentMonth < 4)
                ? ($currentYear - 1) . '-04-01'
                : $currentYear . '-04-01';
            $where = "WHERE p.createDate_project >= ?";
            $params[] = $fiscalYearStart;
        }

        // Final SQL Query (uses latest id_status)
        $sql = "
            SELECT 
                a.nume_agent AS agent,
                SUM(latest.status_name = 'Contract Signed') AS contract_signed,
                SUM(latest.status_name = 'Completed') AS completed,
                SUM(latest.status_name IN ('New', 'Design', 'Qualifying', 'Pending')) AS in_progress,
                SUM(latest.status_name IN ('Cancelled', 'No Solution', 'Offer Refused')) AS cancelled,
                COUNT(p.id_project) AS total_projects
            FROM projects p
            JOIN agents a 
                ON p.agent_project = a.id_agent 
                AND a.status_agent = 1
            LEFT JOIN (
                SELECT h1.project_id, h1.status_name
                FROM project_status_history h1
                INNER JOIN (
                    SELECT project_id, MAX(id_status) AS last_status_id
                    FROM project_status_history
                    GROUP BY project_id
                ) h2 ON h1.id_status = h2.last_status_id
            ) latest 
                ON p.id_project = latest.project_id
        $where
            GROUP BY a.nume_agent
            ORDER BY total_projects DESC
            LIMIT 10
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * CHART: In-Progress Projects by Agent (stacked, top 10 agents)
     * Includes active agents only (status_agent = 1)
     * Uses latest status (MAX(id_status)) per project.
     *
     * Displays counts for New, Qualifying, Design, and Pending statuses.
     *
     * @return array
     */
    public function getInProgressByAgent()
    {
        $sql = "
        SELECT 
            a.nume_agent AS agent,
            SUM(latest.status_name = 'New') AS new_projects,
            SUM(latest.status_name = 'Qualifying') AS qualifying_projects,
            SUM(latest.status_name = 'Design') AS design_projects,
            SUM(latest.status_name = 'Pending') AS pending_projects,
            COUNT(latest.project_id) AS total_in_progress
        FROM projects p
        JOIN agents a 
            ON p.agent_project = a.id_agent 
            AND a.status_agent = 1
        LEFT JOIN (
            SELECT h1.project_id, h1.status_name
            FROM project_status_history h1
            INNER JOIN (
                SELECT project_id, MAX(id_status) AS last_status_id
                FROM project_status_history
                GROUP BY project_id
            ) h2 ON h1.id_status = h2.last_status_id
        ) latest 
            ON p.id_project = latest.project_id
        WHERE latest.status_name IN ('New', 'Design', 'Qualifying', 'Pending')
        GROUP BY a.nume_agent
        ORDER BY total_in_progress DESC
        LIMIT 10
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
