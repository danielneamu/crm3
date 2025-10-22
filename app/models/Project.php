<?php
class Project {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAll() {
        return $this->conn->query("
            SELECT 
                p.id_project,
                c.name_companies AS firma,
                p.name_project AS proiect,
                c.fiscal_code AS cui,
                a.nume_agent AS agent,
                a.current_team AS team,
                p.eft_command AS pt,
                p.solution_dev_number AS sd,
                p.eft_case AS eft,
                p.sfdc_opp AS sfdc,
                DATE_FORMAT(p.createDate_project, '%d-%m-%Y') AS create_date,
                DATE_FORMAT((SELECT MAX(psh.changed_at) FROM project_status_history psh
                 WHERE psh.project_id = p.id_project), '%d-%m-%Y') AS last_update,
                (SELECT psh.status_name FROM project_status_history psh
                 WHERE psh.project_id = p.id_project
                 ORDER BY psh.changed_at DESC LIMIT 1) AS status,
                (SELECT psh.responsible_party FROM project_status_history psh
                 WHERE psh.project_id = p.id_project
                 ORDER BY psh.changed_at DESC LIMIT 1) AS assigned,
                DATE_FORMAT((SELECT psh.deadline FROM project_status_history psh
                 WHERE psh.project_id = p.id_project
                 ORDER BY psh.changed_at DESC LIMIT 1), '%d-%m-%Y') AS dl,
                p.tcv_project AS aov,
                p.active_project AS on_status,
                p.comment_project AS comments,
                p.project_type AS type,
                (SELECT GROUP_CONCAT(pt.name_parteneri SEPARATOR ', ')
                 FROM project_partners pp
                 JOIN parteneri pt ON pp.partner_id = pt.id_parteneri
                 WHERE pp.project_id = p.id_project AND pp.is_active = 1) AS partners
            FROM projects p
            LEFT JOIN companies c ON p.company_project = c.id_companies
            LEFT JOIN agents a ON p.agent_project = a.id_agent
            ORDER BY p.id_project DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
}
