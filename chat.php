<?php
/**
 * Tradie-Bot endpoint.
 * Uses Groq llama-3.3-70b-versatile in JSON-object mode so each reply
 * contains both the user-facing text and structured qualification state.
 */
header('Content-Type: application/json');
require __DIR__ . '/_leads_helper.php';

/* ── Rate limit: 30 req / IP / hour ── */
$rateLimitDir = sys_get_temp_dir() . '/tradie-chat-limits/';
if (!is_dir($rateLimitDir)) @mkdir($rateLimitDir, 0755, true);
$ipFile = $rateLimitDir . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '.json';
$rateData = file_exists($ipFile) ? json_decode(file_get_contents($ipFile), true) : ['count' => 0, 'reset' => time() + 3600];
if (time() > $rateData['reset']) $rateData = ['count' => 0, 'reset' => time() + 3600];
$rateData['count']++;
@file_put_contents($ipFile, json_encode($rateData));
if ($rateData['count'] > 30) {
    echo json_encode(['reply' => "You've hit the chat limit for the hour, mate. Use the contact form below and we'll ring you back."]);
    exit;
}

/* ── Request parsing ── */
$data = json_decode(file_get_contents('php://input'), true) ?: [];
$messages = is_array($data['messages'] ?? null) ? $data['messages'] : [];
$qualIn   = is_array($data['qualification'] ?? null) ? $data['qualification'] : [];

if (empty($messages)) {
    echo json_encode(['reply' => 'Send me a message to get started.']);
    exit;
}

/* ── Load trades for quote features + name→slug mapping ── */
$trades = require __DIR__ . '/trades/_trades.php';

$tradeIndexLines = [];
foreach ($trades as $slug => $t) {
    $firstFeature = $t['services'][0] ?? '';
    $tradeIndexLines[] = "- {$slug} ({$t['plural']}): {$firstFeature}";
}
$tradeIndex = implode("\n", $tradeIndexLines);

/* ── Known state summary for the model ── */
$known = [];
foreach (['name','trade_slug','suburb','has_website','goal','phone','callback_slot'] as $k) {
    $v = trim((string)($qualIn[$k] ?? ''));
    if ($v !== '') $known[] = "{$k}={$v}";
}
$knownStr = empty($known) ? '(nothing yet)' : implode('; ', $known);

/* ── Compute 3 callback slots over the next 2 business days ── */
$slots = tradie_next_slots();

function tradie_next_slots(): array {
    $tz  = new DateTimeZone('Australia/Sydney');
    $now = new DateTime('now', $tz);
    $days = [];
    $cursor = clone $now;
    while (count($days) < 2) {
        $cursor->modify('+1 day');
        $dow = (int)$cursor->format('w'); // 0 Sun .. 6 Sat
        if ($dow === 0 || $dow === 6) continue;
        $days[] = clone $cursor;
    }
    $todayStr    = $now->format('Y-m-d');
    $tomorrowStr = (clone $now)->modify('+1 day')->format('Y-m-d');
    $label = function(DateTime $d) use ($tomorrowStr) {
        return $d->format('Y-m-d') === $tomorrowStr ? 'Tomorrow' : $d->format('l');
    };
    return [
        $label($days[0]) . ' 10am',
        $label($days[0]) . ' 2pm',
        $label($days[1]) . ' 9am',
    ];
}

/* ── System prompt ── */
$system = <<<PROMPT
You are Tradie-Bot for Tradie Sites Co., an Australian service that builds 5-page websites for tradies in 24 hours. Two plans: Self-host (\$200 one-off, we build and hand over the files) and Hosted (\$200 setup + \$80/month, we host and look after it).

YOUR JOB: qualify the lead across a natural conversation (NOT a rigid form), then offer a callback.

QUALIFICATION FIELDS to collect (ask naturally, one topic per message, never all at once):
1. name
2. trade (must map to one of the 30 slugs below)
3. suburb / service area
4. has_website — "yes"/"no"; if yes, which platform
5. goal — one of: "calls" (more phone calls), "portfolio" (show off completed work), "google" (get found on Google)
6. After all five: offer a callback with 3 slots, capture phone number

TONE:
- Friendly Aussie, never cringey. Say "g'day" or "mate" at most ONCE per conversation.
- 2–3 sentences per message, MAX. Tradies are on phones on worksites.
- Confident on pricing — the numbers below are fixed, don't negotiate. If the tradie balks at \$80/month hosting, offer the Self-host \$200 one-off as the alternative, don't drop the price.
- Every message ends with exactly one forward-motion question (unless booking is confirmed).
- Don't invent services. Don't claim SEO guarantees.

WHAT YOU KNOW ABOUT PRICING (BOTH PLANS):
- Self-host: \$200 one-off. We build the 5-page site with professional copywriting, hand over the full source files, you host it wherever you like. No ongoing fee from us.
- Hosted: \$200 setup + \$80/month. Same 5-page build, plus fast Cloudflare hosting, SSL, uptime monitoring, and breakage fixes (stuff that breaks on its own). Stop paying the \$80 and the site comes offline — that's standard for any hosting, disclosed upfront.
- CONTENT CHANGES AFTER GO-LIVE are quoted separately on BOTH plans. The \$80/month does NOT include content edits, new pages, or new features. Don't promise edits. If a tradie asks "are edits included?" answer honestly: "No — we quote changes separately. The \$80 covers hosting + monitoring + breakage fixes only."
- Build time: live within 24 hours of payment landing.
- No lock-in on Hosted — cancel any time, site goes offline.
- Client owns their domain (roughly \$20–30/yr for a .com.au) on both plans.

30 TRADE SLUGS (slug — plural — one key feature):
{$tradeIndex}

CURRENT QUALIFICATION STATE: {$knownStr}
DO NOT re-ask fields that are already populated above. Use them; build on them.

AVAILABLE CALLBACK SLOTS (only offer these three, and only once you know name + trade + suburb + has_website + goal): {$slots[0]} / {$slots[1]} / {$slots[2]}

YOU MUST RESPOND WITH A SINGLE JSON OBJECT in this exact shape. No markdown, no prose outside JSON.

{
  "reply": "string — your 2-3 sentence user-facing reply, ends with a forward question unless the lead is fully booked",
  "name": "string — update only if user has just told you their name, else empty",
  "trade_slug": "string — one of the 30 slugs above if user mentioned a trade this turn, else empty",
  "suburb": "string — if user gave a suburb this turn, else empty",
  "has_website": "string — 'yes' or 'no' (optionally append platform, e.g. 'yes, wix'), else empty",
  "goal": "string — one of calls | portfolio | google, else empty",
  "phone": "string — if user gave a phone number this turn, else empty",
  "selected_slot": "string — one of the 3 slot strings above if user has just picked one, else empty",
  "show_quote": boolean — true only when you have trade_slug AND at least suburb, AND haven't shown the quote yet; use this to trigger the inline preview quote,
  "offer_slots": boolean — true only when all 5 qualification fields are filled AND no slot selected yet
}
PROMPT;

/* ── Build payload. Keep last 20 turns for context. ── */
$history = array_map(function($m) {
    $role = in_array(($m['role'] ?? ''), ['user','assistant'], true) ? $m['role'] : 'user';
    return ['role' => $role, 'content' => substr((string)($m['content'] ?? ''), 0, 1000)];
}, array_slice($messages, -20));

$payload = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => array_merge([['role' => 'system', 'content' => $system]], $history),
    'max_tokens' => 500,
    'temperature' => 0.65,
    'response_format' => ['type' => 'json_object'],
];

/* ── Call Groq (curl if available, stream context otherwise) ── */
$apiKey = getenv('GROQ_API_KEY');
$endpoint = 'https://api.groq.com/openai/v1/chat/completions';
$body = json_encode($payload);
$raw = false;
$code = 0;

if (function_exists('curl_init')) {
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT => 20,
    ]);
    $raw  = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer {$apiKey}\r\n",
            'content' => $body,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ]);
    $raw = @file_get_contents($endpoint, false, $ctx);
    if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
}

if ($code < 200 || $code >= 300 || $raw === false) {
    http_response_code(502);
    echo json_encode([
        'reply' => "Our AI's on smoko — the form just below the chat will ring you back within 24 hours.",
        'fallback' => true,
    ]);
    exit;
}

$groq = json_decode($raw, true);
$content = $groq['choices'][0]['message']['content'] ?? '';
$parsed = json_decode($content, true);

if (!is_array($parsed) || empty($parsed['reply'])) {
    echo json_encode([
        'reply' => "Sorry mate, lost the plot for a sec — can you say that again?",
    ]);
    exit;
}

/* ── Merge qualification ── */
$qualOut = [];
foreach (['name','trade_slug','suburb','has_website','goal','phone','callback_slot'] as $k) {
    $existing = trim((string)($qualIn[$k] ?? ''));
    if ($existing !== '') $qualOut[$k] = $existing;
}
foreach (['name','suburb','has_website','goal','phone'] as $k) {
    $v = trim((string)($parsed[$k] ?? ''));
    if ($v !== '') $qualOut[$k] = $v;
}
$newSlug = trim((string)($parsed['trade_slug'] ?? ''));
if ($newSlug !== '' && isset($trades[$newSlug])) {
    $qualOut['trade_slug'] = $newSlug;
}
$newSlot = trim((string)($parsed['selected_slot'] ?? ''));
if ($newSlot !== '' && in_array($newSlot, $slots, true)) {
    $qualOut['callback_slot'] = $newSlot;
}

$response = ['reply' => $parsed['reply'], 'qualification' => $qualOut];

/* ── Inline preview quote ── */
$showQuote = !empty($parsed['show_quote']) && isset($qualOut['trade_slug']) && isset($trades[$qualOut['trade_slug']]);
if ($showQuote) {
    $t = $trades[$qualOut['trade_slug']];
    $response['quote'] = [
        'trade' => $t['name'],
        'features' => array_slice($t['services'], 0, 5),
    ];
}

/* ── Slot offer ── */
if (!empty($parsed['offer_slots']) && empty($qualOut['callback_slot'])) {
    $response['slots'] = $slots;
}

/* ── Book the callback: requires slot + name + phone + trade ── */
if (!empty($qualOut['callback_slot'])
    && !empty($qualOut['name'])
    && !empty($qualOut['phone'])
    && !empty($qualOut['trade_slug'])
    && empty($qualIn['booked'])) {

    $tradeName = $trades[$qualOut['trade_slug']]['name'] ?? $qualOut['trade_slug'];
    $snippet = [];
    foreach (array_slice($messages, -5) as $m) {
        $role = ($m['role'] ?? 'user') === 'assistant' ? 'Bot' : 'User';
        $snippet[] = $role . ': ' . substr((string)($m['content'] ?? ''), 0, 300);
    }

    tradie_write_and_email_lead([
        'name'                 => $qualOut['name'],
        'phone'                => $qualOut['phone'],
        'trade'                => $tradeName,
        'suburb'               => $qualOut['suburb']        ?? '',
        'has_website'          => $qualOut['has_website']   ?? '',
        'goal'                 => $qualOut['goal']          ?? '',
        'callback_slot'        => $qualOut['callback_slot'],
        'source'               => 'chatbot',
        'conversation_snippet' => implode("\n", $snippet),
    ]);

    $qualOut['booked'] = '1';
    $response['qualification'] = $qualOut;
    $response['booked'] = true;
}

echo json_encode($response);
