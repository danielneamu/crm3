<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';  // ADD THIS LINE
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AgentController.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    $controller = new AgentController($conn);

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    if ($method === 'GET') {
        if ($action === 'list') {
            echo json_encode($controller->getAllAgents());
        } elseif ($action === 'teams') {
            echo json_encode($controller->getAllTeams());
        } elseif ($action === 'team_history' && isset($_GET['id'])) {
            echo json_encode($controller->getTeamHistory($_GET['id']));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
        }
    } elseif ($method === 'POST') {
        parse_str(file_get_contents('php://input'), $_POST);
        echo json_encode($controller->createAgent($_POST));
    } elseif ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $_POST);
        echo json_encode($controller->updateAgent($_POST));
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
