<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once '../config/config.php';  // ← ADD THIS
require_once '../config/database.php';
require_once '../app/controllers/PartnerController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new PartnerController($db);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $partners = $controller->getAllPartners();
            echo json_encode($partners);
            break;

        case 'get':
            $id = $_GET['id'] ?? 0;
            $partner = $controller->getPartner($id);
            echo json_encode($partner ?? ['error' => 'Partner not found']);
            break;

        case 'tags':
            $tags = $controller->getAllTags();
            echo json_encode($tags);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $controller->savePartner($data);
            echo json_encode($result);
            break;

        case 'saveTag':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $controller->saveTag($data);
            echo json_encode($result);
            break;

        case 'deleteTag':
            $id = $_GET['id'] ?? 0;
            $result = $controller->deleteTag($id);
            echo json_encode($result);
            break;


        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}


