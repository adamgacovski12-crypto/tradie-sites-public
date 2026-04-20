<?php
$trades = require __DIR__ . '/_trades.php';

$pageTitle = 'Tradie Website Templates — 30+ Trades Across Australia | Tradie Sites Co.';
$pageDesc  = 'Websites for 30+ Australian trades — plumbers, electricians, builders, painters, concreters and 25 more. $200 setup + $80/month. Live in 24 hours.';
$pageUrl   = 'https://site.tradiebud.tech/trades/';

$collectionJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'CollectionPage',
    'name'     => 'Trade website templates — Tradie Sites Co.',
    'url'      => $pageUrl,
    'description' => $pageDesc,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name'  => 'Tradie Sites Co.',
        'url'   => 'https://site.tradiebud.tech/',
    ],
    'hasPart' => array_map(function($slug, $t) {
        return [
            '@type' => 'Service',
            'name'  => "Website for {$t['plural']}",
            'url'   => "https://site.tradiebud.tech/trades/{$slug}",
        ];
    }, array_keys($trades), array_values($trades)),
];

$breadcrumbJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',   'item' => 'https://site.tradiebud.tech/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Trades', 'item' => $pageUrl],
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
    <meta name="description" content="<?= h($pageDesc) ?>">
    <link rel="canonical" href="<?= h($pageUrl) ?>">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Tradie Sites Co.">
    <meta property="og:title" content="<?= h('Tradie Website Templates — 30+ Trades') ?>">
    <meta property="og:description" content="<?= h($pageDesc) ?>">
    <meta property="og:url" content="<?= h($pageUrl) ?>">
    <meta property="og:image" content="https://site.tradiebud.tech/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_AU">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="https://site.tradiebud.tech/og-image.jpg">

    <script type="application/ld+json"><?= jld($collectionJsonLd) ?></script>
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
                <li aria-current="page">Trades</li>
            </ol>
        </nav>
        <p class="eyebrow">Trade website templates</p>
        <h1>Tradie Website Templates — 30+ Trades Across Australia</h1>
        <p class="hero-sub">Pick your trade to see what we build for it. Same $200 setup + $80/month pricing, tailored content, live in 24 hours.</p>
        <div class="hero-ctas">
            <a href="/#contact" class="btn btn-orange">Get Started — $200</a>
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
    <div class="container">
        <div class="section-heading">
            <h2>All 30 trade templates</h2>
            <p>Each page shows what's included on a website for that trade and answers common questions.</p>
        </div>
        <div class="trade-grid">
<?php foreach ($trades as $slug => $t): ?>
            <a class="trade-tile" href="/trades/<?= h($slug) ?>">
                <span><?= h($t['name']) ?> website builder</span>
                <h3><?= h($t['plural']) ?></h3>
            </a>
<?php endforeach; ?>
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
