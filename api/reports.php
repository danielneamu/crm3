<?php

/**
 * Reports API Endpoint
 * Routes report requests to appropriate controller methods
 * Handles JSON requests and file exports
 */

require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../app/controllers/ReportController.php';

// Require authenticated user
requireLogin();

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

try {
    // Initialize database connection and controller
    $database = new Database();
    $db = $database->getConnection();
    $controller = new ReportController($db);

    // Get request method and action
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // ============================================
    // REPORT 1: Agent Performance
    // ============================================
    if ($action === 'getAgentPerformance') {
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $filters = [
            'team' => isset($_GET['team']) ? explode(',', $_GET['team']) : [],
            'dateFrom' => $_GET['dateFrom'] ?? '',
            'dateTo' => $_GET['dateTo'] ?? '',
            'status' => isset($_GET['status']) ? explode(',', $_GET['status']) : []
        ];

        $result = $controller->getAgentPerformance($filters);
        echo json_encode($result);
        exit;
    }

    // ============================================
    // REPORT 2: Projects Since April 1st
    // ============================================
    if ($action === 'getProjectsSinceApril') {
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $filters = [
            'fiscalYear' => $_GET['fiscalYear'] ?? 'current',  // 'current' or 'previous'
            'team' => isset($_GET['team']) ? explode(',', $_GET['team']) : []
        ];

        $result = $controller->getProjectsSinceApril($filters);
        echo json_encode($result);
        exit;
    }

    // ============================================
    // REPORT 3: Project Timeline
    // ============================================
    if ($action === 'getProjectTimeline') {
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $filters = [
            'dateFrom' => $_GET['dateFrom'] ?? '',
            'dateTo' => $_GET['dateTo'] ?? '',
            'team' => isset($_GET['team']) ? explode(',', $_GET['team']) : [],
            'status' => isset($_GET['status']) ? explode(',', $_GET['status']) : []
        ];

        $result = $controller->getProjectTimeline($filters);
        echo json_encode($result);
        exit;
    }

    // ============================================
    // Get Filter Options (for dropdowns)
    // ============================================
    if ($action === 'getFilterOptions') {
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $result = $controller->getFilterOptions();
        echo json_encode($result);
        exit;
    }

    // ============================================
    // Export Report to CSV
    // ============================================
    if ($action === 'exportCSV') {
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $reportType = $_GET['reportType'] ?? '';

        if (empty($reportType)) {
            http_response_code(400);
            echo json_encode(['error' => 'Report type required']);
            exit;
        }

        // Build filters from query parameters
        $filters = [
            'team' => isset($_GET['team']) ? explode(',', $_GET['team']) : [],
            'dateFrom' => $_GET['dateFrom'] ?? '',
            'dateTo' => $_GET['dateTo'] ?? '',
            'status' => isset($_GET['status']) ? explode(',', $_GET['status']) : [],
            'projectType' => isset($_GET['projectType']) ? explode(',', $_GET['projectType']) : [],
            'fiscalYear' => $_GET['fiscalYear'] ?? 'current'
        ];

        // Generate CSV export (exits after file download)
        $controller->exportToCSV($reportType, $filters);
        exit;
    }

    // ============================================
    // Invalid Action
    // ============================================
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid action',
        'available_actions' => [
            'getAgentPerformance',
            'getProjectsSinceApril',
            'getProjectTimeline',
            'getFilterOptions',
            'exportCSV'
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
