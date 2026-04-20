<?php
/**
 * Tradie Sites Co. — daily housekeeping cron.
 *
 * Run via crontab on the server (once a day, early morning Sydney time):
 *
 *     5 7 * * * /usr/bin/php /var/www/site.tradiebud.tech/admin-leads/cron.php >> /var/www/site.tradiebud.tech/signups/cron.log 2>&1
 *
 * Tasks:
 *   1. Clean expired rate-limit JSON files (reset < now).
 *   2. Delete stale download ZIPs from the system temp dir (> 24 hours old).
 *   3. Fire recurring hosting invoices for deployed+hosted clients where
 *      it has been > 23 days since the last invoice (or deployed_date, if no
 *      invoice has fired yet). Lands ~7 days before the 30-day billing anniversary.
 *
 * Flags:
 *   --dry-run   Log what would happen; don't delete or email.
 *
 * Refuses to run via the web.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden — CLI only.\n";
    exit(1);
}

require __DIR__ . '/../signup/_helpers.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);
$now = time();
$stamp = date('Y-m-d H:i:s', $now);

$log = function(string $m) use ($stamp) {
    fwrite(STDOUT, "[{$stamp}] {$m}\n");
};

$log($dryRun ? '=== CRON DRY RUN ===' : '=== CRON START ===');

$cfg = tsc_cfg();

/* ── 1. Rate-limit cleanup ── */
$cleaned = 0; $kept = 0;
foreach (glob($cfg['paths']['ratelimits_dir'] . '/*.json') ?: [] as $f) {
    $data = json_decode((string)@file_get_contents($f), true);
    if (!is_array($data) || ($data['reset'] ?? 0) < $now) {
        if (!$dryRun) @unlink($f);
        $cleaned++;
    } else {
        $kept++;
    }
}
$log("Rate-limit files: cleaned={$cleaned} kept={$kept}");

/* ── 2. Stale zip cleanup ── */
$zipCleaned = 0;
foreach (glob(sys_get_temp_dir() . '/tsc-*.zip') ?: [] as $z) {
    if (filemtime($z) < $now - 86400) {
        if (!$dryRun) @unlink($z);
        $zipCleaned++;
    }
}
$log("Stale download zips cleaned: {$zipCleaned}");

/* ── 3. Recurring invoices ── */
$invoiced = 0; $skippedNotDue = 0; $skippedError = 0; $wouldInvoice = 0;
foreach (tsc_csv_read_all() as $r) {
    if (($r['status'] ?? '') !== 'deployed') continue;
    if (($r['plan']   ?? '') !== 'hosted')   continue;
    if (empty($r['deployed_date']))           continue;

    $deployedTs = strtotime($r['deployed_date']);
    if (!$deployedTs) continue;

    $lastInv = !empty($r['last_invoice_sent']) ? strtotime($r['last_invoice_sent']) : 0;
    $anchor  = max($lastInv, $deployedTs);
    $sinceAnchor = $now - $anchor;

    /* Fire when >= 23 days since last invoice (or deploy, if none yet) — lands ~7 days before the 30-day billing anniversary. */
    if ($sinceAnchor < 23 * 86400) {
        $skippedNotDue++;
        continue;
    }

    if ($dryRun) {
        $log("WOULD send invoice to {$r['reference']} ({$r['business_name']}) — last anchor " . date('Y-m-d', $anchor));
        $wouldInvoice++;
        continue;
    }

    $fullRec = tsc_load_record($r['reference']);
    if (!$fullRec) { $skippedError++; continue; }

    try {
        tsc_email_recurring_invoice($fullRec);
    } catch (Throwable $e) {
        $log("ERROR sending invoice to {$r['reference']}: " . $e->getMessage());
        $skippedError++;
        continue;
    }

    tsc_update_last_invoice_sent($r['reference'], date('Y-m-d H:i:s', $now));
    $log("Invoiced {$r['reference']} ({$r['business_name']})");
    $invoiced++;
}
if ($dryRun) {
    $log("Invoices: would_send={$wouldInvoice} skipped_not_due={$skippedNotDue}");
} else {
    $log("Invoices: sent={$invoiced} skipped_not_due={$skippedNotDue} errors={$skippedError}");
}

$log('=== CRON DONE ===');
