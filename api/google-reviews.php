<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$configPath = __DIR__ . '/config.php';

if (!file_exists($configPath)) {
    http_response_code(503);
    echo json_encode([
        'error' => 'Missing api/config.php. Copy config.example.php to config.php and add your Google API key.'
    ]);
    exit;
}

require $configPath;

if (!defined('GOOGLE_PLACES_API_KEY') || GOOGLE_PLACES_API_KEY === 'YOUR_GOOGLE_API_KEY_HERE' || GOOGLE_PLACES_API_KEY === '') {
    http_response_code(503);
    echo json_encode(['error' => 'Google Places API key is not configured in api/config.php.']);
    exit;
}

$query = defined('PLACE_TEXT_QUERY') ? PLACE_TEXT_QUERY : 'Green Torque Hybrid/EV Repair and Services 2031 Fulton Avenue Sacramento CA 95825';

$payload = json_encode([
    'textQuery' => $query,
    'maxResultCount' => 1
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => implode("\r\n", [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . GOOGLE_PLACES_API_KEY,
            'X-Goog-FieldMask: places.displayName,places.rating,places.userRatingCount,places.googleMapsUri,places.reviews'
        ]),
        'content' => $payload,
        'timeout' => 15
    ]
]);

$response = @file_get_contents('https://places.googleapis.com/v1/places:searchText', false, $context);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Unable to reach Google Places API.']);
    exit;
}

$data = json_decode($response, true);
$place = $data['places'][0] ?? null;

if (!$place) {
    http_response_code(404);
    echo json_encode(['error' => 'Green Torque Google listing was not found.']);
    exit;
}

$reviews = [];

foreach ($place['reviews'] ?? [] as $review) {
    $reviews[] = [
        'rating' => $review['rating'] ?? 5,
        'text' => $review['text']['text'] ?? '',
        'author' => $review['authorAttribution']['displayName'] ?? 'Google user',
        'time' => $review['relativePublishTimeDescription'] ?? ''
    ];
}

echo json_encode([
    'rating' => $place['rating'] ?? null,
    'reviewCount' => $place['userRatingCount'] ?? count($reviews),
    'googleMapsUri' => $place['googleMapsUri'] ?? null,
    'reviews' => $reviews
]);
