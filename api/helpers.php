<?php

declare(strict_types=1);

function loadConfig(): void
{
    $configPath = __DIR__ . '/config.php';

    if (!file_exists($configPath)) {
        jsonError('Server configuration missing. Copy api/config.example.php to api/config.php.', 503);
    }

    require $configPath;

    if (defined('TIMEZONE')) {
        date_default_timezone_set(TIMEZONE);
    }
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $status = 400): void
{
    jsonResponse(['success' => false, 'error' => $message], $status);
}

function jsonSuccess(array $data = []): void
{
    jsonResponse(array_merge(['success' => true], $data));
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        return $_POST ?: [];
    }

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        jsonError('Invalid JSON request body.');
    }

    return $decoded;
}

function sanitizeString(?string $value, int $maxLength): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;

    if ($value === '') {
        return '';
    }

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength);
    }

    return substr($value, 0, $maxLength);
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateDate(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);

    return $parsed instanceof DateTime && $parsed->format('Y-m-d') === $date;
}

function validateTime(string $time): bool
{
    return preg_match('/^\d{2}:\d{2}$/', $time) === 1;
}

function formatTimeLabel(string $time): string
{
    $parsed = DateTime::createFromFormat('H:i', $time);

    return $parsed instanceof DateTime ? $parsed->format('g:i A') : $time;
}

function serviceLabel(string $service): string
{
    $labels = [
        'hybrid-ev' => 'Hybrid & EV',
        'diagnostics' => 'Diagnostics',
        'tires' => 'Tires',
        'brakes' => 'Brakes',
        'oil-change' => 'Oil & Fluids',
        'inspection' => 'Inspection',
        'other' => 'Other',
    ];

    return $labels[$service] ?? $service;
}

function hybridServiceLabel(string $value): string
{
    $labels = [
        'battery-diagnostics' => 'Battery Diagnostics',
        'battery-replacement' => 'Battery Replacement',
        'high-voltage' => 'High-Voltage System Repair',
        'ev-maintenance' => 'Hybrid/EV Maintenance',
        'charging-system' => 'Charging System Service',
        'electrical' => 'Electrical Troubleshooting',
        'hybrid-other' => 'Other Hybrid/EV Service',
    ];

    return $labels[$value] ?? $value;
}

function buildAppointmentEmailContent(array $appointment): array
{
    $business = defined('BUSINESS_NAME') ? BUSINESS_NAME : 'Green Torque';
    $serviceText = serviceLabel($appointment['service']);

    if (!empty($appointment['hybrid_service'])) {
        $serviceText .= ' (' . hybridServiceLabel($appointment['hybrid_service']) . ')';
    }

    $subject = 'New Appointment Request - ' . $business;
    $bodyLines = [
        'A new appointment request was submitted on the website.',
        '',
        'Customer: ' . $appointment['customer_name'],
        'Email: ' . $appointment['email'],
        'Phone: ' . $appointment['phone'],
        '',
        'Vehicle: ' . $appointment['vehicle_year'] . ' ' . $appointment['vehicle_make'] . ' ' . $appointment['vehicle_model'],
        'Service: ' . $serviceText,
        'Preferred date: ' . $appointment['preferred_date'],
        'Preferred time: ' . formatTimeLabel($appointment['preferred_time']),
        '',
        'Notes:',
        $appointment['notes'] !== '' ? $appointment['notes'] : '(none)',
        '',
        'Appointment ID: #' . $appointment['id'],
    ];

    return [
        'subject' => $subject,
        'body' => implode("\n", $bodyLines),
    ];
}

function sendAppointmentEmailPhpMail(array $appointment, string $subject, string $body): bool
{
    if (!defined('NOTIFY_EMAIL') || NOTIFY_EMAIL === '') {
        return false;
    }

    $from = defined('EMAIL_FROM') && EMAIL_FROM !== '' ? EMAIL_FROM : NOTIFY_EMAIL;
    $business = defined('BUSINESS_NAME') ? BUSINESS_NAME : 'Green Torque';

    $headers = implode("\r\n", [
        'From: ' . $business . ' <' . $from . '>',
        'Reply-To: ' . $appointment['email'],
        'Content-Type: text/plain; charset=UTF-8',
    ]);

    return @mail(NOTIFY_EMAIL, $subject, $body, $headers);
}

function sendAppointmentEmailSmtp(array $appointment, string $subject, string $body): bool
{
    if (!defined('NOTIFY_EMAIL') || NOTIFY_EMAIL === '') {
        return false;
    }

    require_once __DIR__ . '/smtp-client.php';

    $from = defined('SMTP_USER') && SMTP_USER !== '' ? SMTP_USER : NOTIFY_EMAIL;
    $fromName = defined('BUSINESS_NAME') ? BUSINESS_NAME : 'Green Torque';

    return sendSmtpEmail(
        NOTIFY_EMAIL,
        $subject,
        $body,
        $from,
        $fromName,
        $appointment['email']
    );
}

function sendAppointmentEmailFormSubmit(array $appointment): bool
{
    if (!defined('NOTIFY_EMAIL') || NOTIFY_EMAIL === '') {
        return false;
    }

    if (defined('USE_FORMSUBMIT_NOTIFY') && USE_FORMSUBMIT_NOTIFY === false) {
        return false;
    }

    if (!function_exists('curl_init')) {
        return false;
    }

    $serviceText = serviceLabel($appointment['service']);

    if (!empty($appointment['hybrid_service'])) {
        $serviceText .= ' (' . hybridServiceLabel($appointment['hybrid_service']) . ')';
    }

    $payload = [
        '_subject' => 'New Appointment Request - Green Torque',
        '_template' => 'table',
        '_captcha' => 'false',
        'Customer Name' => $appointment['customer_name'],
        'Customer Email' => $appointment['email'],
        'Phone' => $appointment['phone'],
        'Vehicle' => $appointment['vehicle_year'] . ' ' . $appointment['vehicle_make'] . ' ' . $appointment['vehicle_model'],
        'Service' => $serviceText,
        'Preferred Date' => $appointment['preferred_date'],
        'Preferred Time' => formatTimeLabel($appointment['preferred_time']),
        'Notes' => $appointment['notes'] !== '' ? $appointment['notes'] : '(none)',
        'Appointment ID' => '#' . $appointment['id'],
    ];

    $url = 'https://formsubmit.co/ajax/' . rawurlencode(NOTIFY_EMAIL);
    $handle = curl_init($url);

    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    return $status >= 200 && $status < 300 && $response !== false;
}

function sendAppointmentSms(array $appointment): bool
{
    if (!defined('NOTIFY_PHONE') || NOTIFY_PHONE === '') {
        return false;
    }

    if (
        !defined('TWILIO_ACCOUNT_SID') ||
        !defined('TWILIO_AUTH_TOKEN') ||
        !defined('TWILIO_FROM_NUMBER') ||
        TWILIO_ACCOUNT_SID === '' ||
        TWILIO_AUTH_TOKEN === '' ||
        TWILIO_FROM_NUMBER === ''
    ) {
        return false;
    }

    if (!function_exists('curl_init')) {
        return false;
    }

    $serviceText = serviceLabel($appointment['service']);
    $message = 'Green Torque booking #' . $appointment['id'] . ': ' .
        $appointment['customer_name'] . ', ' .
        $appointment['vehicle_year'] . ' ' . $appointment['vehicle_make'] . ' ' . $appointment['vehicle_model'] .
        ', ' . $serviceText . ', ' .
        $appointment['preferred_date'] . ' ' . formatTimeLabel($appointment['preferred_time']) .
        '. Call ' . $appointment['phone'];

    if (strlen($message) > 320) {
        $message = substr($message, 0, 317) . '...';
    }

    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode(TWILIO_ACCOUNT_SID) . '/Messages.json';
    $payload = http_build_query([
        'To' => NOTIFY_PHONE,
        'From' => TWILIO_FROM_NUMBER,
        'Body' => $message,
    ]);

    $handle = curl_init($url);

    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_USERPWD => TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN,
    ]);

    $response = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    return $status >= 200 && $status < 300 && $response !== false;
}

function notifyBusinessOwner(array $appointment): array
{
    $email = buildAppointmentEmailContent($appointment);
    $subject = $email['subject'];
    $body = $email['body'];
    $emailSent = false;

    if (defined('SMTP_USER') && SMTP_USER !== '' && defined('SMTP_PASS') && SMTP_PASS !== '') {
        $emailSent = sendAppointmentEmailSmtp($appointment, $subject, $body);
    }

    if (!$emailSent && (defined('USE_FORMSUBMIT_NOTIFY') ? USE_FORMSUBMIT_NOTIFY : true)) {
        $emailSent = sendAppointmentEmailFormSubmit($appointment);
    }

    if (!$emailSent) {
        $emailSent = sendAppointmentEmailPhpMail($appointment, $subject, $body);
    }

    $smsSent = sendAppointmentSms($appointment);

    return [
        'emailSent' => $emailSent,
        'smsSent' => $smsSent,
        'ownerNotified' => $emailSent || $smsSent,
    ];
}

function validateAppointmentInput(array $input): array
{
    $name = sanitizeString($input['name'] ?? '', 120);
    $email = sanitizeString($input['email'] ?? '', 180);
    $phone = sanitizeString($input['phone'] ?? '', 40);
    $year = (int) ($input['year'] ?? 0);
    $make = sanitizeString($input['make'] ?? '', 60);
    $model = sanitizeString($input['model'] ?? '', 80);
    $service = sanitizeString($input['service'] ?? '', 60);
    $hybridService = sanitizeString($input['hybrid_service'] ?? '', 80);
    $date = sanitizeString($input['date'] ?? '', 10);
    $time = sanitizeString($input['time'] ?? '', 5);
    $notes = sanitizeString($input['message'] ?? '', 2000);

    if ($name === '') {
        jsonError('Full name is required.');
    }

    if (!validateEmail($email)) {
        jsonError('A valid email address is required.');
    }

    if ($phone === '') {
        jsonError('Phone number is required.');
    }

    $currentYear = (int) date('Y');

    if ($year < 1980 || $year > $currentYear + 1) {
        jsonError('Please select a valid vehicle year.');
    }

    if ($make === '' || $model === '') {
        jsonError('Vehicle make and model are required.');
    }

    $allowedServices = ['hybrid-ev', 'diagnostics', 'tires', 'brakes', 'oil-change', 'inspection', 'other'];

    if (!in_array($service, $allowedServices, true)) {
        jsonError('Please select a valid service.');
    }

    if ($service === 'hybrid-ev' && $hybridService === '') {
        jsonError('Please select a hybrid/EV service type.');
    }

    if ($service !== 'hybrid-ev') {
        $hybridService = '';
    }

    if (!validateDate($date)) {
        jsonError('Please select a valid appointment date.');
    }

    $tomorrow = new DateTime('tomorrow');

    if (DateTime::createFromFormat('Y-m-d', $date) < $tomorrow) {
        jsonError('Appointment date must be tomorrow or later.');
    }

    if (!validateTime($time)) {
        jsonError('Please select a valid appointment time.');
    }

    return [
        'customer_name' => $name,
        'email' => $email,
        'phone' => $phone,
        'vehicle_year' => $year,
        'vehicle_make' => $make,
        'vehicle_model' => $model,
        'service' => $service,
        'hybrid_service' => $hybridService !== '' ? $hybridService : null,
        'preferred_date' => $date,
        'preferred_time' => $time . ':00',
        'notes' => $notes,
    ];
}
