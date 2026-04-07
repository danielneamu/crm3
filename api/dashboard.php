<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../app/controllers/DashboardController.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $controller = new DashboardController($db);

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'stats':
            $stats = $controller->getStatistics();
            echo json_encode($stats);
            break;

        case 'projectsByMonth':
            $months = $_GET['months'] ?? 12;
            $data = $controller->getProjectsByMonth($months);
            echo json_encode($data);
            break;

        case 'projectsByType':
            $data = $controller->getProjectsByType();
            echo json_encode($data);
            break;

        case 'projectsByAgent':
            $limit = $_GET['limit'] ?? 10;
            $data = $controller->getProjectsByAgent($limit);
            echo json_encode($data);
            break;

        case 'projectsByTeam':
            $data = $controller->getProjectsByTeam();
            echo json_encode($data);
            break;

        case 'recentProjects':
            $limit = $_GET['limit'] ?? 10;
            $data = $controller->getRecentProjects($limit);
            echo json_encode($data);
            break;

        case 'recentActivity':
            $limit = $_GET['limit'] ?? 10;
            $data = $controller->getRecentActivity($limit);
            echo json_encode($data);
            break;


        case 'monthlyNewProjects':
            $data = $controller->getMonthlyNewProjects();
            echo json_encode($data);
            break;

        case 'monthlyCompletedProjects':
            $data = $controller->getMonthlyCompletedProjects();
            echo json_encode($data);
            break;

        case 'monthlySignedProjects':
            $data = $controller->getMonthlySignedProjects();
            echo json_encode($data);
            break;


        case 'fiscalYearComparison':
            $data = $controller->getFiscalYearComparison();
            echo json_encode($data);
            break;

        case 'projectsByTeamFiscalYear':
            $data = $controller->getProjectsByTeamFiscalYear();
            echo json_encode($data);
            break;

        case 'agentStatusSummary':
            // ?action=agentStatusSummary&period=fiscal OR &period=all
            $period = $_GET['period'] ?? 'fiscal';
            echo json_encode($controller->getAgentStatusSummary($period));
            break;

        case 'inProgressByAgent':
            echo json_encode($controller->getInProgressByAgent());
            break;

        case 'all':
            echo json_encode([
                'stats' => $controller->getStatistics(),
                'monthlyNewProjects' => $controller->getMonthlyNewProjects(),      // NEW
                'monthlyCompletedProjects' => $controller->getMonthlyCompletedProjects(), // NEW
                'monthlySignedProjects' => $controller->getMonthlySignedProjects(), // NEW
                'fiscalYearComparison' => $controller->getFiscalYearComparison(), // USE NEW
                // 'projectsByMonth' => $controller->getProjectsByMonth(12),  // REMOVE or keep for other uses
                'projectsByType' => $controller->getProjectsByType(),
                'projectsByAgent' => $controller->getProjectsByAgent(10),
                'projectsByTeamFiscalYear' => $controller->getProjectsByTeamFiscalYear(),

                'agentStatusFiscal' => $controller->getAgentStatusSummary('fiscal'),
                'agentStatusAll' => $controller->getAgentStatusSummary('all'),

                'inProgressByAgent' => $controller->getInProgressByAgent(),

                'projectsByTeam' => $controller->getProjectsByTeam(),
                'recentProjects' => $controller->getRecentProjects(10)
            ]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
