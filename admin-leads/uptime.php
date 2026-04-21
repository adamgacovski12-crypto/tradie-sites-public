<?php
/**
 * Tradie Sites Co. — uptime check for hosted client sites.
 *
 * Pings every deployed Hosted-plan client's live URL. Logs the response.
 * If a site has been down for 2 consecutive checks (default), emails Adam.
 * Avoids alert spam by tracking consecutive_failures per client.
 *
 * Crontab — every 10 minutes:
 *   *‌/10 * * * * /usr/bin/php /var/www/site.tradiebud.tech/admin-leads/uptime.php >> /var/www/site.tradiebud.tech/signups/uptime.log 2>&1
 *
 * Pass --dry-run to log without alerting.
 *
 * State is persisted to /signups/uptime.json so we know which sites are
 * currently flagged down across cron invocations.
 *
 * CLI-only.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden — CLI only.\n";
    exit(1);
}

require __DIR__ . '/../signup/_helpers.php';

$cfg     = tsc_cfg();
$dryRun  = in_array('--dry-run', $argv ?? [], true);
$now     = time();
$stamp   = date('Y-m-d H:i:s', $now);

$log = function(string $m) use ($stamp) {
    fwrite(STDOUT, "[{$stamp}] {$m}\n");
};

$log($dryRun ? '=== UPTIME DRY RUN ===' : '=== UPTIME CHECK ===');

$statePath = $cfg['paths']['signups_dir'] . '/uptime.json';
$state = file_exists($statePath)
    ? (json_decode((string)@file_get_contents($statePath), true) ?: [])
    : [];

$ALERT_AFTER_FAILURES = 2;       /* alert after this many consecutive failures */
$REQUEST_TIMEOUT = 12;           /* per-site HTTP timeout in seconds */

$checked = 0; $up = 0; $down = 0; $alerted = 0;

foreach (tsc_csv_read_all() as $r) {
    if (($r['status'] ?? '') !== 'deployed') continue;
    if (($r['plan']   ?? '') !== 'hosted')   continue;
    $url = trim((string)($r['live_url'] ?? ''));
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) continue;

    $ref = $r['reference'];
    $biz = $r['business_name'] ?? $ref;
    $checked++;

    /* HEAD first; some hosts respond differently to HEAD vs GET, so on a
     * non-2xx HEAD we retry with GET to reduce false positives. */
    [$ok, $reason] = tsc_uptime_probe($url, 'HEAD', $REQUEST_TIMEOUT);
    if (!$ok) {
        [$ok, $reason] = tsc_uptime_probe($url, 'GET', $REQUEST_TIMEOUT);
    }

    $entry = $state[$ref] ?? ['consecutive_failures' => 0, 'last_alert_at' => 0, 'last_check_at' => 0];
    $entry['last_check_at'] = $now;
    $entry['last_check_url'] = $url;

    if ($ok) {
        if ($entry['consecutive_failures'] > 0) {
            $log("RECOVERED {$ref} ({$biz}) — was down {$entry['consecutive_failures']} checks");
        }
        $entry['consecutive_failures'] = 0;
        $entry['last_seen_up_at'] = $now;
        $up++;
    } else {
        $entry['consecutive_failures']++;
        $entry['last_failure_reason'] = $reason;
        $down++;
        $log("DOWN {$ref} ({$biz}) — {$reason} — failures={$entry['consecutive_failures']}");

        $shouldAlert = $entry['consecutive_failures'] >= $ALERT_AFTER_FAILURES
            && ($now - ($entry['last_alert_at'] ?? 0)) > 1800; /* throttle to 1 alert per 30 min */

        if ($shouldAlert && !$dryRun) {
            try {
                tsc_email_uptime_alert($ref, $biz, $url, $reason);
                $entry['last_alert_at'] = $now;
                $alerted++;
                $log("ALERTED Adam about {$ref}");
            } catch (Throwable $e) {
                $log("ALERT email failed for {$ref}: " . $e->getMessage());
            }
        }
    }

    $state[$ref] = $entry;
}

/* Drop state for refs that are no longer deployed-hosted. */
$activeRefs = [];
foreach (tsc_csv_read_all() as $r) {
    if (($r['status'] ?? '') === 'deployed' && ($r['plan'] ?? '') === 'hosted') {
        $activeRefs[$r['reference']] = true;
    }
}
$state = array_intersect_key($state, $activeRefs);

if (!$dryRun) {
    @file_put_contents($statePath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$log("Checked={$checked} up={$up} down={$down} alerted={$alerted}");
$log('=== UPTIME DONE ===');

/**
 * Probe a URL. Returns [ok:bool, reason:string].
 * 2xx and 3xx (with valid Location chain) count as up.
 */
function tsc_uptime_probe(string $url, string $method, int $timeout): array {
    $code = 0;
    $err  = '';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => $method === 'HEAD',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_USERAGENT      => 'TradieSitesUptimeBot/1.0 (+https://site.tradiebud.tech/admin-leads/uptime.php)',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
    } else {
        /* Stream context fallback. */
        $ctx = stream_context_create([
            'http' => [
                'method'        => $method,
                'timeout'       => $timeout,
                'ignore_errors' => true,
                'header'        => "User-Agent: TradieSitesUptimeBot/1.0\r\n",
                'follow_location' => 1,
                'max_redirects'   => 5,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        @file_get_contents($url, false, $ctx);
        if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
            $code = (int)$m[1];
        }
    }

    if ($code >= 200 && $code < 400) {
        return [true, "HTTP {$code}"];
    }
    if ($code === 0) {
        return [false, 'no response' . ($err !== '' ? " ({$err})" : '')];
    }
    return [false, "HTTP {$code}"];
}
