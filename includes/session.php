<?php

/**
 * Session Management
 */

// Session configuration
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => FORCE_HTTPS,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Regenerate session ID on first visit
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
    $_SESSION['created'] = time();
}

// Session timeout check
if (isset($_SESSION['last_activity'])) {
    $elapsed = time() - $_SESSION['last_activity'];
    if ($elapsed > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: /crm3/public/index.php?timeout=1');
        exit;
    }
}

$_SESSION['last_activity'] = time();
