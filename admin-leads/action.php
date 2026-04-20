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

$buildsRoot = dirname(__DIR__) . '/builds';

/* ── View build folder (files list + preview iframe) ── */
if ($action === 'view_build') {
    $bd = $buildsRoot . '/' . $reference;
    if (!is_dir($bd)) {
        header('Location: /admin-leads/?flash=' . urlencode('Build not prepped yet — click PREP BUILD first'));
        exit;
    }
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($bd, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile()) {
            $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($bd) + 1));
            $files[] = [$rel, $f->getSize()];
        }
    }
    sort($files);
    $homeExists = file_exists($bd . '/home.html');
    $baseExists = file_exists($bd . '/base.html');
    $previewFile = $homeExists ? 'home.html' : ($baseExists ? 'base.html' : null);

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang=en-AU><meta charset=utf-8><title>Build — ' . tsc_h($reference) . '</title>';
    echo '<style>body{font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:#111;color:#eee;padding:28px;margin:0}h1{color:#FF6A00;margin:0 0 8px}a{color:#FF6A00}table{border-collapse:collapse;margin:16px 0;background:#1a1a1a}td,th{padding:6px 12px;border-bottom:1px solid #333;font-size:.88rem;text-align:left}th{color:#888;font-size:.78rem;letter-spacing:1px;text-transform:uppercase}iframe{width:100%;height:70vh;border:3px solid #FF6A00;margin-top:16px;background:#fff}</style>';
    echo '<h1>Build files for ' . tsc_h($reference) . '</h1>';
    echo '<p><a href="/admin-leads/">← Back to leads</a> &nbsp;·&nbsp; Folder: <code>' . tsc_h($bd) . '</code></p>';
    echo '<table><thead><tr><th>File</th><th>Size</th></tr></thead><tbody>';
    foreach ($files as [$rel, $sz]) {
        echo '<tr><td><code>' . tsc_h($rel) . '</code></td><td>' . number_format((int)$sz) . '&nbsp;B</td></tr>';
    }
    echo '</tbody></table>';
    if ($previewFile) {
        echo '<p style="color:#888;font-size:.85rem">Preview of <code>' . tsc_h($previewFile) . '</code> (note: relative image/CSS paths will 404 in this admin preview — deploy to Cloudflare Pages for real preview):</p>';
        echo '<iframe src="/admin-leads/action.php?action=preview_build&reference=' . tsc_h($reference) . '&file=' . urlencode($previewFile) . '" sandbox="allow-same-origin"></iframe>';
    } else {
        echo '<p style="color:#f66">No preview file found. Has Claude Code generated <code>home.html</code> yet?</p>';
    }
    echo '</html>';
    exit;
}

/* ── Serve a single file from the build folder for preview iframe ── */
if ($action === 'preview_build') {
    $file = preg_replace('#[^a-zA-Z0-9._/-]#', '', (string)($_GET['file'] ?? ''));
    if ($file === '' || strpos($file, '..') !== false) {
        http_response_code(400); echo 'Bad file'; exit;
    }
    $target = $buildsRoot . '/' . $reference . '/' . $file;
    $real   = realpath($target);
    $bdReal = realpath($buildsRoot . '/' . $reference);
    if ($real === false || $bdReal === false || strpos($real, $bdReal) !== 0) {
        http_response_code(403); echo 'Forbidden'; exit;
    }
    $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    $types = [
        'html'=>'text/html; charset=UTF-8', 'htm'=>'text/html; charset=UTF-8',
        'css'=>'text/css', 'js'=>'application/javascript',
        'png'=>'image/png', 'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg',
        'svg'=>'image/svg+xml', 'webp'=>'image/webp', 'gif'=>'image/gif',
        'json'=>'application/json', 'md'=>'text/plain; charset=UTF-8',
    ];
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    header('X-Frame-Options: SAMEORIGIN');
    readfile($real);
    exit;
}

/* ── Download build folder as a ZIP ── */
if ($action === 'download_zip') {
    $bd = $buildsRoot . '/' . $reference;
    if (!is_dir($bd)) {
        header('Location: /admin-leads/?flash=' . urlencode('No build folder — run PREP BUILD first'));
        exit;
    }
    if (!class_exists('ZipArchive')) {
        header('Location: /admin-leads/?flash=' . urlencode('ZipArchive not available on this server — SFTP the folder manually'));
        exit;
    }
    $zipPath = sys_get_temp_dir() . '/tsc-' . $reference . '-' . time() . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        header('Location: /admin-leads/?flash=' . urlencode('Could not create zip'));
        exit;
    }
    /* Exclude: signup.json, build.log, photos/ (originals — we want images/ Claude wrote), CLAUDE_BUILD_PROMPT.md */
    $exclude = ['signup.json', 'build.log', 'CLAUDE_BUILD_PROMPT.md', 'CHANGES.md', 'base.html'];
    $excludeDirs = ['photos'];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($bd, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile()) continue;
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($bd) + 1));
        if (in_array(basename($rel), $exclude, true)) continue;
        $first = explode('/', $rel)[0];
        if (in_array($first, $excludeDirs, true)) continue;
        $zip->addFile($f->getPathname(), $rel);
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $reference . '.zip"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);
    @unlink($zipPath);
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
    tsc_admin_log('mark_paid', $reference);
    header('Location: /admin-leads/?flash=' . urlencode($reference . ' marked paid + customer emailed'));
    exit;
}

if ($action === 'cancel') {
    tsc_update_status($reference, 'cancelled');
    tsc_admin_log('cancel', $reference);
    header('Location: /admin-leads/?flash=' . urlencode($reference . ' cancelled'));
    exit;
}

if ($action === 'send_invoice') {
    $rec = tsc_load_record($reference);
    if ($rec) {
        try { tsc_email_recurring_invoice($rec); } catch (Throwable $e) {}
        tsc_update_last_invoice_sent($reference, date('Y-m-d H:i:s'));
        tsc_admin_log('send_invoice', $reference, 'manual');
        header('Location: /admin-leads/?flash=' . urlencode('Invoice sent to ' . $reference));
        exit;
    }
}

if ($action === 'prep_build') {
    $prepScript = dirname(__DIR__) . '/builder/prep.php';
    if (!file_exists($prepScript)) {
        header('Location: /admin-leads/?flash=' . urlencode('prep.php missing'));
        exit;
    }
    $phpBin = defined('PHP_BINARY') ? PHP_BINARY : 'php';
    $cmd = escapeshellarg($phpBin) . ' ' . escapeshellarg($prepScript) . ' ' . escapeshellarg($reference) . ' 2>&1';
    $output = []; $retval = 0;
    exec($cmd, $output, $retval);
    $tail = array_slice($output, -12);
    if ($retval !== 0) {
        tsc_admin_log('prep_build', $reference, 'FAILED: ' . implode(' | ', $tail));
        header('Location: /admin-leads/?flash=' . urlencode('PREP failed: ' . implode(' | ', $tail)));
        exit;
    }
    tsc_admin_log('prep_build', $reference);
    header('Location: /admin-leads/?flash=' . urlencode($reference . ' prepped. Open /builds/' . $reference . '/ in Claude Code.'));
    exit;
}

if ($action === 'mark_deployed') {
    $liveUrl = trim((string)($_POST['live_url'] ?? ''));
    if (!filter_var($liveUrl, FILTER_VALIDATE_URL)) {
        header('Location: /admin-leads/?flash=' . urlencode('Invalid URL'));
        exit;
    }
    /* Persist the live URL into the JSON record + CSV row */
    $rec = tsc_load_record($reference);
    if (!$rec) {
        header('Location: /admin-leads/?flash=' . urlencode('Reference not found'));
        exit;
    }
    $rec['live_url'] = $liveUrl;
    $rec['deployed_date'] = date('Y-m-d H:i:s');
    $jsonPath = $cfg['paths']['records_dir'] . '/' . $reference . '.json';
    @file_put_contents($jsonPath, json_encode($rec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    tsc_update_status($reference, 'deployed');

    /* Email the client */
    if (function_exists('tsc_email_site_live')) {
        try { tsc_email_site_live($rec, $liveUrl); } catch (Throwable $e) {}
    }
    tsc_admin_log('mark_deployed', $reference, 'url=' . $liveUrl);

    header('Location: /admin-leads/?flash=' . urlencode($reference . ' deployed → ' . $liveUrl));
    exit;
}

header('Location: /admin-leads/?flash=Unknown+action');
exit;
