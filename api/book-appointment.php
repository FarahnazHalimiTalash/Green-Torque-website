<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed.', 405);
}

$input = readJsonBody();
$data = validateAppointmentInput($input);

$sql = 'INSERT INTO appointments (
    customer_name,
    email,
    phone,
    vehicle_year,
    vehicle_make,
    vehicle_model,
    service,
    hybrid_service,
    preferred_date,
    preferred_time,
    notes
) VALUES (
    :customer_name,
    :email,
    :phone,
    :vehicle_year,
    :vehicle_make,
    :vehicle_model,
    :service,
    :hybrid_service,
    :preferred_date,
    :preferred_time,
    :notes
)';

try {
    $statement = db()->prepare($sql);
    $statement->execute($data);
    $appointmentId = (int) db()->lastInsertId();
} catch (PDOException $exception) {
    jsonError('Unable to save your appointment. Please call (916) 896-9086.', 500);
}

$data['id'] = $appointmentId;
$data['preferred_time'] = substr($data['preferred_time'], 0, 5);
$emailSent = sendAppointmentEmail($data);

jsonSuccess([
    'message' => 'Thank you! Your appointment request was received. Our team will confirm your booking shortly.',
    'appointmentId' => $appointmentId,
    'emailSent' => $emailSent,
]);
