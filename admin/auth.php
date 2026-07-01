<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../api/helpers.php';

loadConfig();

function adminIsLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function requireAdminLogin(): void
{
    if (!adminIsLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function adminLogout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
