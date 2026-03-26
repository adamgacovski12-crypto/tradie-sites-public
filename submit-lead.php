<?php
header('Content-Type: application/json');

// Accept both JSON body and form data
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $input = $_POST;
}

// Sanitise all input
$name    = trim(htmlspecialchars($input['name'] ?? '', ENT_QUOTES, 'UTF-8'));
$phone   = trim(htmlspecialchars($input['phone'] ?? '', ENT_QUOTES, 'UTF-8'));
$email   = trim(htmlspecialchars($input['email'] ?? '', ENT_QUOTES, 'UTF-8'));
$trade   = trim(htmlspecialchars($input['trade'] ?? '', ENT_QUOTES, 'UTF-8'));
$suburb  = trim(htmlspecialchars($input['suburb'] ?? '', ENT_QUOTES, 'UTF-8'));
$message = trim(htmlspecialchars($input['message'] ?? '', ENT_QUOTES, 'UTF-8'));
$source  = trim(htmlspecialchars($input['source'] ?? 'form', ENT_QUOTES, 'UTF-8'));

// Validate required fields
if ($name === '' || $phone === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name and phone number are required.']);
    exit;
}

$date = date('Y-m-d H:i:s');
$csvFile = __DIR__ . '/leads.csv';

// Create CSV with header if it doesn't exist
$isNew = !file_exists($csvFile);
$fp = fopen($csvFile, 'a');
if ($fp === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unable to save lead. Please call us directly.']);
    exit;
}

// Lock file for concurrent writes
flock($fp, LOCK_EX);

if ($isNew) {
    fputcsv($fp, ['Date', 'Name', 'Phone', 'Email', 'Trade', 'Suburb', 'Message', 'Source']);
}

fputcsv($fp, [$date, $name, $phone, $email, $trade, $suburb, $message, $source]);

flock($fp, LOCK_UN);
fclose($fp);

// Send email notification
$adminEmail = getenv('ADMIN_EMAIL') ?: 'info@tradiebud.tech';
$subject = "New Lead — {$name} — " . ($trade ?: 'No trade specified');

$body = "New lead received from Tradie Sites Co.\n\n";
$body .= "Date:    {$date}\n";
$body .= "Name:    {$name}\n";
$body .= "Phone:   {$phone}\n";
$body .= "Email:   {$email}\n";
$body .= "Trade:   {$trade}\n";
$body .= "Suburb:  {$suburb}\n";
$body .= "Source:  {$source}\n";
$body .= "Message: {$message}\n";

$headers = "From: Tradie Sites Co. <noreply@tradiebud.tech>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

@mail($adminEmail, $subject, $body, $headers);

echo json_encode(['success' => true]);
