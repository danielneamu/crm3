<?php
require_once '../config/config.php';  
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();  // ← Changed
    $conn = $db->getConnection();

    if (isset($_GET['project_id'])) {
        $projectId = $_GET['project_id'];

        $stmt = $conn->prepare("
            SELECT id_comment, project_id, comment_text, created_at 
            FROM project_comments 
            WHERE project_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$projectId]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        throw new Exception('Project ID required');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
