<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

requireLogin();

set_exception_handler(function ($e) {
    error_log('SFDC Product Pipeline API Exception: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
});

set_error_handler(function ($severity, $message, $file, $line) {
    error_log("SFDC Product Pipeline API Error: {$message} in {$file} on line {$line}");

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $message
    ]);
    exit;
});

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log('SFDC Product Pipeline API DB Connection Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit;
}

require_once __DIR__ . '/../app/controllers/SfdcProductPipelineController.php';

try {
    $controller = new SfdcProductPipelineController($conn);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_products':
            $controller->getProducts();
            break;

        case 'get_product_by_id':
            $id = $_GET['id'] ?? 0;
            $controller->getProductById($id);
            break;

        case 'get_product_families':
            $controller->getProductFamilies();
            break;

        case 'get_dashboard_data':
            $controller->getDashboardData();
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
            break;
    }
} catch (Exception $e) {
    error_log('SFDC Product Pipeline API Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
