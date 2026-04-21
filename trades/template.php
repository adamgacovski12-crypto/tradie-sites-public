<?php
$trades = require __DIR__ . '/_trades.php';
$slug = $_GET['slug'] ?? ($tradeSlug ?? '');
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug));

if ($slug === '' || !isset($trades[$slug])) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo "<!doctype html><meta charset=utf-8><title>Trade not found</title>";
    echo "<h1>Trade not found</h1><p><a href=\"/trades/\">See all trades</a></p>";
    exit;
}

$t = $trades[$slug];
$name   = $t['name'];
$plural = $t['plural'];
$meta   = $t['meta_desc'];
$intro  = $t['intro'];
$services = $t['services'];
$faqs   = $t['faqs'];

$pageTitle = "{$name} Website Builder Australia — \$200 Setup, \$80/month | Tradie Sites Co.";
$pageUrl   = "https://site.tradiebud.tech/trades/{$slug}";
$h1        = "Websites for {$plural} — Built in 24 Hours Across Australia";

$faqsJson = [];
foreach ($faqs as $f) {
    $faqsJson[] = [
        '@type' => 'Question',
        'name'  => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ];
}

$serviceJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'Service',
    'name'     => "Website design for {$plural} in Australia",
    'url'      => $pageUrl,
    'image'    => 'https://site.tradiebud.tech/og-image.jpg',
    'description' => $meta,
    'serviceType' => "{$name} website design and development",
    'areaServed'  => ['@type' => 'Country', 'name' => 'Australia'],
    'provider' => ['@type' => 'Organization', 'name' => 'Tradie Sites Co.', 'url' => 'https://site.tradiebud.tech/'],
    'offers' => [
        ['@type' => 'Offer', 'name' => 'Setup',   'price' => '200', 'priceCurrency' => 'AUD'],
        ['@type' => 'Offer', 'name' => 'Monthly', 'price' => '80',  'priceCurrency' => 'AUD'],
    ],
];
$faqJsonLd = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqsJson];
$breadcrumbJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',   'item' => 'https://site.tradiebud.tech/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Trades', 'item' => 'https://site.tradiebud.tech/trades/'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $name,    'item' => $pageUrl],
    ],
];

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function jld($a) { return json_encode($a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); }
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="<?= h($meta) ?>">
    <link rel="canonical" href="<?= h($pageUrl) ?>">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Tradie Sites Co.">
    <meta property="og:title" content="<?= h("{$name} Websites — Built in 24 Hours") ?>">
    <meta property="og:description" content="<?= h($meta) ?>">
    <meta property="og:url" content="<?= h($pageUrl) ?>">
    <meta property="og:image" content="https://site.tradiebud.tech/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_AU">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= h("{$name} Websites — Built in 24 Hours") ?>">
    <meta name="twitter:description" content="<?= h($meta) ?>">
    <meta name="twitter:image" content="https://site.tradiebud.tech/og-image.jpg">

    <script type="application/ld+json"><?= jld($serviceJsonLd) ?></script>
    <script type="application/ld+json"><?= jld($faqJsonLd) ?></script>
    <script type="application/ld+json"><?= jld($breadcrumbJsonLd) ?></script>

    <link rel="preload" href="/assets/fonts/barlow-condensed-800.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/fonts/fonts.css">
    <link rel="stylesheet" href="/assets/site.css">
</head>
<body>

<nav class="nav">
    <div class="container">
        <a href="/" class="nav-logo" aria-label="Tradie Sites Co. home"><span class="mark">T</span> Tradie Sites Co.</a>
        <button class="hamburger" aria-label="Menu" id="hamburger"><span></span><span></span><span></span></button>
        <div class="nav-links" id="navLinks">
            <a href="/#how-it-works">How</a>
            <a href="/#pricing">Pricing</a>
            <a href="/trades/">Trades</a>
            <a href="/gallery">Gallery</a>
            <a href="/blog/">Blog</a>
            <a href="/#chat">Chat</a>
            <a href="/about">About</a>
            <a href="/faq">FAQ</a>
            <a href="/signup/" class="nav-cta">Sign Up</a>
        </div>
    </div>
</nav>

<section class="hero hero-trade stripe-corner">
    <div class="hero-content">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li><a href="/trades/">Trades</a></li>
                <li aria-current="page"><?= h($name) ?></li>
            </ol>
        </nav>
        <span class="eyebrow"><?= h($name) ?> website builder</span>
        <h1><?= h($h1) ?></h1>
        <p class="hero-sub">Custom <?= h(strtolower($name)) ?> websites, live in 24 hours. $200 setup + $80/month. No lock-in contracts.</p>
        <div class="hero-ctas">
            <a href="/signup/" class="btn btn-orange">Get Your <?= h($name) ?> Website</a>
            <a href="/#chat" class="btn btn-ghost">Ask Tradie-Bot</a>
        </div>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container narrow">
        <div class="section-heading reveal">
            <span class="section-kicker">Why It Matters</span>
            <h2>Why <?= h(strtolower($plural)) ?> need a proper website</h2>
        </div>
        <p class="lede reveal"><?= h($intro) ?></p>
    </div>
</section>

<section class="section-pad">
    <div class="container narrow">
        <div class="section-heading reveal">
            <span class="section-kicker">What You Get</span>
            <h2>What's included on your <?= h(strtolower($name)) ?> website</h2>
        </div>
        <ul class="checklist reveal">
<?php foreach ($services as $s): ?>
            <li><?= h($s) ?></li>
<?php endforeach; ?>
        </ul>
        <p class="lede reveal" style="margin-top: 36px; text-align: center;">Plus everything on the standard <a href="/" style="color: var(--orange); font-weight: 600;">Tradie Sites Co.</a> build — mobile-responsive layout, contact form, photo gallery, SEO-ready meta tags and schema. Pick <strong>Hosted</strong> ($80/month) and we keep it online, monitor uptime and fix breakages; pick <strong>Self-host</strong> ($200 one-off) and the files are all yours. Content changes after go-live are quoted separately on both plans.</p>
    </div>
</section>

<section class="section-pad pricing">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">No Hidden Fees</span>
            <h2>Pricing — same for every trade</h2>
            <p>Two plans. Pick the one that fits.</p>
        </div>
        <div class="pricing-cards">
            <div class="pricing-card reveal">
                <h3>Self-host</h3>
                <div class="price">$200</div>
                <div class="price-sub">one-time — no ongoing fee</div>
                <ul>
                    <li>Custom 5-page <?= h(strtolower($name)) ?> website</li>
                    <li>Professional copywriting</li>
                    <li>Domain setup &amp; DNS guidance</li>
                    <li>Full source files handed over</li>
                    <li>Live within 24 hours</li>
                    <li>You arrange your own hosting</li>
                </ul>
                <a href="/signup/" class="btn">Pay $200 Once</a>
            </div>
            <div class="pricing-card popular reveal">
                <div class="popular-banner">Most Popular</div>
                <h3>Hosted</h3>
                <div class="price">$200<span style="font-size: 1.3rem; color: var(--gray-700); font-weight: 600; letter-spacing: 0;"> + $80/mo</span></div>
                <div class="price-sub">we host &amp; keep it online</div>
                <ul>
                    <li>Everything in Self-host, plus</li>
                    <li>Fast Cloudflare hosting + SSL</li>
                    <li>Uptime monitoring</li>
                    <li>Breakage fixes on the house</li>
                    <li>Email &amp; phone support</li>
                    <li>No lock-in — cancel any time</li>
                </ul>
                <a href="/signup/" class="btn btn-orange">Start Hosted Plan</a>
            </div>
        </div>
        <p class="lede reveal" style="margin-top: 28px; text-align: center; font-size: .92rem; color: var(--gray-700);"><strong>Straight up:</strong> content edits, new pages and new features are quoted separately on both plans. On the Hosted plan, if you stop paying the $80/month the site goes offline. Flagged upfront — no surprises.</p>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container narrow">
        <div class="section-heading reveal">
            <span class="section-kicker">FAQs</span>
            <h2><?= h($name) ?> website FAQs</h2>
        </div>
        <div class="faqs">
<?php foreach ($faqs as $f): ?>
            <details class="faq reveal">
                <summary><?= h($f['q']) ?></summary>
                <p><?= h($f['a']) ?></p>
            </details>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="contact-cta">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">Ready When You Are</span>
            <h2>Ready to get your <?= h(strtolower($name)) ?> website online?</h2>
            <p>Fill in the form on the homepage and we'll be in touch within 24 hours — or ask our assistant any question first.</p>
        </div>
        <div class="cta-row">
            <a href="/signup/" class="btn">Get Started — $200</a>
            <a href="/#chat" class="btn btn-link">Ask Tradie-Bot →</a>
            <a href="/trades/" class="btn btn-link">All 30 trades →</a>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div>
            <h4>Tradie Sites Co.</h4>
            <p style="color: rgba(255,255,255,.55); font-size: .9rem; max-width: 260px;">Done-for-you websites for Australian tradies. $200 setup + $80/month. Live in 24 hours.</p>
        </div>
        <div><h4>Links</h4><a href="/">Home</a><a href="/trades/">Trades</a><a href="/gallery">Gallery</a><a href="/blog/">Blog</a><a href="/#pricing">Pricing</a><a href="/#chat">Chat</a></div>
        <div><h4>Popular Trades</h4><a href="/trades/plumber">Plumbers</a><a href="/trades/electrician">Electricians</a><a href="/trades/builder">Builders</a><a href="/trades/painter">Painters</a><a href="/trades/roofer">Roofers</a></div>
        <div><h4>Get In Touch</h4><a href="/#contact">Enquiry Form</a><a href="/#chat">Ask Tradie-Bot</a><a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a></div>
    </div>
    <div class="legal">
        <span class="aus-made">◆ Built in Australia</span>
        &nbsp;&middot;&nbsp;
        &copy; <span id="footerYear">2026</span> Tradie Sites Co.
        &nbsp;&middot;&nbsp;
        <a href="/privacy">Privacy</a>
        &nbsp;&middot;&nbsp;
        <a href="/terms">Terms</a>
    </div>
</footer>

<script>
document.getElementById('footerYear').textContent = new Date().getFullYear();
(() => {
    const h = document.getElementById('hamburger'); const n = document.getElementById('navLinks');
    if (!h || !n) return;
    h.addEventListener('click', () => n.classList.toggle('open'));
    n.querySelectorAll('a').forEach(a => a.addEventListener('click', () => n.classList.remove('open')));
    const close = () => n.classList.remove('open');
    document.addEventListener('DOMContentLoaded', close);
    window.addEventListener('hashchange', close);
})();
(() => {
    document.documentElement.classList.add('reveal-ready');
    const els = document.querySelectorAll('.reveal');
    const forceShow = () => els.forEach(el => el.classList.add('is-visible'));
    if (!('IntersectionObserver' in window)) { forceShow(); return; }
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -40px 0px', threshold: 0.01 });
    els.forEach(el => io.observe(el));
    /* safety net: if observer misses anything (fast scroll, prerender, Lighthouse), show everything after 1.2s */
    setTimeout(forceShow, 1200);
})();
</script>

</body>
</html>
