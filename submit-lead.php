<?php
header('Content-Type: application/json');
require __DIR__ . '/_leads_helper.php';

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $input = $_POST;
}

$sanitise = function ($v) {
    return trim(htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'));
};

$data = [
    'name'                 => $sanitise($input['name']                 ?? ''),
    'phone'                => $sanitise($input['phone']                ?? ''),
    'email'                => $sanitise($input['email']                ?? ''),
    'trade'                => $sanitise($input['trade']                ?? ''),
    'suburb'               => $sanitise($input['suburb']               ?? ''),
    'message'              => $sanitise($input['message']              ?? ''),
    'source'               => $sanitise($input['source']               ?? 'form'),
    'has_website'          => $sanitise($input['has_website']          ?? ''),
    'goal'                 => $sanitise($input['goal']                 ?? ''),
    'callback_slot'        => $sanitise($input['callback_slot']        ?? ''),
    'conversation_snippet' => $sanitise($input['conversation_snippet'] ?? ''),
];

$result = tradie_write_and_email_lead($data);

if (!$result['success']) {
    http_response_code($result['error'] === 'Name and phone number are required.' ? 400 : 500);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => true]);
