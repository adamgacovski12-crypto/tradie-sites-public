<?php
$pageTitle = 'Tradie Website FAQ — Tradie Sites Co.';
$pageDesc  = 'Twelve straight-talking answers to the things tradies actually ask before they sign up. Pricing, domain ownership, cancellation, what happens if you stop paying.';
$pageUrl   = 'https://site.tradiebud.tech/faq';

$faqs = [
    [
        'q' => 'How is this so much cheaper than what agencies quoted me?',
        'a' => "Because we don't have an agency's overheads. No glass office, no senior designers, no 'account managers', no 90-minute discovery workshops. Same five pages every tradie needs, same mobile-responsive code, same Cloudflare hosting. You're paying for the product, not for someone to explain the product to you over three meetings.",
    ],
    [
        'q' => "If I pick the Hosted plan and stop paying, what actually happens?",
        'a' => "The site goes offline until the overdue amount is cleared. We hold your files for 90 days so if you pay the overdue amount in that window, the site goes straight back up. After 90 days the files may be deleted. That's standard for any hosted service — flagged here upfront so nobody's surprised. Cancelling is free; it's only non-payment that takes the site down.",
    ],
    [
        'q' => 'Do I own my domain?',
        'a' => "Yes. Your name goes on the .com.au registry (roughly $20–30/yr to renew). If you leave us, the domain stays yours — you just point it somewhere else. We never register domains in our name.",
    ],
    [
        'q' => "Am I locked in for 12 months like HiPages?",
        'a' => "No. Cancel any time by emailing info@tradiebud.tech. Your hosting continues until the end of the last paid month, then stops. No cancellation fee, no 'notice period' trick, no '12-month minimum' surprise. That's the single biggest difference between us and most of the industry.",
    ],
    [
        'q' => "I heard tradie web guys often disappear — what if you vanish?",
        'a' => "Fair question, genuinely — it's happened to a lot of tradies. Two answers: (1) we're a real registered business (ABN 41 670 505 816), not a Facebook-group freelancer; (2) the Self-host plan ($200 one-off) is your safety net. You own the source files from day one. If we ever disappeared, you'd still have everything you need to host the site somewhere else.",
    ],
    [
        'q' => "What's included in the $80/month on the Hosted plan?",
        'a' => "Fast Cloudflare hosting, SSL certificate, uptime monitoring, breakage fixes (if a dependency update breaks the site we fix it free), and email + phone support. That's it. Content edits, new pages, new features, redesigns — quoted separately on both plans. We don't bundle edits into the $80 because honest quoting beats surprise invoices.",
    ],
    [
        'q' => "How much for a content change later?",
        'a' => "Depends on the change. Small tweaks — update the phone number, swap a photo, fix a typo — usually $20–50. Add a new page from scratch — usually $80–150. A new feature like a booking system — we'll quote it after we know what you need. No automatic hourly ticker, no 'minimum 30-minute' fee. We quote the job, you say yes or no.",
    ],
    [
        'q' => "What's the difference between Self-host and Hosted?",
        'a' => "Same 5-page build either way. Self-host ($200 one-off): we hand over the source files; you host it wherever you like; no ongoing fee from us, but we're also not maintaining or fixing it. Hosted ($200 + $80/month): we keep it on Cloudflare, watch for breakages, fix anything that stops working. Pick Self-host if you're confident managing web hosting; pick Hosted if you'd rather not think about it.",
    ],
    [
        'q' => 'How long does the build actually take?',
        'a' => "Target is 24 hours from the $200 landing in our account to your site being live on a staging URL for you to review. Another day or so after your approval to set up your custom domain. We've never missed the 24-hour build target so far, but if we ever do, you'll hear why before the deadline — not after.",
    ],
    [
        'q' => 'Can I get a refund if I hate the draft?',
        'a' => "Yes. Within 72 hours of us sending the first draft, reply with 'please refund' and we return the full $200. After 72 hours OR after you approve the site, the setup fee is non-refundable because the work's been delivered. Hosting fees are billed monthly in advance and aren't pro-rata refunded mid-month, but Australian Consumer Law rights always override that.",
    ],
    [
        'q' => "Why do you only take bank transfer — no credit card?",
        'a' => "Two reasons: (1) bank transfer has no processing fees, so we pass the savings on, and (2) we don't want to hold your card details on our servers. If you can't do bank transfer for any reason, reply to the receipt email — we'll sort a workaround.",
    ],
    [
        'q' => "Do I need to be GST-registered to sign up?",
        'a' => "No. Most tradies under \$75k/yr aren't GST-registered and that's fine. We don't charge GST on our prices currently because our own turnover is below the threshold; once we cross it, GST will be added to future invoices. Your ABN is required (for our records and your licensed-trade page), but GST status isn't.",
    ],
    [
        'q' => "I still don't get it — can I just ring or chat?",
        'a' => "Yes. Easiest way: the Tradie-Bot chat on the home page — it's real AI, not a dumb form, and it'll happily answer questions for 20 minutes if you want. Or email info@tradiebud.tech. Reply within one business day.",
    ],
];

$faqsJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function($f) {
        return [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ];
    }, $faqs),
];

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

    <script type="application/ld+json"><?= json_encode($faqsJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
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
                <li aria-current="page">FAQ</li>
            </ol>
        </nav>
        <span class="eyebrow">Frequently asked questions</span>
        <h1>Straight answers to the bits tradies actually ask.</h1>
        <p class="hero-sub">Every question here has come up more than once from a real tradie. If yours isn't answered below, email <a href="mailto:info@tradiebud.tech" style="color: var(--orange);">info@tradiebud.tech</a> and we'll add it.</p>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container narrow">
        <div class="faqs">
<?php foreach ($faqs as $f): ?>
            <details class="faq reveal is-visible" open>
                <summary><?= htmlspecialchars($f['q']) ?></summary>
                <p><?= htmlspecialchars($f['a']) ?></p>
            </details>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="contact-cta">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">Still unsure?</span>
            <h2>Ask Tradie-Bot or just email us.</h2>
            <p>It's faster than googling the answer. Real reply, usually same day.</p>
        </div>
        <div class="cta-row">
            <a href="/#chat" class="btn">Ask Tradie-Bot</a>
            <a href="mailto:info@tradiebud.tech" class="btn btn-link">Email us →</a>
            <a href="/signup/" class="btn btn-link">Or sign up now →</a>
        </div>
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
    const close = () => n.classList.remove('open');
    document.addEventListener('DOMContentLoaded', close);
    window.addEventListener('hashchange', close);
})();
</script>

</body>
</html>
