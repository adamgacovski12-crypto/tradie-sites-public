<?php
$pageTitle = 'Gallery — 18 sample designs for Australian tradies';
$pageDesc  = 'See what a Tradie Sites Co. website could look like for your trade. 18 sample designs across 6 trades and 3 styles — live previews, swap trade + style instantly.';
$pageUrl   = 'https://site.tradiebud.tech/gallery';

// Trade + style inputs are query-param driven so the URL always matches
// what the visitor is currently looking at. Defaults: plumber + modern.
$trades = ['plumber','electrician','builder','landscaper','painter','concreter'];
$styles = ['modern','classic','bold'];

$trade = in_array($_GET['trade'] ?? '', $trades, true) ? $_GET['trade'] : 'plumber';
$style = in_array($_GET['style'] ?? '', $styles, true) ? $_GET['style'] : 'modern';
$initialMockup = '/gallery/mockups/' . $trade . '-' . $style . '.html';

header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($pageUrl) ?>">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Tradie Sites Co.">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($pageUrl) ?>">
    <meta property="og:locale" content="en_AU">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preload" href="/assets/fonts/barlow-condensed-800.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/fonts/fonts.css">
    <link rel="stylesheet" href="/assets/site.css">
    <link rel="stylesheet" href="/assets/gallery.css">
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
            <a href="/gallery" class="is-active">Gallery</a>
            <a href="/blog/">Blog</a>
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
                <li aria-current="page">Gallery</li>
            </ol>
        </nav>
        <span class="eyebrow">Sample designs</span>
        <h1>See what your website could look like.</h1>
        <p class="hero-sub">18 sample designs across 6 trades and 3 styles. Pick your trade, pick your style, see it live. We'll customise the one you like for your business.</p>
        <div class="hero-ctas">
            <a href="/signup/" class="btn btn-orange">Start Your Site — $200</a>
            <a href="#gallery" class="btn btn-ghost">Browse designs ↓</a>
        </div>
    </div>
</section>

<section class="section-pad bg-cream" id="gallery">
    <div class="container wide">

        <div class="gallery-layout">

            <aside class="gallery-side" aria-label="Filters">
                <div class="gallery-filter">
                    <h3 class="filter-title">Trade</h3>
                    <div class="pill-group" role="radiogroup" aria-label="Trade">
                        <?php foreach ($trades as $t): ?>
                            <button type="button" class="pill <?= $t === $trade ? 'is-selected' : '' ?>" role="radio" aria-checked="<?= $t === $trade ? 'true' : 'false' ?>" data-trade="<?= $t ?>">
                                <?= ucfirst($t) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="gallery-filter">
                    <h3 class="filter-title">Style</h3>
                    <div class="pill-group" role="radiogroup" aria-label="Style">
                        <?php foreach ($styles as $s): ?>
                            <button type="button" class="pill <?= $s === $style ? 'is-selected' : '' ?>" role="radio" aria-checked="<?= $s === $style ? 'true' : 'false' ?>" data-style="<?= $s ?>">
                                <?= ucfirst($s) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <p class="filter-foot">
                    Every design is customised with your branding, services and photos before it goes live.
                </p>
            </aside>

            <div class="gallery-preview">
                <div class="preview-header">
                    <p class="preview-breadcrumb" aria-live="polite">
                        <strong id="crumb-trade"><?= ucfirst($trade) ?></strong>
                        <span class="sep">—</span>
                        <strong id="crumb-style"><?= ucfirst($style) ?></strong>
                        <span class="sep">style</span>
                    </p>
                </div>

                <div class="preview-frame-wrap">
                    <div class="preview-skeleton" id="preview-skeleton" aria-hidden="true"></div>
                    <iframe
                        id="preview-frame"
                        class="preview-frame"
                        src="<?= htmlspecialchars($initialMockup) ?>"
                        title="Website design preview"
                        loading="lazy"
                        sandbox="allow-same-origin allow-scripts"
                        referrerpolicy="no-referrer"
                    ></iframe>
                </div>

                <div class="preview-actions">
                    <a href="<?= htmlspecialchars($initialMockup) ?>" id="open-fullscreen" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">
                        Open full-screen ↗
                    </a>
                    <a href="#convert" class="btn btn-orange btn-sm">
                        Get this style for my business
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<section class="section-pad bg-black" id="convert">
    <div class="container narrow">
        <div class="section-heading on-dark reveal is-visible">
            <span class="section-kicker">Like what you see?</span>
            <h2>We'll build yours in 24 hours.</h2>
        </div>
        <ul class="convert-list">
            <li><span class="bullet">1</span> We customise this design with your branding, services, and photos.</li>
            <li><span class="bullet">2</span> Live on your own domain in 24 hours — not 24 weeks.</li>
            <li><span class="bullet">3</span> No lock-in — cancel the $80/month hosting any time (or take the $200 self-host plan and keep the files).</li>
        </ul>
        <div class="cta-row">
            <a href="/signup/" class="btn btn-orange">Start Your Site — $200 setup</a>
            <a href="/#chat" class="btn btn-link">Talk to us first →</a>
        </div>
        <p class="convert-note">Sample designs are illustrative only — invented business names, stock photos, and fake phone numbers. Your real site uses your real details.</p>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div>
            <h4>Tradie Sites Co.</h4>
            <p style="color: rgba(255,255,255,.55); font-size: .9rem; max-width: 260px;">Done-for-you websites for Australian tradies. $200 setup + $80/month. Live in 24 hours.</p>
        </div>
        <div><h4>Links</h4><a href="/">Home</a><a href="/trades/">Trades</a><a href="/gallery">Gallery</a><a href="/blog/">Blog</a><a href="/about">About</a><a href="/faq">FAQ</a><a href="/signup/">Sign Up</a></div>
        <div><h4>Popular Trades</h4><a href="/trades/plumber">Plumbers</a><a href="/trades/electrician">Electricians</a><a href="/trades/builder">Builders</a><a href="/trades/painter">Painters</a><a href="/trades/roofer">Roofers</a></div>
        <div><h4>Legal</h4><a href="/privacy">Privacy</a><a href="/terms">Terms</a><a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a></div>
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
})();
</script>
<script src="/assets/gallery.js"></script>

</body>
</html>
