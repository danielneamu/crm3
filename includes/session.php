<?php

/**
 * Session Configuration with Error Handling
 */

// Define custom session directory
$custom_session_path = dirname(__DIR__) . '/logs/sessions';

// Create directory if it doesn't exist with correct permissions
if (!is_dir($custom_session_path)) {
    if (!mkdir($custom_session_path, 0770, true)) {
        error_log("Failed to create session directory: " . $custom_session_path);
        die("Session Error: Cannot create session directory. Check permissions.");
    }
}

// Verify directory is writable
if (!is_writable($custom_session_path)) {
    error_log("Session directory not writable: " . $custom_session_path);
    die("Session Error: Session directory not writable. Current permissions: " . substr(sprintf('%o', fileperms($custom_session_path)), -4));
}

// Set custom session save path BEFORE starting session
ini_set('session.save_path', $custom_session_path);

// Configure session settings BEFORE session_start()
if (session_status() === PHP_SESSION_NONE) {
    // Session lifetime: 8 hours
    ini_set('session.gc_maxlifetime', 7200);
    ini_set('session.cookie_lifetime', 7200);

    // Security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);

    // Custom session name
    session_name('CRM_SESSION');

    // Start session
    if (!session_start()) {
        error_log("session_start() failed. Check PHP error log.");
        die("Session Error: Could not start session.");
    }

    // Regenerate session ID every 30 minutes for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
