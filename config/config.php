<?php

/**
 * Main Configuration Loader
 * Auto-detects environment and loads appropriate config
 */

$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detect environment
if (
    strpos($hostname, 'localhost') !== false ||
    strpos($hostname, '.local') !== false ||
    strpos($hostname, '127.0.0.1') !== false
) {
    require_once __DIR__ . '/config.dev.php';
} else {
    require_once __DIR__ . '/config.prod.php';
}

// Apply PHP settings based on environment
ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
error_reporting(DEBUG_MODE ? E_ALL : E_ERROR | E_WARNING);
ini_set('log_errors', '1');
ini_set('error_log', LOG_PATH . 'php_errors.log');
