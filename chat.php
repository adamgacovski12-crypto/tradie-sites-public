<?php
header('Content-Type: application/json');

// Rate limiting: max 30 requests per IP per hour
$rateLimitDir = sys_get_temp_dir() . '/tradie-chat-limits/';
if (!is_dir($rateLimitDir)) mkdir($rateLimitDir, 0755, true);
$ipFile = $rateLimitDir . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '.json';

$rateData = file_exists($ipFile) ? json_decode(file_get_contents($ipFile), true) : ['count' => 0, 'reset' => time() + 3600];
if (time() > $rateData['reset']) {
    $rateData = ['count' => 0, 'reset' => time() + 3600];
}
$rateData['count']++;
file_put_contents($ipFile, json_encode($rateData));

if ($rateData['count'] > 30) {
    echo json_encode(['reply' => "You've reached the chat limit. Please use the contact form below or call us directly."]);
    exit;
}

// Get API key from environment or .htaccess SetEnv
$GROQ_API_KEY = getenv('GROQ_API_KEY');

$data = json_decode(file_get_contents('php://input'), true);
$messages = $data['messages'] ?? [];

// Validate messages
if (!is_array($messages) || empty($messages)) {
    echo json_encode(['reply' => 'Please send a message to get started.']);
    exit;
}

// System prompt (do not include in user-visible messages)
$system = "You are the friendly AI assistant for Tradie Sites Co., an Australian service that builds professional websites for tradies.

WHAT YOU KNOW:
- Setup fee: \$200 one-time (includes: 5-page website, custom domain setup, copywriting, live in 24 hours)
- Monthly fee: \$80/month (includes: hosting, 2 edits/month, gallery updates, email support, 30-day cancel)
- Trades covered: 30+ including Plumber, Electrician, Builder, Painter, Concreter, Tiler, Landscaper, Fencer, Roofer, Air Conditioning, Carpenter, Pest Control, Cleaner, Handyman, Glazier, Plasterer, Flooring, Solar Installer, Locksmith, Pool Builder, Demolition, Arborist, Pressure Washing, Irrigation, Gas Fitter, Concrete Cutter, Scaffolding, Waterproofing, Renderer, General Tradie
- Sites are mobile-responsive, SEO-ready, include contact forms, photo gallery, and are deployed on fast Cloudflare hosting
- No lock-in contracts — cancel with 30 days notice
- Client owns their domain, Tradie Sites Co. retains code templates
- No SEO ranking guarantees
- Extra edits beyond 2/month: \$50/hr

YOUR JOB:
- Answer questions about the service clearly and confidently
- Be friendly, straight-talking, and Australian in tone
- When someone seems ready to proceed or asks how to get started, ask for: their name, their trade, their phone number
- Once you have name + trade + phone, say: 'Perfect [name]! I've got your details — someone from our team will call you within 24 hours to get you sorted. Is there anything else I can help with?'
- If asked anything outside of Tradie Sites Co. services, politely redirect
- Keep responses concise — 2-4 sentences max unless detailed answer needed
- Do NOT make up prices or services not listed above";

$payload = [
    'model' => 'llama-3.1-70b-versatile',
    'messages' => array_merge(
        [['role' => 'system', 'content' => $system]],
        array_map(function($m) {
            return ['role' => $m['role'] ?? 'user', 'content' => substr($m['content'] ?? '', 0, 1000)];
        }, array_slice($messages, -20)) // Limit context window
    ),
    'max_tokens' => 300,
    'temperature' => 0.7
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $GROQ_API_KEY
    ],
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['reply' => "Sorry, I'm having trouble connecting right now. Please use the contact form below and we'll get back to you soon."]);
    exit;
}

$result = json_decode($response, true);
$reply = $result['choices'][0]['message']['content'] ?? 'Sorry, something went wrong. Please try again.';

echo json_encode(['reply' => $reply]);
