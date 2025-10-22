<?php
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function login($userId, $username, $fullName)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['full_name'] = $fullName;
}

function logout()
{
    session_destroy();
    header('Location: index.php');
    exit;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}
