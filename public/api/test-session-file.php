<?php
// Create at: public/test-session-file.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$save_path = ini_get('session.save_path');
$session_file = $save_path . '/sess_' . session_id();

echo "Session Save Path: " . $save_path . "\n";
echo "Session File: " . $session_file . "\n";
echo "File Exists: " . (file_exists($session_file) ? "YES" : "NO") . "\n";
echo "File Readable: " . (is_readable($session_file) ? "YES" : "NO") . "\n";
echo "File Size: " . (file_exists($session_file) ? filesize($session_file) : 0) . " bytes\n";
echo "File Modified: " . (file_exists($session_file) ? date('Y-m-d H:i:s', filemtime($session_file)) : 'N/A') . "\n";
