<?php

require_once __DIR__ . '/sfdc_common.php';
require_once __DIR__ . '/../app/controllers/SfdcWonController.php';

try {
    $controller = new SfdcWonController($conn);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_won':
            $controller->getWon();
            break;

        case 'get_won_by_id':
            $id = $_GET['id'] ?? 0;
            $controller->getWonById($id);
            break;

        case 'update_won_field':
            $controller->updateWonField($_POST);
            break;

        case 'get_type_options':
            $controller->getTypeOptions();
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
    error_log('SFDC Won API Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
