<?php
// OLD CODD
//if (session_status() === PHP_SESSION_NONE) {
//    session_start();
//}

if (session_status() === PHP_SESSION_NONE) {
    // Session lifetime: 8 hours
    ini_set('session.gc_maxlifetime', 14400);
    ini_set('session.cookie_lifetime', 14400);

    // Security settings
    ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access to session cookie
    ini_set('session.cookie_secure', 1);    // Only send cookie over HTTPS (disable if not using HTTPS)
    ini_set('session.use_strict_mode', 1);  // Reject uninitialized session IDs

    session_name('CRM_SESSION');
    session_start();

    // Regenerate session ID every 30 minutes
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}