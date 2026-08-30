<?php
/**
 * KMA — admin/logout.php  |  PHP 7.2
 * Destroys the admin session and returns to login.
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

/* Clear all session data, then destroy + wipe the cookie */
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
