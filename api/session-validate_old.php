<?php

/**
 * Session Validation Endpoint
 * Called periodically by JavaScript to verify session is still active
 * 
 * Returns:
 * - 200 OK + JSON if session valid
 * - 401 Unauthorized if session expired
 */

require_once '../config/config.php';
require_once '../includes/session.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'valid' => false,
        'error' => 'Session expired or not authenticated'
    ]);
    exit;
}

// Session is valid - return current time remaining
$time_remaining_absolute = $_SESSION['_time_remaining'] ?? 0;
$time_remaining_idle = $_SESSION['_idle_remaining'] ?? 0;
$time_remaining = min($time_remaining_absolute, $time_remaining_idle);

http_response_code(200);
echo json_encode([
    'valid' => true,
    'user_id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? 'Unknown',
    'time_remaining' => max(0, $time_remaining),
    'session_id' => session_id(),
    'server_time' => time()
]);
