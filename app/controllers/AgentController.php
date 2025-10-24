<?php
class AgentController
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function getAllAgents()
    {
        $sql = "
        SELECT 
            a.id_agent,
            a.nume_agent,
            a.cod_agent,
            a.current_team,
            a.status_agent,
            COUNT(DISTINCT CASE WHEN p.active_project = 1 THEN p.id_project END) as active_projects,
            MIN(h.start_date) as member_since
        FROM agents a
        LEFT JOIN projects p ON p.agent_project = a.id_agent
        LEFT JOIN agent_team_history h ON h.agent_id = a.id_agent
        GROUP BY a.id_agent
        ORDER BY a.status_agent DESC, a.nume_agent ASC
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTeams()
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT current_team 
            FROM agents 
            WHERE current_team != '' 
            ORDER BY current_team
        ");
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'current_team');
    }

    public function createAgent($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO agents (nume_agent, cod_agent, current_team, status_agent, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['nume_agent'],
            $data['cod_agent'],
            $data['current_team'],
            $data['status_agent'] ?? 1
        ]);

        $agentId = $this->conn->lastInsertId();

        $stmt = $this->conn->prepare("
            INSERT INTO agent_team_history (agent_id, team_name, start_date, end_date, notes)
            VALUES (?, ?, ?, NULL, 'Initial assignment')
        ");
        $stmt->execute([
            $agentId,
            $data['current_team'],
            $data['effective_date'] ?? date('Y-m-d')
        ]);

        return ['success' => true, 'id' => $agentId];
    }

    public function updateAgent($data)
    {
        $stmt = $this->conn->prepare("SELECT current_team FROM agents WHERE id_agent = ?");
        $stmt->execute([$data['id_agent']]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($current['current_team'] !== $data['current_team']) {
            $stmt = $this->conn->prepare("
                UPDATE agent_team_history 
                SET end_date = DATE_SUB(?, INTERVAL 1 DAY)
                WHERE agent_id = ? AND end_date IS NULL
            ");
            $stmt->execute([
                $data['effective_date'] ?? date('Y-m-d'),
                $data['id_agent']
            ]);

            $stmt = $this->conn->prepare("
                INSERT INTO agent_team_history (agent_id, team_name, start_date, end_date, notes)
                VALUES (?, ?, ?, NULL, ?)
            ");
            $stmt->execute([
                $data['id_agent'],
                $data['current_team'],
                $data['effective_date'] ?? date('Y-m-d'),
                $data['change_notes'] ?? ''
            ]);
        }

        $stmt = $this->conn->prepare("
            UPDATE agents 
            SET nume_agent = ?, cod_agent = ?, current_team = ?, status_agent = ?
            WHERE id_agent = ?
        ");
        $stmt->execute([
            $data['nume_agent'],
            $data['cod_agent'],
            $data['current_team'],
            $data['status_agent'] ?? 1,
            $data['id_agent']
        ]);

        return ['success' => true];
    }

    public function getTeamHistory($agentId)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                team_name,
                start_date,
                end_date,
                notes,
                DATEDIFF(IFNULL(end_date, CURDATE()), start_date) as days
            FROM agent_team_history
            WHERE agent_id = ?
            ORDER BY start_date DESC
        ");
        $stmt->execute([$agentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
