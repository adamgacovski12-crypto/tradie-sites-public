<?php
/**
 * Shared helpers for signup + admin-leads.
 * Blocked from web by the ^_ rule in /.htaccess.
 */

function tsc_cfg(): array {
    static $cfg = null;
    if ($cfg === null) $cfg = require __DIR__ . '/_config.php';
    return $cfg;
}

function tsc_ensure_dirs(): void {
    $cfg = tsc_cfg();
    foreach (['signups_dir','records_dir','assets_dir','ratelimits_dir'] as $k) {
        $p = $cfg['paths'][$k];
        if (!is_dir($p)) @mkdir($p, 0755, true);
    }
}

function tsc_ensure_signups_htaccess(): void {
    $cfg = tsc_cfg();
    $p = $cfg['paths']['signups_dir'] . '/.htaccess';
    if (!file_exists($p)) {
        @file_put_contents($p, "Require all denied\nDeny from all\n");
    }
}

function tsc_h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function tsc_slugify_biz(string $name): string {
    $n = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
    if ($n === '') $n = 'TRADIE';
    return substr($n, 0, 6);
}

function tsc_generate_reference(string $businessName): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; /* no ambiguous chars */
    $random = '';
    for ($i = 0; $i < 4; $i++) $random .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    return 'TSC-' . tsc_slugify_biz($businessName) . '-' . $random;
}

function tsc_valid_abn(string $abn): bool {
    $digits = preg_replace('/\D/', '', $abn);
    return strlen($digits) === 11;
}

function tsc_valid_phone_au(string $phone): bool {
    $digits = preg_replace('/\D/', '', $phone);
    // AU phone: 10 digits (starts 0) or 11 digits (61 + 9), mobile often 04xx
    if (strlen($digits) === 10 && $digits[0] === '0') return true;
    if (strlen($digits) === 11 && substr($digits, 0, 2) === '61') return true;
    return false;
}

function tsc_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function tsc_csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if (empty($_SESSION['tsc_csrf'])) $_SESSION['tsc_csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['tsc_csrf'];
}

function tsc_csrf_ok(?string $token): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    return $token && !empty($_SESSION['tsc_csrf']) && hash_equals($_SESSION['tsc_csrf'], $token);
}

function tsc_rate_limit_check(string $ip): bool {
    $cfg = tsc_cfg();
    tsc_ensure_dirs();
    $file = $cfg['paths']['ratelimits_dir'] . '/' . md5($ip) . '.json';
    $now = time();
    $data = ['count' => 0, 'reset' => $now + 3600];
    if (file_exists($file)) {
        $data = json_decode((string)@file_get_contents($file), true) ?: $data;
        if ($now > ($data['reset'] ?? 0)) $data = ['count' => 0, 'reset' => $now + 3600];
    }
    $data['count']++;
    @file_put_contents($file, json_encode($data));
    return $data['count'] <= $cfg['rate_limit_per_hour'];
}

/* ── File upload: return saved filename or null ── */
function tsc_save_upload(array $file, string $destDir, array $allowedExts, int $maxBytes, string $prefix = ''): ?string {
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) return null;
    if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
    $baseName = preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
    if ($baseName === '') $baseName = 'file';
    $safe = ($prefix !== '' ? $prefix . '-' : '') . substr($baseName, 0, 30) . '.' . $ext;
    $target = $destDir . '/' . $safe;
    /* avoid collisions */
    $i = 1;
    while (file_exists($target)) {
        $safe = ($prefix !== '' ? $prefix . '-' : '') . substr($baseName, 0, 30) . '-' . $i . '.' . $ext;
        $target = $destDir . '/' . $safe;
        $i++;
        if ($i > 20) return null;
    }
    if (!@move_uploaded_file($file['tmp_name'], $target)) return null;
    return $safe;
}

/* ── CSV schema ── */
function tsc_csv_header(): array {
    return [
        'date','reference','plan','business_name','contact_name','phone','email',
        'abn','trade','suburbs','licence','tagline','services','years',
        'existing_website','existing_fb','logo_path','photo_paths',
        'status','payment_confirmed_date','live_url','deployed_date','last_invoice_sent'
    ];
}

function tsc_csv_append(array $row): void {
    $cfg = tsc_cfg();
    tsc_ensure_dirs();
    $path = $cfg['paths']['csv_file'];
    $isNew = !file_exists($path);
    $fp = fopen($path, 'a');
    if ($fp === false) return;
    flock($fp, LOCK_EX);
    if ($isNew) fputcsv($fp, tsc_csv_header());
    $out = [];
    foreach (tsc_csv_header() as $col) $out[] = (string)($row[$col] ?? '');
    fputcsv($fp, $out);
    flock($fp, LOCK_UN);
    fclose($fp);
}

function tsc_csv_read_all(): array {
    $cfg = tsc_cfg();
    $path = $cfg['paths']['csv_file'];
    if (!file_exists($path)) return [];
    $rows = [];
    if (($fp = fopen($path, 'r')) === false) return [];
    $header = null;
    while (($line = fgetcsv($fp)) !== false) {
        if ($header === null) { $header = $line; continue; }
        $rows[] = array_combine($header, array_pad($line, count($header), ''));
    }
    fclose($fp);
    return $rows;
}

/* ── Update a single signup status in both CSV + JSON ── */
function tsc_update_status(string $reference, string $newStatus, ?string $confirmedDate = null): bool {
    $cfg = tsc_cfg();
    $path = $cfg['paths']['csv_file'];
    if (!file_exists($path)) return false;
    $rows = tsc_csv_read_all();
    $found = false;
    foreach ($rows as &$r) {
        if ($r['reference'] === $reference) {
            $r['status'] = $newStatus;
            if ($confirmedDate !== null) $r['payment_confirmed_date'] = $confirmedDate;
            $found = true;
            break;
        }
    }
    unset($r);
    if (!$found) return false;
    $fp = fopen($path, 'w');
    if ($fp === false) return false;
    flock($fp, LOCK_EX);
    fputcsv($fp, tsc_csv_header());
    foreach ($rows as $r) {
        $out = [];
        foreach (tsc_csv_header() as $col) $out[] = (string)($r[$col] ?? '');
        fputcsv($fp, $out);
    }
    flock($fp, LOCK_UN);
    fclose($fp);

    /* update JSON record too */
    $json = $cfg['paths']['records_dir'] . '/' . $reference . '.json';
    if (file_exists($json)) {
        $rec = json_decode((string)@file_get_contents($json), true) ?: [];
        $rec['status'] = $newStatus;
        if ($confirmedDate !== null) $rec['payment_confirmed_date'] = $confirmedDate;
        @file_put_contents($json, json_encode($rec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    return true;
}

function tsc_load_record(string $reference): ?array {
    $cfg = tsc_cfg();
    $ref = preg_replace('/[^A-Z0-9-]/', '', strtoupper($reference));
    if ($ref === '') return null;
    $p = $cfg['paths']['records_dir'] . '/' . $ref . '.json';
    if (!file_exists($p)) return null;
    $data = json_decode((string)@file_get_contents($p), true);
    return is_array($data) ? $data : null;
}

/* ── Email helpers ──
 *
 * Primary sender: SMTP-AUTH to Adam's cPanel mailbox on VentraIP.
 *   Env vars: SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS
 *   Example:  mail.tradiebud.tech, 587, info@tradiebud.tech, <mailbox pass>
 *
 * Fallback: PHP mail() — only works locally if a sendmail-compatible MTA is configured.
 *
 * Every failure is logged to signups/mail.log so you can see what tried and why.
 */
function tsc_mail(string $to, string $subject, string $body, ?string $replyTo = null): bool {
    $cfg  = tsc_cfg();
    $from = $cfg['from_email'];
    $fromName = $cfg['from_name'];
    $reply = $replyTo ?? $from;

    /* 1. cPanel SMTP */
    $smtpHost = (string)getenv('SMTP_HOST');
    if ($smtpHost !== '') {
        $ok = tsc_mail_via_smtp($to, $subject, $body, $from, $fromName, $reply);
        if ($ok) return true;
        tsc_mail_log("SMTP failed for {$to} (subject: {$subject}) — falling back to PHP mail()");
    }

    /* 2. PHP mail() — last resort */
    $headers  = "From: {$fromName} <{$from}>\r\n";
    $headers .= "Reply-To: {$reply}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $ok = @mail($to, $subject, $body, $headers);
    if (!$ok) tsc_mail_log("PHP mail() also failed for {$to} (subject: {$subject})");
    return $ok;
}

/**
 * Minimal SMTP-AUTH client. Zero external dependencies.
 * Supports STARTTLS (port 587) and implicit TLS (port 465).
 * AUTH LOGIN only (no PLAIN/CRAM-MD5) — enough for cPanel/Exim.
 * Returns true on a successful send; logs + returns false on any error.
 */
function tsc_mail_via_smtp(string $to, string $subject, string $body, string $from, string $fromName, string $reply): bool {
    $host = (string)getenv('SMTP_HOST');
    $port = (int)(getenv('SMTP_PORT') ?: 587);
    $user = (string)getenv('SMTP_USER');
    $pass = (string)getenv('SMTP_PASS');
    if ($host === '' || $user === '' || $pass === '') {
        tsc_mail_log("SMTP config incomplete (host/user/pass required)");
        return false;
    }

    $useImplicitTls = ($port === 465);
    $connectHost = $useImplicitTls ? "ssl://{$host}" : $host;
    $errno = 0; $errstr = '';
    $fp = @stream_socket_client("{$connectHost}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if ($fp === false) {
        tsc_mail_log("SMTP connect failed {$host}:{$port} — {$errno} {$errstr}");
        return false;
    }
    stream_set_timeout($fp, 15);

    $read = function() use ($fp) {
        $out = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) return $out;
            $out .= $line;
            /* SMTP multi-line replies use a '-' after the code on continuation lines; final line uses ' '. */
            if (strlen($line) >= 4 && $line[3] === ' ') break;
        }
        return $out;
    };
    $send = function(string $cmd) use ($fp) { fwrite($fp, $cmd . "\r\n"); };
    $expect = function(string $resp, string $prefix) {
        return substr($resp, 0, strlen($prefix)) === $prefix;
    };

    $greeting = $read();
    if (!$expect($greeting, '220')) { tsc_mail_log("SMTP greeting bad: " . trim($greeting)); fclose($fp); return false; }

    $hostname = gethostname() ?: 'localhost';
    $send("EHLO {$hostname}");
    $ehlo1 = $read();
    if (!$expect($ehlo1, '250')) { tsc_mail_log("SMTP EHLO bad: " . trim($ehlo1)); fclose($fp); return false; }

    /* STARTTLS upgrade for port 587 (most cPanel hosts). */
    if (!$useImplicitTls) {
        $send('STARTTLS');
        $tlsResp = $read();
        if (!$expect($tlsResp, '220')) { tsc_mail_log("SMTP STARTTLS refused: " . trim($tlsResp)); fclose($fp); return false; }
        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            tsc_mail_log("SMTP TLS negotiation failed for {$host}:{$port}");
            fclose($fp); return false;
        }
        /* Re-EHLO after TLS per RFC 3207 */
        $send("EHLO {$hostname}");
        $ehlo2 = $read();
        if (!$expect($ehlo2, '250')) { tsc_mail_log("SMTP EHLO-after-TLS bad: " . trim($ehlo2)); fclose($fp); return false; }
    }

    /* AUTH LOGIN */
    $send('AUTH LOGIN');
    $authResp = $read();
    if (!$expect($authResp, '334')) { tsc_mail_log("SMTP AUTH LOGIN rejected: " . trim($authResp)); fclose($fp); return false; }
    $send(base64_encode($user));
    $userResp = $read();
    if (!$expect($userResp, '334')) { tsc_mail_log("SMTP username rejected: " . trim($userResp)); fclose($fp); return false; }
    $send(base64_encode($pass));
    $passResp = $read();
    if (!$expect($passResp, '235')) { tsc_mail_log("SMTP password rejected: " . trim($passResp)); fclose($fp); return false; }

    /* Envelope */
    $send("MAIL FROM:<{$from}>");
    $mfResp = $read();
    if (!$expect($mfResp, '250')) { tsc_mail_log("SMTP MAIL FROM bad: " . trim($mfResp)); fclose($fp); return false; }
    $send("RCPT TO:<{$to}>");
    $rcptResp = $read();
    if (!$expect($rcptResp, '250') && !$expect($rcptResp, '251')) {
        tsc_mail_log("SMTP RCPT TO bad ({$to}): " . trim($rcptResp)); fclose($fp); return false;
    }
    $send('DATA');
    $dataResp = $read();
    if (!$expect($dataResp, '354')) { tsc_mail_log("SMTP DATA refused: " . trim($dataResp)); fclose($fp); return false; }

    /* Headers + body. Dot-stuffing: any line starting with '.' must be escaped. */
    $messageId = '<' . bin2hex(random_bytes(8)) . '@' . (preg_replace('/^[^@]+@/', '', $from) ?: 'tradiebud.tech') . '>';
    $date = date('r');
    $subjEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $fromEnc = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

    $headers  = "Date: {$date}\r\n";
    $headers .= "From: {$fromEnc} <{$from}>\r\n";
    $headers .= "To: <{$to}>\r\n";
    $headers .= "Reply-To: <{$reply}>\r\n";
    $headers .= "Subject: {$subjEnc}\r\n";
    $headers .= "Message-ID: {$messageId}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    $normalisedBody = preg_replace('/(?<!\r)\n/', "\r\n", $body);
    /* Dot-stuff lines that start with a single dot */
    $dotSafeBody = preg_replace('/^\./m', '..', $normalisedBody);

    fwrite($fp, $headers . "\r\n" . $dotSafeBody . "\r\n.\r\n");
    $finalResp = $read();
    if (!$expect($finalResp, '250')) { tsc_mail_log("SMTP DATA end refused: " . trim($finalResp)); fclose($fp); return false; }

    $send('QUIT');
    @$read();
    fclose($fp);
    return true;
}


function tsc_mail_log(string $line): void {
    $cfg = tsc_cfg();
    tsc_ensure_dirs();
    $path = $cfg['paths']['signups_dir'] . '/mail.log';
    @file_put_contents($path, '[' . date('Y-m-d H:i:s') . '] ' . $line . "\n", FILE_APPEND | LOCK_EX);
}

function tsc_email_customer_receipt(array $rec): void {
    $cfg = tsc_cfg();
    $plan = $cfg['plans'][$rec['plan']] ?? $cfg['plans']['hosted'];
    $bank = $cfg['bank'];
    $isHosted = !empty($plan['is_hosted']);
    $subject = 'Your Tradie Sites Co. signup — next step';
    $body  = "G'day {$rec['contact_name']},\n\n";
    $body .= "Thanks for signing up. Here's what you chose:\n\n";
    $body .= "Plan: {$plan['label']} — {$plan['sub']}\n";
    $body .= "Reference: {$rec['reference']}\n\n";
    $body .= "To get your site built, transfer \$200 to:\n\n";
    $body .= "Account: {$bank['account_name']}\n";
    $body .= "BSB: {$bank['bsb']}\n";
    $body .= "Account number: {$bank['account_number']}\n";
    $body .= "Reference: {$rec['reference']}  <- include this in the transfer description\n\n";
    $body .= "Your site will be live within 24 hours of the payment landing.\n\n";
    if ($isHosted) {
        $body .= "Hosting ($80/month) kicks in a month from go-live. If hosting payments stop, the site comes offline — that's how hosting works, no surprises.\n\n";
        $body .= "Changes, new pages or new features aren't part of the $80 — reply any time and we'll quote separately.\n\n";
    } else {
        $body .= "On self-host: once payment lands and we finish building, we'll email the full source files + DNS setup notes. From there the site's entirely yours to host wherever you like — no ongoing fee from us.\n\n";
        $body .= "Need changes or a rebuild later? Reply any time and we'll quote separately.\n\n";
    }
    $body .= "Any questions, reply to this email.\n\n";
    $body .= "Cheers,\nTradie Sites Co.\n";
    tsc_mail($rec['email'], $subject, $body);
}

function tsc_email_admin_notification(array $rec): void {
    $cfg = tsc_cfg();
    $plan = $cfg['plans'][$rec['plan']] ?? $cfg['plans']['hosted'];
    $subject = "\xF0\x9F\x94\xA5 NEW SIGNUP: {$rec['business_name']} — {$plan['label']}";
    $body  = "NEW SIGNUP\n";
    $body .= "──────────────────\n";
    foreach ([
        'Date'=>$rec['date'] ?? '','Reference'=>$rec['reference'] ?? '',
        'Plan'=>$plan['label'],
        'Business'=>$rec['business_name'] ?? '','Contact'=>$rec['contact_name'] ?? '',
        'Phone'=>$rec['phone'] ?? '','Email'=>$rec['email'] ?? '',
        'ABN'=>$rec['abn'] ?? '','Trade'=>$rec['trade'] ?? '',
        'Suburbs'=>$rec['suburbs'] ?? '','Licence'=>$rec['licence'] ?? '',
        'Tagline'=>$rec['tagline'] ?? '',
        'Services'=>$rec['services'] ?? '',
        'Years'=>$rec['years'] ?? '',
        'Existing website'=>$rec['existing_website'] ?? '',
        'Existing Facebook'=>$rec['existing_fb'] ?? '',
        'Logo'=>$rec['logo_path'] ?? '—',
        'Photos'=>$rec['photo_paths'] ?? '—',
        'Status'=>$rec['status'] ?? 'awaiting_payment',
    ] as $k => $v) {
        $body .= str_pad($k . ':', 20) . $v . "\n";
    }
    tsc_mail($cfg['admin_email'], $subject, $body, $rec['email'] ?? null);
}

function tsc_email_payment_confirmed(array $rec): void {
    $cfg = tsc_cfg();
    $plan = $cfg['plans'][$rec['plan']] ?? $cfg['plans']['hosted'];
    $isHosted = !empty($plan['is_hosted']);
    $signupDate = $rec['date'] ?? date('Y-m-d H:i:s');
    $subject = 'Payment received — your site is being built now';
    $body  = "G'day {$rec['contact_name']},\n\n";
    $body .= "Got your \$200. We're on it.\n\n";
    if ($isHosted) {
        $nextDue = date('j F Y', strtotime($signupDate . ' ' . $plan['recurring_interval']));
        $body .= "Your site will be live within 24 hours at a URL we'll email you once ready.\n\n";
        $body .= "First hosting invoice: {$plan['recurring_label']}, due {$nextDue}. We'll email bank details a week before.\n\n";
        $body .= "Reminder: if hosting payments stop, the site goes offline. Disclosed at signup — just so we're on the same page.\n\n";
        $body .= "Content edits, extra pages and new features are quoted separately — reply any time to request a quote.\n\n";
    } else {
        $body .= "Self-host build: your site will be built within 24 hours. When it's ready, we'll email the full source files + DNS setup notes. No ongoing fee from us.\n\n";
        $body .= "Want changes or a rebuild later? Reply any time — we quote separately.\n\n";
    }
    $body .= "Any questions, reply.\n\nCheers,\nTradie Sites Co.\n";
    tsc_mail($rec['email'], $subject, $body);
}

function tsc_email_site_live(array $rec, string $liveUrl): void {
    $subject = 'Your site is live — ' . $liveUrl;
    $body  = "G'day {$rec['contact_name']},\n\n";
    $body .= "Your site is live at:\n{$liveUrl}\n\n";
    $body .= "Ring us if anything looks off. Otherwise, you're sorted — customers can find you on Google now.\n\n";
    $body .= "Reminder: content changes, new pages or new features are quoted separately. Reply any time and we'll sort a quote.\n\n";
    $body .= "Cheers,\nTradie Sites Co.\n";
    tsc_mail($rec['email'], $subject, $body);
}

function tsc_email_recurring_invoice(array $rec): void {
    $cfg = tsc_cfg();
    $plan = $cfg['plans'][$rec['plan']] ?? $cfg['plans']['hosted'];
    /* Self-host has no recurring billing — no-op if someone calls it by mistake. */
    if (empty($plan['is_hosted'])) return;
    $bank = $cfg['bank'];
    $dueDate = date('j F Y', strtotime('+0 day'));
    $invoiceRef = $rec['reference'] . '-' . date('Ymd');
    $month = strtoupper(date('F Y'));
    $subject = "Your Tradie Sites Co. hosting invoice — {$month}";
    $body  = "G'day {$rec['contact_name']},\n\n";
    $body .= "Your hosting fee ({$plan['recurring_label']}) is due on {$dueDate}.\n\n";
    $body .= "Transfer to:\n";
    $body .= "Account: {$bank['account_name']}\n";
    $body .= "BSB: {$bank['bsb']}\n";
    $body .= "Account number: {$bank['account_number']}\n";
    $body .= "Reference: {$invoiceRef}\n\n";
    $body .= "This covers hosting, uptime monitoring and breakage fixes for the next month. If you've got content changes or new pages to add, reply and we'll quote those separately.\n\n";
    $body .= "Miss this one and the site goes offline until it's cleared — disclosed at signup.\n\n";
    $body .= "Cheers,\nTradie Sites Co.\n";
    tsc_mail($rec['email'], $subject, $body);
}

/* ── Record updates for cron + admin actions ── */
function tsc_update_last_invoice_sent(string $reference, string $when): bool {
    $cfg = tsc_cfg();
    $path = $cfg['paths']['csv_file'];
    if (!file_exists($path)) return false;
    $rows = tsc_csv_read_all();
    $found = false;
    foreach ($rows as &$r) {
        if ($r['reference'] === $reference) {
            $r['last_invoice_sent'] = $when;
            $found = true;
            break;
        }
    }
    unset($r);
    if (!$found) return false;
    $fp = fopen($path, 'w');
    if ($fp === false) return false;
    flock($fp, LOCK_EX);
    fputcsv($fp, tsc_csv_header());
    foreach ($rows as $r) {
        $out = [];
        foreach (tsc_csv_header() as $col) $out[] = (string)($r[$col] ?? '');
        fputcsv($fp, $out);
    }
    flock($fp, LOCK_UN);
    fclose($fp);

    $json = $cfg['paths']['records_dir'] . '/' . $reference . '.json';
    if (file_exists($json)) {
        $rec = json_decode((string)@file_get_contents($json), true) ?: [];
        $rec['last_invoice_sent'] = $when;
        @file_put_contents($json, json_encode($rec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    return true;
}

/* ── Admin audit log: append one line per mutating action ── */
function tsc_admin_log(string $action, string $reference, string $detail = ''): void {
    $cfg = tsc_cfg();
    tsc_ensure_dirs();
    $path = $cfg['paths']['signups_dir'] . '/admin.log';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $user = $_SERVER['PHP_AUTH_USER'] ?? '-';
    $line = sprintf(
        "[%s] user=%s ip=%s action=%s reference=%s%s\n",
        date('Y-m-d H:i:s'),
        $user,
        $ip,
        $action,
        $reference,
        $detail !== '' ? ' ' . $detail : ''
    );
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}
