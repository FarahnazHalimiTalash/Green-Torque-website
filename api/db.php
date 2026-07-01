<?php

declare(strict_types=1);

require __DIR__ . '/helpers.php';

loadConfig();

function createPdo(): PDO
{
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new RuntimeException('Database settings are missing in api/config.php.');
    }

    $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . $charset;

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $pdo = createPdo();
    } catch (Throwable $exception) {
        jsonError('Unable to connect to the database. Check api/config.php settings.', 503);
    }

    return $pdo;
}
