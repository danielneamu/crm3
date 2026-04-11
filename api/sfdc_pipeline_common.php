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
    error_log('SFDC Pipeline API Exception: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
});

set_error_handler(function ($severity, $message, $file, $line) {
    error_log("SFDC Pipeline API Error: {$message} in {$file} on line {$line}");

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
    error_log('SFDC Pipeline API DB Connection Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit;
}
