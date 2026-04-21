<?php
/**
 * Tradie Sites Co. — health endpoint for ops monitoring.
 *
 * Returns a JSON snapshot of system state. Protected by the parent .htaccess
 * Basic Auth (admin user only). Designed to be polled by an external uptime
 * service (UptimeRobot, BetterStack, etc.) or hit manually.
 *
 * Returns HTTP 200 if all checks pass, 503 if any critical check fails.
 */
require __DIR__ . '/../signup/_helpers.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

$cfg = tsc_cfg();
$now = time();
$problems = [];

/* PHP version */
$phpVersion = PHP_VERSION;

/* Disk space on the data partition */
$dataDir = $cfg['paths']['signups_dir'];
$freeBytes = is_dir($dataDir) ? @disk_free_space($dataDir) : null;
$freeMB = $freeBytes !== null && $freeBytes !== false ? (int)($freeBytes / 1024 / 1024) : null;
if ($freeMB !== null && $freeMB < 200) {
    $problems[] = "low disk space: {$freeMB} MB free";
}

/* Required env vars */
$envCheck = [
    'GROQ_API_KEY' => (string)getenv('GROQ_API_KEY') !== '',
    'SMTP_HOST'    => (string)getenv('SMTP_HOST') !== '',
];
foreach ($envCheck as $k => $present) {
    if (!$present) $problems[] = "env var not set: {$k}";
}

/* Signup data summary */
$signups = file_exists($cfg['paths']['csv_file']) ? tsc_csv_read_all() : [];
$counts = ['total' => count($signups), 'awaiting_payment' => 0, 'paid' => 0, 'prepped' => 0, 'deployed' => 0, 'cancelled' => 0, 'refunded' => 0];
foreach ($signups as $r) {
    $s = $r['status'] ?? 'unknown';
    if (isset($counts[$s])) $counts[$s]++;
}

/* Cron freshness — last cron.log line within 36 hours? */
$cronLog = $cfg['paths']['signups_dir'] . '/cron.log';
$cronLastRunAgoHours = null;
if (file_exists($cronLog)) {
    $age = $now - filemtime($cronLog);
    $cronLastRunAgoHours = (int)($age / 3600);
    if ($cronLastRunAgoHours > 36) {
        $problems[] = "cron hasn't run in {$cronLastRunAgoHours}h (expected daily)";
    }
}

/* Backup freshness — last backup within 36 hours? */
$backupsDir = $cfg['paths']['signups_dir'] . '/backups';
$backupAgeHours = null;
if (is_dir($backupsDir)) {
    $latest = 0;
    foreach (glob($backupsDir . '/*.tar.gz') ?: [] as $b) {
        $latest = max($latest, filemtime($b));
    }
    if ($latest > 0) {
        $backupAgeHours = (int)(($now - $latest) / 3600);
        if ($backupAgeHours > 36) {
            $problems[] = "last backup is {$backupAgeHours}h old (expected daily)";
        }
    } else {
        $problems[] = 'no backups exist yet';
    }
}

/* Mail log tail — last 5 failures, if any. Helps spot deliverability issues. */
$mailLog = $cfg['paths']['signups_dir'] . '/mail.log';
$recentMailFailures = 0;
if (file_exists($mailLog)) {
    $lines = @file($mailLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach (array_slice($lines, -50) as $line) {
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $m)) {
            $ts = strtotime($m[1]);
            if ($ts && ($now - $ts) < 86400) $recentMailFailures++;
        }
    }
    if ($recentMailFailures >= 5) {
        $problems[] = "{$recentMailFailures} mail failures in the last 24h — check signups/mail.log";
    }
}

$status = empty($problems) ? 'ok' : 'degraded';
http_response_code(empty($problems) ? 200 : 503);

echo json_encode([
    'status'     => $status,
    'checked_at' => date('c', $now),
    'php_version'=> $phpVersion,
    'disk_free_mb' => $freeMB,
    'env'        => $envCheck,
    'signups'    => $counts,
    'cron_last_run_hours_ago'    => $cronLastRunAgoHours,
    'backup_age_hours'           => $backupAgeHours,
    'mail_failures_last_24h'     => $recentMailFailures,
    'problems'   => $problems,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
