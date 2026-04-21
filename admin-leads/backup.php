<?php
/**
 * Tradie Sites Co. — daily backup of all client signup data.
 *
 * Tarballs /signups/records/, /signups/assets/, and /signups/signups.csv
 * into /signups/backups/YYYY-MM-DD.tar.gz. Keeps the last 30 days; deletes older.
 *
 * Privacy Act 1988 compliance — losing personal information you collected is
 * a notifiable data breach. Run this daily via cron, AND offsite-rsync the
 * /signups/backups/ folder to a second location (S3, another server, your laptop)
 * separately — this script alone doesn't protect against full-server loss.
 *
 * Crontab:
 *   30 3 * * * /usr/bin/php /var/www/site.tradiebud.tech/admin-leads/backup.php >> /var/www/site.tradiebud.tech/signups/backup.log 2>&1
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
$dateTag = date('Y-m-d', $now);

$log = function(string $m) use ($stamp) {
    fwrite(STDOUT, "[{$stamp}] {$m}\n");
};

$log($dryRun ? '=== BACKUP DRY RUN ===' : '=== BACKUP START ===');

$signupsDir = $cfg['paths']['signups_dir'];
$backupsDir = $signupsDir . '/backups';
if (!is_dir($backupsDir)) {
    if (!$dryRun) @mkdir($backupsDir, 0755, true);
}

/* Build list of paths to back up — only what exists. */
$paths = [];
if (file_exists($cfg['paths']['csv_file']))    $paths[] = 'signups.csv';
if (is_dir($cfg['paths']['records_dir']))      $paths[] = 'records';
if (is_dir($cfg['paths']['assets_dir']))       $paths[] = 'assets';
/* Skip ratelimits/ (auto-cleaned), backups/ (this dir), and *.log (regenerable). */

if (empty($paths)) {
    $log('No data to back up — exiting.');
    exit(0);
}

$archive = $backupsDir . '/' . $dateTag . '.tar.gz';

if ($dryRun) {
    $log("Would write: {$archive}");
    $log('Would include: ' . implode(', ', $paths));
} else {
    /* Prefer system tar (smaller archives, handles symlinks, faster).
     * Fallback to PharData if tar isn't on the host.
     */
    $tarBin = trim((string)@shell_exec('which tar 2>/dev/null'));
    if ($tarBin !== '' && is_executable($tarBin)) {
        $cmd = sprintf(
            '%s -czf %s -C %s %s 2>&1',
            escapeshellarg($tarBin),
            escapeshellarg($archive),
            escapeshellarg($signupsDir),
            implode(' ', array_map('escapeshellarg', $paths))
        );
        $output = []; $code = 0;
        exec($cmd, $output, $code);
        if ($code !== 0) {
            $log('tar failed: ' . implode(' | ', $output));
            exit(1);
        }
        $log("Wrote tar.gz: {$archive} (" . number_format(filesize($archive)) . ' bytes)');
    } else {
        /* PharData fallback — produces .tar.gz without external tar binary. */
        $tmp = $backupsDir . '/' . $dateTag . '.tar';
        if (file_exists($tmp))     @unlink($tmp);
        if (file_exists($tmp . '.gz')) @unlink($tmp . '.gz');
        try {
            $phar = new PharData($tmp);
            foreach ($paths as $p) {
                $full = $signupsDir . '/' . $p;
                if (is_dir($full))       $phar->buildFromDirectory($full);
                else if (is_file($full)) $phar->addFile($full, $p);
            }
            $phar->compress(Phar::GZ);
            unset($phar);
            @unlink($tmp);
            $log("Wrote PharData tar.gz: {$archive} (" . number_format(filesize($archive)) . ' bytes)');
        } catch (Throwable $e) {
            $log('PharData failed: ' . $e->getMessage());
            exit(1);
        }
    }
}

/* ── Rotate: keep last 30 days ── */
$retentionDays = 30;
$cutoff = $now - ($retentionDays * 86400);
$pruned = 0;
foreach (glob($backupsDir . '/*.tar.gz') ?: [] as $old) {
    if (filemtime($old) < $cutoff) {
        if (!$dryRun) @unlink($old);
        $pruned++;
    }
}
$log("Pruned old backups (>{$retentionDays} days): {$pruned}");

$log('=== BACKUP DONE ===');
