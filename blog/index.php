<?php
/**
 * Blog listing. Newest post first, paginated 10/page.
 */

$postsDir = __DIR__ . '/posts';
$files = glob($postsDir . '/*.md') ?: [];
/* newest first: filenames begin with YYYY-MM-DD so a reverse sort is correct */
rsort($files);

$posts = [];
foreach ($files as $f) {
    $raw = file_get_contents($f);
    if (!preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $raw, $m)) continue;
    $meta = json_decode($m[1], true);
    if (!is_array($meta)) continue;
    $body = $m[2];

    /* 150-char excerpt from plain-text body (strip markdown syntax roughly) */
    $plain = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $body);   // links → text
    $plain = preg_replace('/[`#*_>\-]/', '', $plain);           // md punctuation
    $plain = preg_replace('/\s+/', ' ', $plain);
    $plain = trim($plain);
    $mb = function_exists('mb_substr');
    $excerpt = $mb ? mb_substr($plain, 0, 150) : substr($plain, 0, 150);
    $len     = $mb ? mb_strlen($plain) : strlen($plain);
    if ($len > 150) $excerpt .= '…';

    $posts[] = [
        'slug'        => (string)($meta['slug']        ?? basename($f, '.md')),
        'title'       => (string)($meta['title']       ?? 'Untitled'),
        'description' => (string)($meta['description'] ?? ''),
        'date'        => (string)($meta['date']        ?? ''),
        'topic_tag'   => (string)($meta['topic_tag']   ?? 'general'),
        'excerpt'     => $excerpt,
    ];
}

/* ── Pagination ── */
$perPage = 10;
$total   = count($posts);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = max(1, min($pages, (int)($_GET['page'] ?? 1)));
$slice   = array_slice($posts, ($page - 1) * $perPage, $perPage);

$pageUrl = 'https://site.tradiebud.tech/blog/' . ($page > 1 ? "?page={$page}" : '');

$collectionJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'CollectionPage',
    'name'     => 'Tradie Sites Co. Blog',
    'url'      => $pageUrl,
    'description' => 'Tradie website tips, SEO advice and lead-generation how-tos for Australian tradies.',
    'isPartOf' => ['@type' => 'WebSite', 'name' => 'Tradie Sites Co.', 'url' => 'https://site.tradiebud.tech/'],
];
$breadcrumbJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://site.tradiebud.tech/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => 'https://site.tradiebud.tech/blog/'],
    ],
];

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function jld($a) { return json_encode($a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); }

$pageTitle = 'Tradie Website Tips & Advice — Tradie Sites Co. Blog';
$pageDesc  = 'Plain-English blog for Australian tradies: how to get more leads, rank on Google, and turn your website into a job machine.';
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
    <meta property="og:title" content="<?= h($pageTitle) ?>">
    <meta property="og:description" content="<?= h($pageDesc) ?>">
    <meta property="og:url" content="<?= h($pageUrl) ?>">
    <meta property="og:image" content="https://site.tradiebud.tech/og-image.jpg">
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
                <li aria-current="page">Blog</li>
            </ol>
        </nav>
        <span class="eyebrow">Tradie Sites Co. Blog</span>
        <h1>Tradie Website Tips &amp; Advice</h1>
        <p class="hero-sub">How to get found, get leads, and turn your website into the hardest-working apprentice on your crew.</p>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container">
<?php if (empty($slice)): ?>
        <div class="section-heading reveal">
            <span class="section-kicker">Just Getting Started</span>
            <h2>First posts coming shortly</h2>
            <p>We're publishing tradie website tips, SEO how-tos and lead-gen tactics weekly. Come back soon, or <a href="/#contact" style="color: var(--orange);">ring us now</a>.</p>
        </div>
<?php else: ?>
        <div class="section-heading reveal">
            <span class="section-kicker"><?= (int)$total ?> posts</span>
            <h2>The Tradie Website Playbook</h2>
            <p>Plain-English advice. No agency fluff. Written for Aussies on worksites, not marketers in meetings.</p>
        </div>
        <div class="blog-grid">
<?php foreach ($slice as $p): ?>
            <a class="blog-card reveal" href="/blog/<?= h($p['slug']) ?>">
                <div class="meta">
                    <span><?= h($p['topic_tag']) ?></span>
                    <?php if ($p['date']): ?><span><?= h(date('j M Y', strtotime($p['date']))) ?></span><?php endif; ?>
                </div>
                <h3><?= h($p['title']) ?></h3>
                <p><?= h($p['description'] !== '' ? $p['description'] : $p['excerpt']) ?></p>
                <span class="read-link">Read</span>
            </a>
<?php endforeach; ?>
        </div>
<?php if ($pages > 1): ?>
        <nav class="pagination reveal" aria-label="Blog pagination">
<?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-link">← Newer</a>
<?php endif; ?>
            <span class="current">Page <?= (int)$page ?> of <?= (int)$pages ?></span>
<?php if ($page < $pages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-link">Older →</a>
<?php endif; ?>
        </nav>
<?php endif; ?>
<?php endif; ?>
    </div>
</section>

<section class="contact-cta">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">Ready When You Are</span>
            <h2>Stop reading. Start ranking.</h2>
            <p>We build tradie websites in 24 hours — $200 setup + $80/month, no lock-in.</p>
        </div>
        <div class="cta-row">
            <a href="/#contact" class="btn">Get Started — $200</a>
            <a href="/trades/" class="btn btn-link">Browse 30 trades →</a>
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
    setTimeout(forceShow, 1200);
})();
</script>

</body>
</html>
