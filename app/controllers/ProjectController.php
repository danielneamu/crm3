<?php
class ProjectController
{
    private $model;
    private $conn;

    public function __construct($db)
    {
        require_once __DIR__ . '/../models/Project.php';
        $this->conn = $db;  // Store connection
        $this->model = new Project($db);
    }

    public function createProject($data)
    {
        try {
            $this->conn->beginTransaction();

            $createDate = !empty($data['createDate_project'])
                ? date('Y-m-d', strtotime(str_replace('-', '/', $data['createDate_project'])))
                : date('Y-m-d');

            $stmt = $this->conn->prepare("
            INSERT INTO projects (
                name_project, company_project, agent_project, project_type,
                tcv_project, contract_project, eft_command, solution_dev_number, 
                eft_case, sfdc_opp, active_project, createDate_project, comment_project
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

            $stmt->execute([
                $data['name_project'],
                $data['company_project'],
                $data['agent_project'],
                $data['project_type'] ?? null,
                !empty($data['tcv_project']) ? $data['tcv_project'] : 0,
                !empty($data['contract_project']) ? $data['contract_project'] : null,
                $data['eft_command'] ?? null,
                $data['solution_dev_number'] ?? null,
                $data['eft_case'] ?? null,
                $data['sfdc_opp'] ?? null,
                isset($data['active_project']) ? 1 : 0,
                $createDate,
                $data['comment_project'] ?? null  // Single comment field
            ]);

            $projectId = $this->conn->lastInsertId();

            // Insert initial status
            $stmtStatus = $this->conn->prepare("
            INSERT INTO project_status_history (
                project_id, status_name, responsible_party, deadline, changed_at
            ) VALUES (?, 'New', 'Presales', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW())
        ");
            $stmtStatus->execute([$projectId]);

            // Insert partners
            if (!empty($data['partners'])) {
                $this->updateProjectPartners($projectId, $data['partners']);
            }

            $this->conn->commit();

            return ['success' => true, 'id' => $projectId];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    public function updateProject($id, $data)
    {
        try {
            $this->conn->beginTransaction();

            $createDate = !empty($data['createDate_project'])
                ? date('Y-m-d', strtotime(str_replace('-', '/', $data['createDate_project'])))
                : date('Y-m-d');

            $stmt = $this->conn->prepare("
            UPDATE projects SET
                name_project = ?,
                company_project = ?,
                agent_project = ?,
                project_type = ?,
                tcv_project = ?,
                contract_project = ?,
                eft_command = ?,
                solution_dev_number = ?,
                eft_case = ?,
                sfdc_opp = ?,
                active_project = ?,
                createDate_project = ?,
                comment_project = ?
            WHERE id_project = ?
        ");

            $stmt->execute([
                $data['name_project'],
                $data['company_project'],
                $data['agent_project'],
                $data['project_type'] ?? null,
                !empty($data['tcv_project']) ? $data['tcv_project'] : null,
                !empty($data['contract_project']) ? $data['contract_project'] : null,
                $data['eft_command'] ?? null,
                $data['solution_dev_number'] ?? null,
                $data['eft_case'] ?? null,
                $data['sfdc_opp'] ?? null,
                isset($data['active_project']) ? 1 : 0,
                $createDate,
                $data['comment_project'] ?? null,
                $id
            ]);

            // Update partners
            if (isset($data['partners'])) {
                $this->updateProjectPartners($id, $data['partners']);
            }

            $this->conn->commit();

            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    private function regenerateJson()
    {
        try {
            // Create a fresh database connection for JSON generation
            $db = new Database();
            $freshConn = $db->getConnection();

            // Create a new Project model with fresh connection
            $projectModel = new Project($freshConn);

            // Get all projects
            $projects = $projectModel->getAll();

            // Save to JSON file
            $jsonFile = __DIR__ . '/../../public/data/projects.json';

            // Ensure directory exists
            $dir = dirname($jsonFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($jsonFile, json_encode(['data' => $projects], JSON_PRETTY_PRINT));

            return true;
        } catch (Exception $e) {
            error_log('JSON regeneration failed: ' . $e->getMessage());
            return false;
        }
    }




    public function deleteProject($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM projects WHERE id_project = ?");
            $stmt->execute([$id]);

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function updateProjectPartners($projectId, $partnerIds)
    {
        // First, deactivate all existing partners for this project
        $stmt = $this->conn->prepare("
        UPDATE project_partners 
        SET is_active = 0 
        WHERE project_id = ?
    ");
        $stmt->execute([$projectId]);

        // If no partners selected, we're done
        if (empty($partnerIds)) {
            return;
        }

        // Handle both array and string input
        if (is_string($partnerIds)) {
            $partnerIds = explode(',', $partnerIds);
        }

        // Insert or reactivate each selected partner
        foreach ($partnerIds as $partnerId) {
            if (empty($partnerId)) continue;

            // Try to reactivate existing record first
            $stmt = $this->conn->prepare("
            UPDATE project_partners 
            SET is_active = 1 
            WHERE project_id = ? AND partner_id = ?
        ");
            $stmt->execute([$projectId, $partnerId]);

            // If no rows were updated, insert new record
            if ($stmt->rowCount() == 0) {
                $stmt = $this->conn->prepare("
                INSERT INTO project_partners (project_id, partner_id, is_active) 
                VALUES (?, ?, 1)
            ");
                $stmt->execute([$projectId, $partnerId]);
            }
        }
    }


    private function addProjectComments($projectId, $comments)
    {
        $stmt = $this->conn->prepare("INSERT INTO project_comments (project_id, comment_text, created_at) VALUES (?, ?, ?)");
        foreach ($comments as $comment) {
            if (!isset($comment['id_comment'])) {
                // Convert ISO 8601 to MySQL datetime
                $createdAt = isset($comment['created_at'])
                    ? date('Y-m-d H:i:s', strtotime($comment['created_at']))
                    : date('Y-m-d H:i:s');

                $stmt->execute([
                    $projectId,
                    $comment['comment_text'],
                    $createdAt
                ]);
            }
        }
    }


    // Keep your existing getProjectsJson() method
    public function generateJson()
    {
        $data = $this->model->getAll();
        $file = __DIR__ . '/../../public/data/projects.json';
        @mkdir(dirname($file), 0755, true);
        file_put_contents($file, json_encode(['data' => $data]));
    }
}
