<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(static function (): void {
    $error = error_get_last();

    if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    if (!headers_sent()) {
        http_response_code(500);
    }

    echo json_encode([
        'success' => false,
        'error' => 'Server error while running email test.',
        'hint' => 'Often a typo in api/config.php (missing ; or quote). Open config.php and check the lines near EMAIL_TEST_KEY and SMTP_PASS.',
        'detail' => $error['message'] . ' in ' . basename($error['file']) . ' line ' . $error['line'],
    ], JSON_PRETTY_PRINT);
});

require __DIR__ . '/helpers.php';

loadConfig();

$key = (string) ($_GET['key'] ?? '');
$expectedKey = defined('EMAIL_TEST_KEY') ? (string) EMAIL_TEST_KEY : '';

if ($expectedKey === '' || !hash_equals($expectedKey, $key)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Access denied. Set EMAIL_TEST_KEY in api/config.php, then open this URL with ?key=YOUR_KEY',
    ], JSON_PRETTY_PRINT);
    exit;
}

$step = (string) ($_GET['step'] ?? 'send');

if ($step === 'ping') {
    echo json_encode([
        'success' => true,
        'step' => 'ping',
        'message' => 'config.php loaded OK. Add &step=send to the URL to send a test email.',
        'notifyEmail' => defined('NOTIFY_EMAIL') ? NOTIFY_EMAIL : null,
        'smtpConfigured' => defined('SMTP_USER') && SMTP_USER !== '' && defined('SMTP_PASS') && SMTP_PASS !== '',
        'phpVersion' => PHP_VERSION,
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
    'preferred_time' => '10:00:00',
    'notes' => 'This is a test message from api/email-test.php',
];

try {
    $result = notifyBusinessOwner($appointment);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage(),
        'hint' => 'Check api/config.php and that helpers.php + smtp-client.php are uploaded.',
    ], JSON_PRETTY_PRINT);
    exit;
}

echo json_encode([
    'success' => true,
    'notifyEmail' => defined('NOTIFY_EMAIL') ? NOTIFY_EMAIL : null,
    'emailSent' => $result['emailSent'],
    'smsSent' => $result['smsSent'],
    'message' => $result['emailSent']
        ? 'Test email was sent. Check NOTIFY_EMAIL inbox and spam.'
        : 'Email was not sent. Check SMTP password, FormSubmit activation, and Hostinger email mailbox.',
], JSON_PRETTY_PRINT);
