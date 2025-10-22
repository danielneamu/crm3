<?php

/**
 * PROJECTS API ENDPOINT
 * Reference: Called by public/assets/js/projects_dt.js
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
    $result = $controller->getProjectsJson();

    if ($result['success']) {
        echo json_encode(['data' => $result['data']]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $result['error']]);
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
