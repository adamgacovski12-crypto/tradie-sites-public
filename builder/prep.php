<?php
/**
 * Tradie Sites Co. — Build preparation CLI.
 *
 * Usage:  php builder/prep.php TSC-MIKESP-A3K9
 *
 * Prepares /builds/{REF}/ so Adam can open a fresh Claude Code session in that
 * folder and paste CLAUDE_BUILD_PROMPT.md to generate the final 5-page site.
 *
 * Does NOT call Claude. Does NOT call Groq. Pure file plumbing.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden — CLI only.\n";
    exit(1);
}

$root     = dirname(__DIR__);
$tplDir   = __DIR__ . '/templates';
$buildsRoot = $root . '/builds';

require $root . '/signup/_helpers.php';

$ref = isset($argv[1]) ? trim((string)$argv[1]) : '';
if ($ref === '' || !preg_match('/^TSC-[A-Z0-9-]+$/i', $ref)) {
    fwrite(STDERR, "Usage: php builder/prep.php TSC-REF-XXXX\n");
    exit(1);
}
$ref = strtoupper($ref);

$cfg     = tsc_cfg();
$recPath = $cfg['paths']['records_dir'] . '/' . $ref . '.json';
if (!file_exists($recPath)) {
    fwrite(STDERR, "ERROR: signup record not found at {$recPath}\n");
    exit(1);
}
$record = json_decode((string)file_get_contents($recPath), true);
if (!is_array($record)) {
    fwrite(STDERR, "ERROR: signup record is not valid JSON\n");
    exit(1);
}

/* ── Build folder ── */
$buildDir = $buildsRoot . '/' . $ref;
if (is_dir($buildDir)) {
    fwrite(STDERR, "WARNING: {$buildDir} already exists. Existing files may be overwritten.\n");
} else {
    if (!@mkdir($buildDir, 0755, true)) {
        fwrite(STDERR, "ERROR: could not mkdir {$buildDir}\n");
        exit(1);
    }
}
@mkdir($buildDir . '/photos', 0755, true);

/* ── build.log helper ── */
$logPath = $buildDir . '/build.log';
$logFp = fopen($logPath, 'a');
$log = function(string $msg) use ($logFp) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    fwrite($logFp, $line);
    echo $msg . "\n";
};

$log("PREP started for {$ref}");

/* ── 1. Sanitise and copy signup.json (drop phone/email/IP to reduce leak surface) ── */
$sanitised = [
    'reference'        => $record['reference']        ?? $ref,
    'date'             => $record['date']             ?? '',
    'plan'             => $record['plan']             ?? '',
    'business_name'    => $record['business_name']    ?? '',
    'contact_name'     => $record['contact_name']     ?? '',
    'abn'              => $record['abn']              ?? '',
    'trade'            => $record['trade']            ?? '',
    'trade_slug'       => $record['trade_slug']       ?? '',
    'suburbs'          => $record['suburbs']          ?? '',
    'licence'          => $record['licence']          ?? '',
    'tagline'          => $record['tagline']          ?? '',
    'services'         => $record['services']         ?? '',
    'years'            => $record['years']            ?? '',
    'existing_website' => $record['existing_website'] ?? '',
    'existing_fb'      => $record['existing_fb']      ?? '',
    'logo_path'        => $record['logo_path']        ?? '',
    'photo_paths'      => $record['photo_paths']      ?? '',
];
file_put_contents(
    $buildDir . '/signup.json',
    json_encode($sanitised, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);
$log("Wrote signup.json (sanitised — phone/email/IP stripped)");

/* ── 2. Copy photos ── */
$srcAssets = $cfg['paths']['assets_dir'] . '/' . $ref;
$photoCount = 0;
if (is_dir($srcAssets . '/photos')) {
    foreach (glob($srcAssets . '/photos/*') ?: [] as $p) {
        if (!is_file($p)) continue;
        $dest = $buildDir . '/photos/' . basename($p);
        if (@copy($p, $dest)) {
            $photoCount++;
            $log("Copied photo: " . basename($p));
        }
    }
}
$log("Photos copied: {$photoCount}");

/* ── 3. Copy logo if present ── */
$logoExt = '';
$hasLogo = 'no';
if (!empty($record['logo_path'])) {
    $srcLogo = $root . '/signups/' . $record['logo_path'];
    if (file_exists($srcLogo)) {
        $logoExt = strtolower(pathinfo($srcLogo, PATHINFO_EXTENSION));
        $destLogo = $buildDir . '/logo.' . $logoExt;
        if (@copy($srcLogo, $destLogo)) {
            $hasLogo = 'yes';
            $log("Copied logo: logo.{$logoExt}");
        }
    }
}

/* ── 4. Pick accent colour + tone hint from config.json ── */
$tplConfig = json_decode((string)@file_get_contents($tplDir . '/config.json'), true) ?: [];
$tradeSlug = $sanitised['trade_slug'] !== '' ? $sanitised['trade_slug'] : 'general-tradie';
$tradeEntry = $tplConfig['trades'][$tradeSlug] ?? null;
$accent    = $tradeEntry['accent']    ?? ($tplConfig['default_accent'] ?? '#FF6A00');
$toneHint  = $tradeEntry['tone_hint'] ?? ($tplConfig['default_tone']   ?? '');
$formEndpoint = $tplConfig['default_form_endpoint'] ?? 'https://formspree.io/f/REPLACE_ME';
$log("Trade slug: {$tradeSlug} → accent {$accent}");

/* ── 5. Prepare replacement map ── */
$suburbsRaw   = trim((string)$sanitised['suburbs']);
$suburbsList  = $suburbsRaw !== '' ? $suburbsRaw : ($sanitised['trade'] . ' service area');
$suburbsArr   = array_values(array_filter(array_map('trim', preg_split('/[,\n;]+/', $suburbsRaw))));
if (empty($suburbsArr)) $suburbsArr = [''];
$suburbPrimary = $suburbsArr[0] !== '' ? $suburbsArr[0] : 'Australia';
$suburbsJson  = json_encode($suburbsArr, JSON_UNESCAPED_UNICODE);

$services = array_values(array_filter(array_map('trim', explode('|', (string)$sanitised['services']))));
$service1 = $services[0] ?? '[[SERVICE_1]]';
$service2 = $services[1] ?? '[[SERVICE_2]]';
$service3 = $services[2] ?? '[[SERVICE_3]]';

$years = $sanitised['years'] !== '' ? (string)$sanitised['years'] : '—';
$logoUrl = $hasLogo === 'yes' ? './logo.' . $logoExt : '';

$map = [
    '{{REF}}'               => $ref,
    '{{BUSINESS_NAME}}'     => $sanitised['business_name'] ?: 'the business',
    '{{TRADE}}'             => $sanitised['trade']         ?: 'Tradie',
    '{{TRADE_SLUG}}'        => $tradeSlug,
    '{{TAGLINE}}'           => $sanitised['tagline']       ?: '',
    '{{PHONE}}'             => '{{PHONE}}',        /* intentionally left for Adam */
    '{{EMAIL}}'             => '{{EMAIL}}',        /* intentionally left for Adam */
    '{{ABN}}'               => $sanitised['abn']           ?: '',
    '{{LICENCE}}'           => $sanitised['licence']       ?: '—',
    '{{SUBURBS_LIST}}'      => $suburbsList,
    '{{SUBURB_PRIMARY}}'    => $suburbPrimary,
    '{{SUBURBS_JSON_ARRAY}}'=> $suburbsJson,
    '{{STATE}}'             => '{{STATE}}',        /* Adam may need to fill */
    '{{YEARS_IN_BUSINESS}}' => $years,
    '{{SERVICE_1}}'         => $service1,
    '{{SERVICE_2}}'         => $service2,
    '{{SERVICE_3}}'         => $service3,
    '{{LOGO_URL}}'          => $logoUrl,
    '{{LOGO_EXT}}'          => $logoExt !== '' ? $logoExt : 'png',
    '{{HAS_LOGO}}'          => $hasLogo,
    '{{PHOTO_COUNT}}'       => (string)$photoCount,
    '{{ACCENT_COLOUR}}'     => $accent,
    '{{FORM_ENDPOINT}}'     => $formEndpoint,
    '{{CURRENT_YEAR}}'      => date('Y'),
    '{{TONE_HINT}}'         => $toneHint,
];

$fill = function(string $text) use ($map): string {
    return str_replace(array_keys($map), array_values($map), $text);
};

/* ── 6. Copy base.html + base.css into build dir with data placeholders filled ── */
$baseHtml = file_get_contents($tplDir . '/base.html');
$baseCss  = file_get_contents($tplDir . '/base.css');
if ($baseHtml === false || $baseCss === false) {
    fwrite(STDERR, "ERROR: could not read template files\n");
    exit(1);
}
file_put_contents($buildDir . '/base.html', $fill($baseHtml));
file_put_contents($buildDir . '/base.css',  $fill($baseCss));
$log("Wrote base.html + base.css (data placeholders filled; copy placeholders [[...]] remain)");

/* ── 7. Generate CLAUDE_BUILD_PROMPT.md from the template ── */
$promptTpl = file_get_contents(__DIR__ . '/CLAUDE_PROMPT.md');
if ($promptTpl === false) {
    fwrite(STDERR, "ERROR: could not read CLAUDE_PROMPT.md\n");
    exit(1);
}
file_put_contents($buildDir . '/CLAUDE_BUILD_PROMPT.md', $fill($promptTpl));
$log("Wrote CLAUDE_BUILD_PROMPT.md");

/* ── 8. Update signup record status → prepped ── */
tsc_update_status($ref, 'prepped');
$log("Signup status updated → prepped");

fclose($logFp);

echo "\n";
echo "───────────────────────────────────────────────\n";
echo "PREP COMPLETE for {$ref}\n";
echo "───────────────────────────────────────────────\n";
echo "Build folder: {$buildDir}\n";
echo "Photos:       {$photoCount}\n";
echo "Accent:       {$accent}\n";
echo "\n";
echo "NEXT STEPS FOR ADAM:\n";
echo "1. Open Claude Code: cd {$buildDir}\n";
echo "2. Start fresh session, paste the contents of:\n";
echo "   {$buildDir}/CLAUDE_BUILD_PROMPT.md\n";
echo "3. Wait 5-10 min for Claude Code to build the 5 pages.\n";
echo "4. Spot-check, then back to /admin-leads/ → DOWNLOAD ZIP → Cloudflare Pages.\n";
