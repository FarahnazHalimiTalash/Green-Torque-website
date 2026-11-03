<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$checks = [
    'configFile' => false,
    'databaseConnection' => false,
    'appointmentsTable' => false,
];

$messages = [];

$configPath = __DIR__ . '/config.php';

if (!file_exists($configPath)) {
    $messages[] = 'Missing api/config.php. Copy api/config.example.php to api/config.php on the server.';
    echo json_encode(['ok' => false, 'checks' => $checks, 'messages' => $messages], JSON_PRETTY_PRINT);
    exit;
}

$checks['configFile'] = true;
require $configPath;

try {
    require __DIR__ . '/db.php';
    $pdo = createPdo();
    $checks['databaseConnection'] = true;
} catch (Throwable $exception) {
    $messages[] = 'Database connection failed. In hPanel → Databases, copy the exact database name, username, password, and MySQL host into api/config.php.';
    $messages[] = 'On Hostinger, DB_HOST is usually localhost, but use the hostname shown in hPanel if different.';
    echo json_encode(['ok' => false, 'checks' => $checks, 'messages' => $messages], JSON_PRETTY_PRINT);
    exit;
}

try {
    $pdo->query('SELECT 1 FROM appointments LIMIT 1');
    $checks['appointmentsTable'] = true;
} catch (Throwable $exception) {
    $messages[] = 'Connected to MySQL, but table appointments is missing. Import database/schema.sql in phpMyAdmin.';
    echo json_encode(['ok' => false, 'checks' => $checks, 'messages' => $messages], JSON_PRETTY_PRINT);
    exit;
}

$messages[] = 'Booking backend is configured correctly.';

echo json_encode(['ok' => true, 'checks' => $checks, 'messages' => $messages], JSON_PRETTY_PRINT);
