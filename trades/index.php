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
    'isPartOf' => ['@type' => 'WebSite', 'name' => 'Tradie Sites Co.', 'url' => 'https://site.tradiebud.tech/'],
    'hasPart' => array_map(function($slug, $t) {
        return ['@type' => 'Service', 'name' => "Website for {$t['plural']}", 'url' => "https://site.tradiebud.tech/trades/{$slug}"];
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
    <meta property="og:title" content="Tradie Website Templates — 30+ Trades">
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
            <a href="/blog/">Blog</a>
            <a href="/#chat">Chat</a>
            <a href="/#contact" class="nav-cta">Get Your Site</a>
        </div>
    </div>
</nav>

<section class="hero hero-trade stripe-corner">
    <div class="hero-content">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li aria-current="page">Trades</li>
            </ol>
        </nav>
        <span class="eyebrow">30+ Trade Templates</span>
        <h1>Tradie Website Templates — Australia Wide</h1>
        <p class="hero-sub">Pick your trade to see what we build for it. Same $200 setup + $80/month pricing. Tailored content, live in 24 hours.</p>
        <div class="hero-ctas">
            <a href="/#contact" class="btn btn-orange">Get Started — $200</a>
            <a href="/#chat" class="btn btn-ghost">Ask Tradie-Bot</a>
        </div>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">All 30 Trades</span>
            <h2>Click A Trade. See What We Build.</h2>
            <p>Each page shows the features, licence displays and FAQs specific to that trade.</p>
        </div>
        <div class="trade-grid">
<?php foreach ($trades as $slug => $t): ?>
            <a class="trade-tile reveal" href="/trades/<?= h($slug) ?>">
                <span class="tile-kicker"><?= h($t['name']) ?> website builder</span>
                <h3><?= h($t['plural']) ?></h3>
                <span class="tile-cta">See Template</span>
            </a>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="contact-cta">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">Ready When You Are</span>
            <h2>Get Your Site Live In 24 Hours</h2>
            <p>Pick your trade above or just tell us what you do — we'll handle the rest.</p>
        </div>
        <div class="cta-row">
            <a href="/#contact" class="btn">Get Started — $200</a>
            <a href="/#chat" class="btn btn-link">Or ask Tradie-Bot →</a>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div>
            <h4>Tradie Sites Co.</h4>
            <p style="color: rgba(255,255,255,.55); font-size: .9rem; max-width: 260px;">Done-for-you websites for Australian tradies. $200 setup + $80/month. Live in 24 hours.</p>
        </div>
        <div><h4>Links</h4><a href="/">Home</a><a href="/trades/">Trades</a><a href="/blog/">Blog</a><a href="/#pricing">Pricing</a><a href="/#chat">Chat</a></div>
        <div><h4>Popular Trades</h4><a href="/trades/plumber">Plumbers</a><a href="/trades/electrician">Electricians</a><a href="/trades/builder">Builders</a><a href="/trades/painter">Painters</a><a href="/trades/roofer">Roofers</a></div>
        <div><h4>Get In Touch</h4><a href="/#contact">Enquiry Form</a><a href="/#chat">Ask Tradie-Bot</a><a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a></div>
    </div>
    <div class="legal">
        <span class="aus-made">◆ Built in Australia</span>
        &nbsp;&middot;&nbsp;
        &copy; <span id="footerYear">2026</span> Tradie Sites Co.
    </div>
</footer>

<script>
document.getElementById('footerYear').textContent = new Date().getFullYear();
(() => {
    const h = document.getElementById('hamburger'); const n = document.getElementById('navLinks');
    if (!h || !n) return;
    h.addEventListener('click', () => n.classList.toggle('open'));
    n.querySelectorAll('a').forEach(a => a.addEventListener('click', () => n.classList.remove('open')));
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
