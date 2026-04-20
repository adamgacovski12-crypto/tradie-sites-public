<?php
/**
 * Blog post renderer. /blog/[slug] → template.php?slug=[slug]
 */

require __DIR__ . '/../lib/Parsedown.php';
require __DIR__ . '/_markdown.php';

$slug = $_GET['slug'] ?? ($postSlug ?? '');
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug));

if ($slug === '') {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo "<!doctype html><meta charset=utf-8><title>Not found</title><h1>Post not found</h1><p><a href=\"/blog/\">Back to blog</a></p>";
    exit;
}

/* ── Find the .md file whose filename ends with -{slug}.md ── */
$postsDir = __DIR__ . '/posts';
$match = null;
foreach (glob($postsDir . '/*.md') ?: [] as $f) {
    if (preg_match('/\d{4}-\d{2}-\d{2}-' . preg_quote($slug, '/') . '\.md$/', $f)) {
        $match = $f;
        break;
    }
}

if ($match === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo "<!doctype html><meta charset=utf-8><title>Not found</title><h1>Post not found</h1><p><a href=\"/blog/\">Back to blog</a></p>";
    exit;
}

/* ── Parse JSON frontmatter ── */
$raw = file_get_contents($match);
if (!preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $raw, $m)) {
    http_response_code(500);
    echo 'Post is missing frontmatter.';
    exit;
}
$meta = json_decode($m[1], true);
$bodyMd = $m[2];
if (!is_array($meta)) {
    http_response_code(500);
    echo 'Post frontmatter is not valid JSON.';
    exit;
}

$title     = (string)($meta['title']       ?? 'Untitled');
$metaTitle = (string)($meta['meta_title']  ?? $title);
$descr     = (string)($meta['description'] ?? '');
$date      = (string)($meta['date']        ?? '');
$topicTag  = (string)($meta['topic_tag']   ?? 'general');
$faqs      = is_array($meta['faqs'] ?? null) ? $meta['faqs'] : [];

$pageUrl = "https://site.tradiebud.tech/blog/{$slug}";

/* ── Body HTML + reading time ── */
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$bodyHtml = $parsedown->text(tradie_normalise_markdown($bodyMd));

$wordCount = str_word_count(strip_tags($bodyHtml));
$readMins  = max(2, (int)ceil($wordCount / 200));

/* ── JSON-LD ── */
$faqsJson = [];
foreach ($faqs as $f) {
    $q = (string)($f['q'] ?? '');
    $a = (string)($f['a'] ?? '');
    if ($q === '' || $a === '') continue;
    $faqsJson[] = [
        '@type' => 'Question',
        'name'  => $q,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
    ];
}

$articleJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BlogPosting',
    'headline' => $title,
    'description' => $descr,
    'datePublished' => $date,
    'dateModified'  => $date,
    'author'    => ['@type' => 'Organization', 'name' => 'Tradie Sites Co.'],
    'publisher' => [
        '@type' => 'Organization',
        'name'  => 'Tradie Sites Co.',
        'logo'  => ['@type' => 'ImageObject', 'url' => 'https://site.tradiebud.tech/favicon.svg'],
    ],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $pageUrl],
    'url' => $pageUrl,
];
$breadcrumbJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',  'item' => 'https://site.tradiebud.tech/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog',  'item' => 'https://site.tradiebud.tech/blog/'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $title,  'item' => $pageUrl],
    ],
];
$faqJsonLd = $faqsJson ? [
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => $faqsJson,
] : null;

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function jld($a) { return json_encode($a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); }

$displayDate = $date !== '' ? date('j F Y', strtotime($date)) : '';
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($metaTitle) ?> | Tradie Sites Co.</title>
    <meta name="description" content="<?= h($descr) ?>">
    <link rel="canonical" href="<?= h($pageUrl) ?>">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Tradie Sites Co.">
    <meta property="og:title" content="<?= h($metaTitle) ?>">
    <meta property="og:description" content="<?= h($descr) ?>">
    <meta property="og:url" content="<?= h($pageUrl) ?>">
    <meta property="og:image" content="https://site.tradiebud.tech/og-image.jpg">
    <meta property="og:locale" content="en_AU">
    <meta property="article:published_time" content="<?= h($date) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= h($metaTitle) ?>">
    <meta name="twitter:description" content="<?= h($descr) ?>">
    <meta name="twitter:image" content="https://site.tradiebud.tech/og-image.jpg">

    <script type="application/ld+json"><?= jld($articleJsonLd) ?></script>
    <script type="application/ld+json"><?= jld($breadcrumbJsonLd) ?></script>
<?php if ($faqJsonLd): ?>
    <script type="application/ld+json"><?= jld($faqJsonLd) ?></script>
<?php endif; ?>

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
                <li><a href="/blog/">Blog</a></li>
                <li aria-current="page"><?= h($title) ?></li>
            </ol>
        </nav>
        <span class="eyebrow"><?= h($topicTag) ?></span>
        <h1><?= h($title) ?></h1>
        <p class="hero-sub"><?php if ($displayDate): ?><?= h($displayDate) ?> · <?php endif; ?><?= (int)$readMins ?> min read</p>
    </div>
</section>

<article class="section-pad bg-cream">
    <div class="container narrow">
        <div class="post-body reveal">
            <?= $bodyHtml ?>
        </div>
    </div>
</article>

<?php if ($faqs): ?>
<section class="section-pad">
    <div class="container narrow">
        <div class="section-heading reveal">
            <span class="section-kicker">FAQs</span>
            <h2>Quick questions, straight answers</h2>
        </div>
        <div class="faqs">
<?php foreach ($faqs as $f): ?>
            <details class="faq reveal">
                <summary><?= h($f['q'] ?? '') ?></summary>
                <p><?= h($f['a'] ?? '') ?></p>
            </details>
<?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="contact-cta">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">Ready When You Are</span>
            <h2>Want your trade on the first page of Google?</h2>
            <p>We build tradie websites in 24 hours. $200 setup + $80/month. No lock-in.</p>
        </div>
        <div class="cta-row">
            <a href="/#contact" class="btn">Get Started — $200</a>
            <a href="/blog/" class="btn btn-link">More blog posts →</a>
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
    setTimeout(forceShow, 1200);
})();
</script>

</body>
</html>
