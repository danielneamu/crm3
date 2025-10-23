<?php
require_once '../config/config.php';  
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();  // ← Changed from getInstance()
    $conn = $db->getConnection();

    // Get agents by team
    if (isset($_GET['team'])) {
        $team = $_GET['team'];
        $stmt = $conn->prepare("
            SELECT id_agent, nume_agent 
            FROM agents 
            WHERE current_team = ? AND status_agent = '1' 
            ORDER BY nume_agent
        ");
        $stmt->execute([$team]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // Get all dropdown data
    $data = [
        'companies' => $conn->query("
            SELECT id_companies, name_companies 
            FROM companies 
            ORDER BY name_companies
        ")->fetchAll(PDO::FETCH_ASSOC),

        'teams' => $conn->query("
            SELECT DISTINCT current_team 
            FROM agents 
            WHERE status_agent = '1' 
            ORDER BY current_team
        ")->fetchAll(PDO::FETCH_COLUMN),

        'types' => ['ICT/IOT', 'Fixed', 'Mobile', 'Other'],

        'partners' => $conn->query("
            SELECT id_parteneri, name_parteneri 
            FROM parteneri 
            ORDER BY name_parteneri
        ")->fetchAll(PDO::FETCH_ASSOC)
    ];

    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
