<?php
/**
 * Blog post generator. CLI only.
 * Usage: php blog/generate.php "Topic title"
 *
 * Uses TWO Groq calls because llama-3.3-70b in json_object mode can't
 * reliably quote-escape 700+ words of markdown inside a string field:
 *   1. Metadata call — JSON mode, small. Returns meta_title,
 *      meta_description, topic_tag, 3 FAQs.
 *   2. Body call — plain text mode, focused prompt. Returns raw
 *      markdown only.
 *
 * The defensive whitespace normaliser in template.php still catches any
 * leading-indent regression at render time.
 *
 * Requires GROQ_API_KEY in env (set via .htaccess SetEnv on prod, or
 * exported in the shell locally).
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden — CLI only.\n";
    exit(1);
}

require __DIR__ . '/_markdown.php';

$root = dirname(__DIR__);

if ($argc < 2 || trim((string)$argv[1]) === '') {
    echo "Usage: php blog/generate.php \"Topic title\"\n";
    exit(1);
}

$title = trim((string)$argv[1]);

function tradie_slugify(string $s): string {
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return $s === '' ? 'post' : $s;
}

/* ── Look the topic up in _topics.php, else synthesise ── */
$topics = require __DIR__ . '/_topics.php';
$topic = null;
foreach ($topics as $t) {
    if (strcasecmp($t['title'], $title) === 0) { $topic = $t; break; }
}
if ($topic === null) {
    $topic = [
        'title' => $title,
        'slug'  => tradie_slugify($title),
        'target_keyword' => strtolower($title),
        'topic_tag' => 'general',
    ];
}

/* ── Trade links index for the body call ── */
$trades = require $root . '/trades/_trades.php';
$tradeLines = [];
foreach ($trades as $slug => $t) {
    $tradeLines[] = "- /trades/{$slug} — for {$t['plural']}";
}
$tradeList = implode("\n", $tradeLines);

/* ── Shared HTTP helper ── */
$apiKey = getenv('GROQ_API_KEY');
if (!$apiKey) {
    fwrite(STDERR, "ERROR: GROQ_API_KEY is not set. Set it via .htaccess SetEnv on prod, or export it in your shell.\n");
    exit(1);
}

function tradie_call_groq(array $payload, string $apiKey): array {
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
            CURLOPT_TIMEOUT => 90,
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
                'timeout' => 90,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($endpoint, false, $ctx);
        if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
            $code = (int)$m[1];
        }
    }

    $ok = ($code >= 200 && $code < 300 && $raw !== false);
    $content = '';
    if ($ok) {
        $decoded = json_decode((string)$raw, true);
        $content = (string)($decoded['choices'][0]['message']['content'] ?? '');
    }
    return ['ok' => $ok, 'code' => $code, 'raw' => (string)$raw, 'content' => $content];
}

/* ──────────────────────────────────────────────────────────────
   CALL 1 — metadata (JSON mode, small, deterministic)
   ────────────────────────────────────────────────────────────── */
$metaSystem = <<<PROMPT
You generate blog post metadata for Tradie Sites Co., an Australian service that builds tradie websites in 24 hours for \$200 setup + \$80/month.

Return a SINGLE JSON OBJECT with exactly these fields:
- "meta_title": string, ≤ 60 characters, compelling, includes the topic's target keyword.
- "meta_description": string, ≤ 160 characters, plain-English summary with one CTA hook.
- "faqs": array of exactly 3 objects, each {"q": string, "a": string}. Natural questions a tradie would Google. Answers are 1–3 sentences.

Australian English. No hype. No "In today's digital age" slop. Output JSON only.
PROMPT;

$metaUser = "Topic title: {$topic['title']}\nTarget keyword: {$topic['target_keyword']}\nTopic tag: {$topic['topic_tag']}";

$metaResult = tradie_call_groq([
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => [
        ['role' => 'system', 'content' => $metaSystem],
        ['role' => 'user',   'content' => $metaUser],
    ],
    'max_tokens'      => 400,
    'temperature'     => 0.3,
    'response_format' => ['type' => 'json_object'],
], $apiKey);

if (!$metaResult['ok']) {
    fwrite(STDERR, "ERROR: metadata call failed — HTTP {$metaResult['code']}\nRaw response:\n{$metaResult['raw']}\n");
    exit(1);
}

$meta = json_decode($metaResult['content'], true);
if (!is_array($meta) || empty($meta['meta_title'])) {
    fwrite(STDERR, "ERROR: metadata call returned invalid JSON.\nContent:\n{$metaResult['content']}\n");
    exit(1);
}

$metaTitle = trim((string)$meta['meta_title']);
$metaDesc  = trim((string)($meta['meta_description'] ?? ''));
$faqs      = [];
foreach (($meta['faqs'] ?? []) as $f) {
    $q = trim((string)($f['q'] ?? ''));
    $a = trim((string)($f['a'] ?? ''));
    if ($q !== '' && $a !== '') $faqs[] = ['q' => $q, 'a' => $a];
}

/* ──────────────────────────────────────────────────────────────
   CALL 2 — body markdown (plain text mode, focused prompt)
   ────────────────────────────────────────────────────────────── */
$bodySystem = <<<PROMPT
Write a blog post in markdown for Tradie Sites Co., an Australian tradie website builder (\$200 setup + \$80/month, 24-hour turnaround, no lock-in).

Rules:
- 800 to 1,200 words.
- Australian English and Australian context. Plain-spoken, no AI slop.
- Open with a 2–3 sentence hook. No "In today's digital age".
- Use ## for H2 headings. At least 3 H2 sections. Short paragraphs, 2–4 sentences.
- Use - for bullet lists, **bold** for emphasis. No H1 — the template adds it.
- Include EXACTLY 2 or 3 internal links to /trades/[slug] pages, in markdown [text](/trades/slug) form. Pick slugs from the list below that fit the topic.
- End with a CTA paragraph linking the words "book a site" to "/#contact".
- NO leading whitespace on any line. Every line flush-left at column 0. Never prefix 4+ spaces.
- Output ONLY the markdown body. No JSON, no code fences, no preamble, no wrapping.

Available /trades/ slugs:
{$tradeList}
PROMPT;

$bodyUser = "Topic: {$topic['title']}\nTarget keyword: {$topic['target_keyword']}\n\nWrite the post now.";

$bodyResult = tradie_call_groq([
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => [
        ['role' => 'system', 'content' => $bodySystem],
        ['role' => 'user',   'content' => $bodyUser],
    ],
    'max_tokens'  => 2000,
    'temperature' => 0.7,
], $apiKey);

if (!$bodyResult['ok']) {
    fwrite(STDERR, "ERROR: body call failed — HTTP {$bodyResult['code']}\nRaw response:\n{$bodyResult['raw']}\n");
    exit(1);
}

$bodyMd = tradie_normalise_markdown(trim($bodyResult['content']));
if ($bodyMd === '') {
    fwrite(STDERR, "ERROR: body call returned empty content.\nContent:\n{$bodyResult['content']}\n");
    exit(1);
}

/* ── Write /blog/posts/YYYY-MM-DD-slug.md ── */
$date = date('Y-m-d');
$postsDir = __DIR__ . '/posts';
if (!is_dir($postsDir)) mkdir($postsDir, 0755, true);

$filename = "{$date}-{$topic['slug']}.md";
$path = "{$postsDir}/{$filename}";

$frontmatter = [
    'title'       => $topic['title'],
    'meta_title'  => $metaTitle,
    'description' => $metaDesc,
    'date'        => $date,
    'slug'        => $topic['slug'],
    'topic_tag'   => $topic['topic_tag'],
    'faqs'        => $faqs,
];

$fileContent  = "---\n";
$fileContent .= json_encode($frontmatter, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
$fileContent .= "---\n\n";
$fileContent .= $bodyMd . "\n";

if (file_put_contents($path, $fileContent) === false) {
    fwrite(STDERR, "ERROR: Could not write {$path}.\n");
    exit(1);
}

echo "Done — preview at /blog/{$topic['slug']}\n";
echo "Wrote {$path} (" . strlen($fileContent) . " bytes)\n";
