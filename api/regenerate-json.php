<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Project.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);

    $projects = $projectModel->getAll();

    $jsonFile = __DIR__ . '/../public/data/projects.json';
    $dir = dirname($jsonFile);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($jsonFile, json_encode(['data' => $projects], JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'message' => 'JSON regenerated successfully',
        'total_projects' => count($projects),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
