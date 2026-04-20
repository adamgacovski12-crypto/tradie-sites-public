<?php
/**
 * Blog post generator. CLI only.
 * Usage: php blog/generate.php "Topic title"
 *
 * Single JSON-object Groq call produces meta_title, meta_description,
 * body_markdown and 3 FAQs. Writes /blog/posts/YYYY-MM-DD-slug.md with
 * a one-line JSON frontmatter between --- fences.
 *
 * Requires GROQ_API_KEY in env (set via .htaccess SetEnv on prod, or
 * passed in the shell locally).
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden — CLI only.\n";
    exit(1);
}

$root = dirname(__DIR__);

if ($argc < 2 || trim((string)$argv[1]) === '') {
    echo "Usage: php blog/generate.php \"Topic title\"\n";
    exit(1);
}

$title = trim((string)$argv[1]);

/* ── Look the topic up in _topics.php, else fall back to a synthesised one ── */
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

function tradie_slugify(string $s): string {
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return $s === '' ? 'post' : $s;
}

/* ── Build trade-link index so the model can insert internal links ── */
$trades = require $root . '/trades/_trades.php';
$tradeLines = [];
foreach ($trades as $slug => $t) {
    $tradeLines[] = "- /trades/{$slug} — for {$t['plural']}";
}
$tradeList = implode("\n", $tradeLines);

/* ── System prompt ── */
$system = <<<PROMPT
You are the in-house SEO writer for Tradie Sites Co., an Australian service that builds 5-page websites for tradies in 24 hours (\$200 setup + \$80/month, no lock-in).

TASK: Write one blog post for the topic provided by the user.

WRITING RULES:
- 800 to 1,200 words in the body.
- Australian English and Australian context (suburbs, real trades, ABN/licence references, GST talk).
- Open with a hooky 2–3 sentence lead — no "In today's digital age…" AI slop.
- Use H2 and H3 markdown headings to break the post up. At least 3 H2 sections.
- Short paragraphs (2–4 sentences). Use lists where they help. Bold a key phrase or two.
- Natural keyword density for the target keyword — use it in the intro, one H2, and the conclusion.
- Sound like a plain-spoken Aussie tradie-services expert, not a content mill.
- Do NOT invent statistics or cite sources you can't verify.
- Include EXACTLY 2 or 3 internal links to /trades/[slug] pages where naturally relevant, using markdown link syntax like [plumbers](/trades/plumber). Pick links from the list below that fit the topic.
- End with a single CTA paragraph that points readers to contacting Tradie Sites Co. (link the words "book a site" to "/#contact").

AVAILABLE /trades/ LINKS:
{$tradeList}

META FIELDS:
- meta_title: ≤ 60 characters, compelling, includes the target keyword.
- meta_description: ≤ 160 characters, a plain-English summary with one CTA hook.
- faqs: exactly 3 Q&A pairs. Each question is natural (what a tradie would actually Google). Each answer is 1–3 sentences.

YOU MUST RESPOND WITH A SINGLE JSON OBJECT. No markdown outside JSON. Shape:

{
  "meta_title": "string",
  "meta_description": "string",
  "body_markdown": "string — the full post body, starting with the opening lead (no H1 — the template renders the H1 from the title)",
  "faqs": [
    {"q": "string", "a": "string"},
    {"q": "string", "a": "string"},
    {"q": "string", "a": "string"}
  ]
}
PROMPT;

$userMsg = "Topic title: {$topic['title']}\nTarget keyword: {$topic['target_keyword']}\nTopic tag: {$topic['topic_tag']}\n\nWrite the post now. Return JSON only.";

/* ── Call Groq (curl or stream fallback) ── */
$apiKey = getenv('GROQ_API_KEY');
if (!$apiKey) {
    fwrite(STDERR, "ERROR: GROQ_API_KEY is not set. Set it via .htaccess SetEnv on prod, or export it in your shell.\n");
    exit(1);
}

$payload = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => $system],
        ['role' => 'user',   'content' => $userMsg],
    ],
    'max_tokens' => 3500,
    'temperature' => 0.75,
    'response_format' => ['type' => 'json_object'],
];

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

if ($code < 200 || $code >= 300 || $raw === false) {
    fwrite(STDERR, "ERROR: Groq API call failed with HTTP {$code}.\n");
    exit(1);
}

$groq = json_decode($raw, true);
$content = $groq['choices'][0]['message']['content'] ?? '';
$parsed = json_decode($content, true);

if (!is_array($parsed) || empty($parsed['body_markdown']) || empty($parsed['meta_title'])) {
    fwrite(STDERR, "ERROR: Groq returned a response that didn't match the schema.\n");
    exit(1);
}

$metaTitle = trim((string)$parsed['meta_title']);
$metaDesc  = trim((string)$parsed['meta_description'] ?? '');
$bodyMd    = trim((string)$parsed['body_markdown']);
$faqs      = [];
foreach (($parsed['faqs'] ?? []) as $f) {
    $q = trim((string)($f['q'] ?? ''));
    $a = trim((string)($f['a'] ?? ''));
    if ($q !== '' && $a !== '') $faqs[] = ['q' => $q, 'a' => $a];
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
