<?php
/**
 * Admin ops actions. Protected by parent .htaccess Basic Auth.
 */
require __DIR__ . '/../signup/_helpers.php';
$cfg = tsc_cfg();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$reference = strtoupper(preg_replace('/[^A-Z0-9-]/i', '', (string)($_POST['reference'] ?? $_GET['reference'] ?? '')));

if ($reference === '') {
    header('Location: /admin-leads/?flash=Missing+reference');
    exit;
}

/* ── View uploads listing ── */
if ($action === 'list_uploads') {
    $rec = tsc_load_record($reference);
    if (!$rec) {
        header('Location: /admin-leads/?flash=Reference+not+found');
        exit;
    }
    $assetDir = $cfg['paths']['assets_dir'] . '/' . $reference;
    $files = [];
    if (is_dir($assetDir)) {
        foreach (glob($assetDir . '/*') ?: [] as $f) {
            if (is_file($f)) $files[] = basename($f);
        }
        foreach (glob($assetDir . '/photos/*') ?: [] as $f) {
            if (is_file($f)) $files[] = 'photos/' . basename($f);
        }
    }
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><meta charset=utf-8><title>Uploads — ' . tsc_h($reference) . '</title>';
    echo '<style>body{font-family:sans-serif;background:#111;color:#eee;padding:28px}h1{color:#FF6A00}a{color:#FF6A00}</style>';
    echo '<h1>Uploads for ' . tsc_h($reference) . '</h1>';
    echo '<p><a href="/admin-leads/">← Back to leads</a></p>';
    if (empty($files)) {
        echo '<p>No files uploaded for this signup.</p>';
    } else {
        echo '<p>Files are stored under <code>/signups/assets/' . tsc_h($reference) . '/</code> (blocked from web access).</p>';
        echo '<ul>';
        foreach ($files as $f) echo '<li>' . tsc_h($f) . '</li>';
        echo '</ul>';
        echo '<p style="color:#888;font-size:.85rem">To view or download, SSH into the server and copy from the path above.</p>';
    }
    exit;
}

/* ── Mutating actions require POST ── */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: /admin-leads/');
    exit;
}

if ($action === 'mark_paid') {
    $ok = tsc_update_status($reference, 'paid', date('Y-m-d H:i:s'));
    if (!$ok) {
        header('Location: /admin-leads/?flash=Reference+not+found');
        exit;
    }
    $rec = tsc_load_record($reference);
    if ($rec) {
        try { tsc_email_payment_confirmed($rec); } catch (Throwable $e) {}
    }
    header('Location: /admin-leads/?flash=' . urlencode($reference . ' marked paid + customer emailed'));
    exit;
}

if ($action === 'cancel') {
    tsc_update_status($reference, 'cancelled');
    header('Location: /admin-leads/?flash=' . urlencode($reference . ' cancelled'));
    exit;
}

if ($action === 'send_invoice') {
    $rec = tsc_load_record($reference);
    if ($rec) {
        try { tsc_email_recurring_invoice($rec); } catch (Throwable $e) {}
        header('Location: /admin-leads/?flash=' . urlencode('Invoice sent to ' . $reference));
        exit;
    }
}

header('Location: /admin-leads/?flash=Unknown+action');
exit;
