<?php
/**
 * Tradie Sites Co. — single trade page renderer.
 * Accepts slug via $_GET['slug'] (set by .htaccess rewrite) or the caller
 * setting $tradeSlug before including this file.
 */

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
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => $f['a'],
        ],
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
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'Tradie Sites Co.',
        'url'   => 'https://site.tradiebud.tech/',
    ],
    'offers' => [
        ['@type' => 'Offer', 'name' => 'Setup', 'price' => '200', 'priceCurrency' => 'AUD'],
        ['@type' => 'Offer', 'name' => 'Monthly',  'price' => '80',  'priceCurrency' => 'AUD'],
    ],
];

$faqJsonLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $faqsJson,
];

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

    <link rel="stylesheet" href="/assets/fonts/fonts.css">
    <link rel="stylesheet" href="/assets/site.css">
</head>
<body>

<nav class="nav">
    <div class="container">
        <a href="/" class="nav-logo">Tradie Sites Co.</a>
        <button class="hamburger" aria-label="Menu" id="hamburger"><span></span><span></span><span></span></button>
        <div class="nav-links" id="navLinks">
            <a href="/#how-it-works">How It Works</a>
            <a href="/#pricing">Pricing</a>
            <a href="/trades/">Trades</a>
            <a href="/#contact">Contact</a>
            <a href="/#contact" class="nav-cta">Get Started</a>
        </div>
    </div>
</nav>

<section class="hero hero-trade">
    <div class="hero-content">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li><a href="/trades/">Trades</a></li>
                <li aria-current="page"><?= h($name) ?></li>
            </ol>
        </nav>
        <p class="eyebrow"><?= h($name) ?> website builder</p>
        <h1><?= h($h1) ?></h1>
        <p class="hero-sub">Custom <?= h(strtolower($name)) ?> websites, live in 24 hours. $200 setup + $80/month. No lock-in contracts.</p>
        <div class="hero-ctas">
            <a href="/#contact" class="btn btn-orange">Get Your <?= h($name) ?> Website — $200</a>
            <a href="/#chat" class="btn btn-outline">Chat With Us ↓</a>
        </div>
    </div>
</section>

<div class="trust-strip">
    <div class="container">
        30+ trades covered <span>|</span> 24-hour delivery <span>|</span> $80/month all-in <span>|</span> No lock-in contracts
    </div>
</div>

<section class="section-pad">
    <div class="container narrow">
        <h2>Why <?= h(strtolower($plural)) ?> need a proper website</h2>
        <p class="lede"><?= h($intro) ?></p>
    </div>
</section>

<section class="section-pad alt">
    <div class="container narrow">
        <h2>What's included on your <?= h(strtolower($name)) ?> website</h2>
        <ul class="checklist">
<?php foreach ($services as $s): ?>
            <li><?= h($s) ?></li>
<?php endforeach; ?>
        </ul>
        <p class="lede" style="margin-top:28px">Plus everything on the standard <a href="/">Tradie Sites Co.</a> build — mobile-responsive layout, contact form, photo gallery, Cloudflare hosting, SEO-ready meta tags and schema, and two content edits per month.</p>
    </div>
</section>

<section class="section-pad pricing">
    <div class="container">
        <div class="section-heading">
            <h2>Pricing — same for every trade</h2>
            <p>Simple, transparent pricing. No hidden fees.</p>
        </div>
        <div class="pricing-cards">
            <div class="pricing-card">
                <h3>Setup</h3>
                <div class="price">$200</div>
                <div class="price-sub">One-time payment</div>
                <ul>
                    <li>Custom 5-page <?= h(strtolower($name)) ?> website</li>
                    <li>Professional copywriting</li>
                    <li>Domain setup &amp; configuration</li>
                    <li>Photo gallery setup</li>
                    <li>Live within 24 hours</li>
                </ul>
            </div>
            <div class="pricing-card popular">
                <div class="popular-badge">Most Popular</div>
                <h3>Monthly</h3>
                <div class="price">$80</div>
                <div class="price-sub">Per month, cancel anytime</div>
                <ul>
                    <li>Fast Cloudflare hosting</li>
                    <li>2 content edits per month</li>
                    <li>Gallery photo updates</li>
                    <li>Email &amp; phone support</li>
                    <li>No lock-in — 30 day notice</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container narrow">
        <h2><?= h($name) ?> website FAQs</h2>
        <div class="faqs">
<?php foreach ($faqs as $f): ?>
            <details class="faq">
                <summary><?= h($f['q']) ?></summary>
                <p><?= h($f['a']) ?></p>
            </details>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-pad contact-cta">
    <div class="container">
        <div class="section-heading">
            <h2>Ready to get your <?= h(strtolower($name)) ?> website online?</h2>
            <p>Fill in the form on the homepage and we'll be in touch within 24 hours — or ask our assistant any question first.</p>
        </div>
        <div class="cta-row">
            <a href="/#contact" class="btn btn-orange">Get Started — $200</a>
            <a href="/#chat" class="btn btn-outline">Chat With Us</a>
            <a href="/trades/" class="btn btn-link">See all 30 trades →</a>
        </div>
    </div>
</section>

<footer class="footer">
    <p><a href="/">Tradie Sites Co.</a> | <a href="https://site.tradiebud.tech">site.tradiebud.tech</a> | <a href="/trades/">All Trades</a></p>
    <p>&copy; <span id="footerYear">2026</span> Tradie Sites Co.</p>
</footer>

<script>
document.getElementById('footerYear').textContent = new Date().getFullYear();
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');
if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => navLinks.classList.toggle('open'));
    navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('open')));
}
</script>

</body>
</html>
