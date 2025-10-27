<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../app/controllers/CompanyController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new CompanyController($db);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $companies = $controller->getAllCompanies();
            echo json_encode($companies);
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $company = $controller->getCompany($id);
            echo json_encode($company ?? ['error' => 'Company not found']);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $controller->saveCompany($data);
            echo json_encode($result);
            break;

        case 'delete':
            $id = $_GET['id'] ?? 0;
            $result = $controller->deleteCompany($id);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
