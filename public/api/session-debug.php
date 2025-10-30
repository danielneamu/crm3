<?php

/**
 * Session Debug Endpoint
 * Access via: https://yourapp.com/debug/session-info.php
 * Only use in development - restrict in production!
 */

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/html; charset=utf-8');

// Only allow localhost or specific IP in production
$allowed_ips = ['127.0.0.1', '::1']; // Add your dev IP
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied. Only localhost allowed.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get all session-related PHP settings
$php_settings = [
    'session.save_handler' => ini_get('session.save_handler'),
    'session.save_path' => ini_get('session.save_path'),
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
    'session.gc_probability' => ini_get('session.gc_probability'),
    'session.gc_divisor' => ini_get('session.gc_divisor'),
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_samesite' => ini_get('session.cookie_samesite'),
    'session.use_strict_mode' => ini_get('session.use_strict_mode'),
    'session.name' => ini_get('session.name'),
];

$session_info = [
    'Current Session ID' => session_id(),
    'Session Status' => session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE',
    'Session Name' => session_name(),
    'Session Data Size' => strlen(serialize($_SESSION)) . ' bytes',
    'Session Key Count' => count($_SESSION),
    'Server Time' => date('Y-m-d H:i:s', time()),
    'Session Created' => isset($_SESSION['session_created'])
        ? date('Y-m-d H:i:s', $_SESSION['session_created'])
        : 'Not set',
];

// Calculate expiry
if (isset($_SESSION['session_created'])) {
    $maxlifetime = (int)ini_get('session.gc_maxlifetime');
    $expiry_time = $_SESSION['session_created'] + $maxlifetime;
    $now = time();
    $time_remaining = $expiry_time - $now;

    $session_info['Session Expiry Time'] = date('Y-m-d H:i:s', $expiry_time);
    $session_info['Time Remaining (seconds)'] = $time_remaining;
    $session_info['Time Remaining (minutes)'] = round($time_remaining / 60, 2);
    $session_info['Session Expired?'] = $time_remaining <= 0 ? 'YES - EXPIRED' : 'NO - Active';
}

// Check actual session file
$save_path = ini_get('session.save_path');
$session_id = session_id();
$session_file = $save_path . '/sess_' . $session_id;

$file_info = [
    'Session File Path' => $session_file,
    'Session File Exists' => file_exists($session_file) ? 'YES' : 'NO',
    'Session File Size' => file_exists($session_file) ? filesize($session_file) . ' bytes' : 'N/A',
    'Session File Modified' => file_exists($session_file) ? date('Y-m-d H:i:s', filemtime($session_file)) : 'N/A',
    'Session File Readable' => is_readable($session_file) ? 'YES' : 'NO',
    'Session File Writable' => is_writable($session_file) ? 'YES' : 'NO',
];

// Check last regeneration
$last_regen = isset($_SESSION['last_regeneration'])
    ? date('Y-m-d H:i:s', $_SESSION['last_regeneration'])
    : 'Never regenerated';
$regen_interval = isset($_SESSION['last_regeneration'])
    ? time() - $_SESSION['last_regeneration']
    : 'N/A';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 2rem;
        }

        .debug-section {
            background: white;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .debug-section h3 {
            background: #007bff;
            color: white;
            padding: 1rem;
            margin: 0;
            border-radius: 8px 8px 0 0;
        }

        .debug-content {
            padding: 1.5rem;
        }

        .debug-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }

        .debug-row:last-child {
            border-bottom: none;
        }

        .debug-label {
            font-weight: 600;
            width: 35%;
            color: #495057;
        }

        .debug-value {
            width: 65%;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }

        .status-good {
            color: #28a745;
            font-weight: 600;
        }

        .status-bad {
            color: #dc3545;
            font-weight: 600;
        }

        .status-warning {
            color: #ffc107;
            font-weight: 600;
        }

        .section-title {
            color: #333;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mb-4">🔍 Session Debugging Information</h1>

        <!-- PHP Settings -->
        <div class="debug-section">
            <h3>PHP Session Configuration</h3>
            <div class="debug-content">
                <?php foreach ($php_settings as $key => $value): ?>
                    <div class="debug-row">
                        <div class="debug-label"><?= htmlspecialchars($key) ?></div>
                        <div class="debug-value">
                            <?php
                            // Highlight issues
                            if (in_array($key, ['session.cookie_httponly', 'session.cookie_secure', 'session.use_strict_mode'])) {
                                echo $value ? '<span class="status-good">✓ ' . $value . '</span>' : '<span class="status-warning">⚠ ' . $value . '</span>';
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Current Session Info -->
        <div class="debug-section">
            <h3>Current Session Status</h3>
            <div class="debug-content">
                <?php foreach ($session_info as $key => $value): ?>
                    <div class="debug-row">
                        <div class="debug-label"><?= htmlspecialchars($key) ?></div>
                        <div class="debug-value">
                            <?php
                            if (strpos($value, 'EXPIRED') !== false) {
                                echo '<span class="status-bad">' . htmlspecialchars($value) . '</span>';
                            } elseif (strpos($value, 'Not set') !== false) {
                                echo '<span class="status-warning">⚠ ' . htmlspecialchars($value) . '</span>';
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Session File Info -->
        <div class="debug-section">
            <h3>Session File Information</h3>
            <div class="debug-content">
                <?php foreach ($file_info as $key => $value): ?>
                    <div class="debug-row">
                        <div class="debug-label"><?= htmlspecialchars($key) ?></div>
                        <div class="debug-value">
                            <?php
                            if (strpos($value, 'NO') !== false) {
                                echo '<span class="status-bad">❌ ' . htmlspecialchars($value) . '</span>';
                            } elseif (strpos($value, 'YES') !== false) {
                                echo '<span class="status-good">✓ ' . htmlspecialchars($value) . '</span>';
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Session Regeneration -->
        <div class="debug-section">
            <h3>Session Regeneration Info</h3>
            <div class="debug-content">
                <div class="debug-row">
                    <div class="debug-label">Last Regeneration</div>
                    <div class="debug-value"><?= htmlspecialchars($last_regen) ?></div>
                </div>
                <div class="debug-row">
                    <div class="debug-label">Time Since Regen (seconds)</div>
                    <div class="debug-value"><?= is_numeric($regen_interval) ? $regen_interval : htmlspecialchars($regen_interval) ?></div>
                </div>
                <div class="debug-row">
                    <div class="debug-label">Regeneration Interval Target</div>
                    <div class="debug-value">300 seconds (5 minutes)</div>
                </div>
            </div>
        </div>

        <!-- Session Data -->
        <div class="debug-section">
            <h3>Session Data ($_SESSION)</h3>
            <div class="debug-content">
                <pre style="background: #f4f4f4; padding: 1rem; border-radius: 4px; max-height: 300px; overflow-y: auto;"><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="debug-section">
            <h3>🔧 Diagnostic Checklist</h3>
            <div class="debug-content">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <input type="checkbox" id="check1" <?= (int)ini_get('session.cookie_httponly') ? 'checked' : '' ?>>
                        <label for="check1">✓ <code>session.cookie_httponly</code> = 1 (required)</label>
                    </li>
                    <li class="mb-2">
                        <input type="checkbox" id="check2" <?= (int)ini_get('session.use_strict_mode') ? 'checked' : '' ?>>
                        <label for="check2">✓ <code>session.use_strict_mode</code> = 1 (required)</label>
                    </li>
                    <li class="mb-2">
                        <input type="checkbox" id="check3" <?= file_exists($session_file) ? 'checked' : '' ?>>
                        <label for="check3">✓ Session file exists at <code><?= htmlspecialchars($session_file) ?></code></label>
                    </li>
                    <li class="mb-2">
                        <input type="checkbox" id="check4" <?= is_writable($session_file) ? 'checked' : '' ?>>
                        <label for="check4">✓ Session file is writable</label>
                    </li>
                    <li class="mb-2">
                        <input type="checkbox" id="check5" <?= ((time() - $_SESSION['session_created']) < (int)ini_get('session.gc_maxlifetime')) ? 'checked' : '' ?>>
                        <label for="check5">✓ Session not expired (time remaining > 0)</label>
                    </li>
                </ul>
            </div>
        </div>

        <!-- How to Fix -->
        <div class="alert alert-info mt-4">
            <h4>📋 How to Debug Further:</h4>
            <ol>
                <li><strong>Check host PHP settings:</strong> Contact hosting provider and request <code>session.gc_maxlifetime</code>, <code>session.save_path</code>, and <code>session.save_handler</code></li>
                <li><strong>Monitor session file:</strong> SSH into server and run: <code>ls -la <?= htmlspecialchars($session_file) ?></code></li>
                <li><strong>Check for cron cleanup:</strong> Ask host if they run session cleanup jobs</li>
                <li><strong>Test timeout:</strong> Log in, wait 15 mins, try action. Does it redirect to login?</li>
                <li><strong>Check browser cookies:</strong> Open DevTools → Application → Cookies. Look for <code><?= htmlspecialchars(ini_get('session.name')) ?></code></li>
            </ol>
        </div>
    </div>
</body>

</html>