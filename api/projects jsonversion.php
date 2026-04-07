<?php

/**
 * PROJECTS API ENDPOINT
 * Handles GET (existing), POST, PUT, DELETE operations
 * Reference: Called by public/assets/js/projects_dt.js and projects-actions.js
 */
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../app/controllers/ProjectController.php';

requireLogin();
header('Content-Type: application/json');

try {
    $db = new Database();
    $controller = new ProjectController($db->getConnection());
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET': // Existing - get all projects
            $result = $controller->getProjectsJson();

            if ($result['success']) {
                echo json_encode(['data' => $result['data']]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $result['error']]);
            }
            break;

        case 'POST': // Create new project
            $result = $controller->createProject($_POST);

            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => $result['error']]);
            }
            break;

        case 'PUT': // Update project
            parse_str(file_get_contents("php://input"), $_PUT);
            $id = $_PUT['id_project'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Project ID required']);
                break;
            }

            $result = $controller->updateProject($id, $_PUT);

            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => $result['error']]);
            }
            break;

        case 'DELETE': // Delete project
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Project ID required');
            }

            $result = $controller->deleteProject($id);

            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => $result['error']]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
