<?php

require_once __DIR__ . '/../config/config.php';      
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $projectId = $_GET['project_id'] ?? null;

            if (!$projectId) {
                http_response_code(400);
                echo json_encode(['error' => 'Project ID required']);
                exit;
            }

            $stmt = $conn->prepare("
                SELECT 
                    id_status,
                    project_id,
                    status_name,
                    responsible_party,
                    DATE_FORMAT(changed_at, '%Y-%m-%d') as changed_at,
                    DATE_FORMAT(deadline, '%Y-%m-%d') as deadline,
                    comment
                FROM project_status_history 
                WHERE project_id = ? 
                ORDER BY id_status DESC
            ");
            $stmt->execute([$projectId]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            $projectId = $_POST['project_id'] ?? null;
            $statusName = $_POST['status_name'] ?? null;
            $responsible = $_POST['responsible_party'] ?? null;
            $changedAt = $_POST['changed_at'] ?? date('Y-m-d');
            $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
            $comments = !empty($_POST['comments']) ? $_POST['comments'] : null;

            if (!$projectId || !$statusName || !$responsible) {
                http_response_code(400);
                echo json_encode(['error' => 'Required fields missing']);
                exit;
            }

            $stmt = $conn->prepare("
                INSERT INTO project_status_history 
                (project_id, status_name, responsible_party, changed_at, deadline, comment)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([$projectId, $statusName, $responsible, $changedAt, $deadline, $comments]);
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Status ID required']);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM project_status_history WHERE id_status = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
