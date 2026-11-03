<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/helpers.php';

loadConfig();

$key = $_GET['key'] ?? '';

if (!defined('EMAIL_TEST_KEY') || EMAIL_TEST_KEY === '' || !hash_equals(EMAIL_TEST_KEY, $key)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Access denied. Set EMAIL_TEST_KEY in api/config.php, then open this URL with ?key=YOUR_KEY',
    ], JSON_PRETTY_PRINT);
    exit;
}

$appointment = [
    'id' => 0,
    'customer_name' => 'Email Test',
    'email' => 'test@example.com',
    'phone' => '(916) 000-0000',
    'vehicle_year' => 2024,
    'vehicle_make' => 'Toyota',
    'vehicle_model' => 'Prius',
    'service' => 'diagnostics',
    'hybrid_service' => null,
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00',
    'notes' => 'This is a test message from api/email-test.php',
];

$result = notifyBusinessOwner($appointment);

echo json_encode([
    'success' => true,
    'notifyEmail' => defined('NOTIFY_EMAIL') ? NOTIFY_EMAIL : null,
    'emailSent' => $result['emailSent'],
    'smsSent' => $result['smsSent'],
    'message' => $result['emailSent']
        ? 'Test email was sent. Check NOTIFY_EMAIL inbox and spam.'
        : 'Email was not sent. Check SMTP password, FormSubmit activation, and Hostinger email mailbox.',
], JSON_PRETTY_PRINT);
