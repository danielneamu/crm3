<?php
// test-session-file.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Write something to the session to create the file
$_SESSION['test_data'] = 'Session is working!';
$_SESSION['timestamp'] = time();

$save_path = ini_get('session.save_path');
$session_file = $save_path . '/sess_' . session_id();

echo "<h3>Session Diagnostics</h3>";
echo "<strong>Session ID:</strong> " . session_id() . "<br>";
echo "<strong>Session Save Path:</strong> " . $save_path . "<br>";
echo "<strong>Session File:</strong> " . $session_file . "<br>";
echo "<strong>File Exists:</strong> " . (file_exists($session_file) ? "✅ YES" : "❌ NO") . "<br>";
echo "<strong>File Readable:</strong> " . (is_readable($session_file) ? "✅ YES" : "❌ NO") . "<br>";
echo "<strong>File Size:</strong> " . (file_exists($session_file) ? filesize($session_file) : 0) . " bytes<br>";
echo "<strong>File Modified:</strong> " . (file_exists($session_file) ? date('Y-m-d H:i:s', filemtime($session_file)) : 'N/A') . "<br>";

echo "<hr>";
echo "<h4>Session Data:</h4>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";
echo "<h4>Raw Session File Content:</h4>";
if (file_exists($session_file)) {
    echo "<pre>" . file_get_contents($session_file) . "</pre>";
} else {
    echo "File not found";
}
