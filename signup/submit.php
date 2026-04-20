<?php
require __DIR__ . '/_helpers.php';
$cfg = tsc_cfg();
tsc_ensure_dirs();
tsc_ensure_signups_htaccess();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: /signup/');
    exit;
}

/* ── CSRF + rate limit ── */
if (!tsc_csrf_ok($_POST['csrf'] ?? null)) {
    http_response_code(400);
    echo 'Your session expired — go back and try again.';
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!tsc_rate_limit_check($ip)) {
    http_response_code(429);
    echo 'Too many signups from this network — try again in an hour or email info@tradiebud.tech.';
    exit;
}

$clean = fn($v, $max = 500) => substr(trim((string)$v), 0, $max);

$plan          = $clean($_POST['plan'] ?? '', 20);
$businessName  = $clean($_POST['business_name'] ?? '', 120);
$contactName   = $clean($_POST['contact_name'] ?? '', 80);
$phone         = $clean($_POST['phone'] ?? '', 25);
$email         = $clean($_POST['email'] ?? '', 120);
$abn           = $clean($_POST['abn'] ?? '', 14);
$trade         = $clean($_POST['trade'] ?? '', 40);
$suburbs       = $clean($_POST['suburbs'] ?? '', 500);
$licence       = $clean($_POST['licence'] ?? '', 40);
$tagline       = $clean($_POST['tagline'] ?? '', 140);
$service1      = $clean($_POST['service1'] ?? '', 80);
$service2      = $clean($_POST['service2'] ?? '', 80);
$service3      = $clean($_POST['service3'] ?? '', 80);
$years         = $clean($_POST['years'] ?? '', 4);
$existingWeb   = $clean($_POST['existing_website'] ?? '', 200);
$existingFb    = $clean($_POST['existing_fb'] ?? '', 200);

/* ── Server-side validation ── */
$errors = [];
if (!isset($cfg['plans'][$plan]))            $errors[] = 'Pick a valid plan.';
if ($businessName === '')                     $errors[] = 'Business name is required.';
if ($contactName === '')                      $errors[] = 'Your name is required.';
if (!tsc_valid_phone_au($phone))              $errors[] = 'Valid Australian phone is required.';
if (!tsc_valid_email($email))                 $errors[] = 'Valid email is required.';
if (!tsc_valid_abn($abn))                     $errors[] = 'Valid 11-digit ABN is required.';

$trades = require __DIR__ . '/../trades/_trades.php';
if (!isset($trades[$trade]))                  $errors[] = 'Pick a valid trade.';

if (in_array($trade, $cfg['licence_required_slugs'], true) && $licence === '') {
    $errors[] = 'Licence number is required for your trade.';
}
if ($service1 === '')                         $errors[] = 'At least one service is required.';

if ($errors) {
    http_response_code(400);
    echo "<!doctype html><meta charset=utf-8><title>Fix a few things</title>";
    echo "<h1>Please fix the following:</h1><ul>";
    foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>';
    echo '</ul><p><a href="/signup/">Back to signup</a></p>';
    exit;
}

/* ── Reference + record storage ── */
$reference = tsc_generate_reference($businessName);
while (file_exists($cfg['paths']['records_dir'] . '/' . $reference . '.json')) {
    $reference = tsc_generate_reference($businessName);
}

$assetDir = $cfg['paths']['assets_dir'] . '/' . $reference;
@mkdir($assetDir, 0755, true);
@mkdir($assetDir . '/photos', 0755, true);

/* ── File uploads ── */
$logoFilename = null;
if (!empty($_FILES['logo']['name'])) {
    $logoFilename = tsc_save_upload(
        $_FILES['logo'],
        $assetDir,
        $cfg['logo_exts'],
        $cfg['logo_max_bytes'],
        'logo'
    );
}

$photoFilenames = [];
if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
    $count = count($_FILES['photos']['name']);
    for ($i = 0; $i < min($count, 5); $i++) {
        $one = [
            'name'     => $_FILES['photos']['name'][$i]     ?? '',
            'type'     => $_FILES['photos']['type'][$i]     ?? '',
            'tmp_name' => $_FILES['photos']['tmp_name'][$i] ?? '',
            'error'    => $_FILES['photos']['error'][$i]    ?? UPLOAD_ERR_NO_FILE,
            'size'     => $_FILES['photos']['size'][$i]     ?? 0,
        ];
        $saved = tsc_save_upload(
            $one,
            $assetDir . '/photos',
            $cfg['photo_exts'],
            $cfg['photo_max_bytes'],
            'photo' . ($i + 1)
        );
        if ($saved) $photoFilenames[] = $saved;
    }
}

/* ── Build record ── */
$record = [
    'date'                   => date('Y-m-d H:i:s'),
    'reference'              => $reference,
    'plan'                   => $plan,
    'business_name'          => $businessName,
    'contact_name'           => $contactName,
    'phone'                  => $phone,
    'email'                  => $email,
    'abn'                    => $abn,
    'trade'                  => $trades[$trade]['name'] ?? $trade,
    'trade_slug'             => $trade,
    'suburbs'                => $suburbs,
    'licence'                => $licence,
    'tagline'                => $tagline,
    'services'               => implode(' | ', array_filter([$service1, $service2, $service3])),
    'years'                  => $years,
    'existing_website'       => $existingWeb,
    'existing_fb'            => $existingFb,
    'logo_path'              => $logoFilename ? "assets/{$reference}/{$logoFilename}" : '',
    'photo_paths'            => implode(';', array_map(fn($p) => "assets/{$reference}/photos/{$p}", $photoFilenames)),
    'status'                 => 'awaiting_payment',
    'payment_confirmed_date' => '',
    'ip'                     => $ip,
];

/* ── Persist ── */
@file_put_contents(
    $cfg['paths']['records_dir'] . '/' . $reference . '.json',
    json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);
tsc_csv_append($record);

/* ── Emails (fire both; swallow failures so customer still sees the pay page) ── */
try { tsc_email_customer_receipt($record); }   catch (Throwable $e) {}
try { tsc_email_admin_notification($record); } catch (Throwable $e) {}

/* ── Redirect to pay page ── */
header('Location: /signup/pay?ref=' . urlencode($reference));
exit;
