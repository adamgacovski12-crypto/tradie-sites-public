<?php
$pageTitle = 'About Tradie Sites Co. — built by an Aussie, for Aussie tradies';
$pageDesc  = 'Tradie Sites Co. is a small Australian business building websites for tradies. Here\'s who\'s behind it, why we built it, and how to reach us.';
$pageUrl   = 'https://site.tradiebud.tech/about';
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

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AboutPage",
      "url": "<?= htmlspecialchars($pageUrl) ?>",
      "name": "About Tradie Sites Co.",
      "description": "<?= htmlspecialchars($pageDesc) ?>",
      "mainEntity": {
        "@type": "Organization",
        "name": "Tradie Sites Co.",
        "url": "https://site.tradiebud.tech/",
        "email": "info@tradiebud.tech",
        "foundingDate": "2026",
        "areaServed": { "@type": "Country", "name": "Australia" },
        "identifier": {
          "@type": "PropertyValue",
          "name": "ABN",
          "value": "41 670 505 816"
        }
      }
    }
    </script>
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
                <li aria-current="page">About</li>
            </ol>
        </nav>
        <span class="eyebrow">About the business</span>
        <h1>One bloke, 30 trades, one flat price.</h1>
        <p class="hero-sub">Tradie Sites Co. isn't an agency. It's one Aussie developer who got sick of watching mates in the trades lose work to a Facebook page that doesn't rank on Google.</p>
    </div>
</section>

<article class="section-pad bg-cream">
    <div class="container narrow">
        <div class="post-body reveal is-visible">

<h2>Why this exists</h2>
<p>Every tradie I know has the same problem. They've got more work than they can handle from word-of-mouth in the good weeks, and not enough in the quiet ones — because a Facebook page with three photos from 2022 doesn't show up when someone googles "plumber Parramatta" at 9pm on a Sunday.</p>
<p>Meanwhile, agencies charge $4,000 to build them a site they'll never understand, plus $300/month for "ongoing SEO" that never seems to move the needle. The tradie ends up paying for a product built for marketing managers, not for a tradie on a worksite with a dead phone battery.</p>
<p>So I built something simpler. $200 to build the site. $80 a month if you want me to host and look after it (or $0 ongoing if you just want the files). 24 hours from sign-up to live site. No lock-in, no upsell ladder, no surprise invoices for "minor content changes". Content changes are quoted separately on both plans — same as any honest tradesperson quotes a job.</p>

<h2>Who's behind it</h2>
<p>I'm Adam. I run this business myself, from Australia. The only other person touching the work is my cousin, who handles some of the ops and is a 50/50 partner in the business. No offshore team, no "junior account manager", no agency middle-layer. You email <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a> and you're talking to me or my cousin, not a support tier.</p>
<p>I'm not a tradesman. I'm a developer. That matters two ways: I'm good at building websites, and I'm not going to pretend I understand plumbing or electrical work the way you do. That's why every trade page on this site uses real per-trade language — pulled from the licence requirements, the actual service words tradies use, and what homeowners Google. If I get something wrong, tell me, and I'll fix it in the template for everyone.</p>

<h2>The business</h2>
<dl class="trust-strip" style="display: grid; grid-template-columns: 1fr; gap: 10px; background: var(--white); border: 3px solid var(--black); padding: 20px 24px; margin: 28px 0;">
    <div><dt style="font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: .78rem; color: var(--gray-700);">Business name</dt><dd style="margin: 2px 0 0; font-weight: 700;">Tradie Sites Co.</dd></div>
    <div><dt style="font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: .78rem; color: var(--gray-700);">ABN</dt><dd style="margin: 2px 0 0; font-weight: 700;">41 670 505 816</dd></div>
    <div><dt style="font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: .78rem; color: var(--gray-700);">Based in</dt><dd style="margin: 2px 0 0; font-weight: 700;">Australia</dd></div>
    <div><dt style="font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: .78rem; color: var(--gray-700);">Started</dt><dd style="margin: 2px 0 0; font-weight: 700;">2026</dd></div>
    <div><dt style="font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: .78rem; color: var(--gray-700);">Email</dt><dd style="margin: 2px 0 0; font-weight: 700;"><a href="mailto:info@tradiebud.tech" style="color: var(--orange);">info@tradiebud.tech</a></dd></div>
</dl>

<h2>What you're actually buying</h2>
<p>Same five pages every tradie needs: Home, About, Services, Gallery, Contact. Custom copy written for your specific trade — a plumber's site isn't just a "General Business" template with "plumber" swapped in. Hosted on Cloudflare if you go that route, or handed over as files if you'd rather self-host. Your domain, your words, your ABN and licence on every page.</p>
<p>What you're <em>not</em> buying: a five-person account team, a "discovery workshop", a 40-page strategy document, a CMS login you'll never use, or a lock-in contract. The agencies charging $4,000+ do that stuff because they have to — their overheads demand it. I don't, so I don't.</p>

<h2>What I can't do</h2>
<p>I'll be straight with you. This service isn't for every tradie. If you:</p>
<ul>
    <li>Want a 15-page website with a custom booking system and a photo slider that animates in three directions — not this service. Hire an agency.</li>
    <li>Want me to guarantee you'll rank #1 on Google in 30 days — no-one honest can promise that. SEO depends on your competitors, reviews, service area, and Google's mood.</li>
    <li>Want to spend three weeks on design revisions — the $200 model only works because we build fast and ship fast. Two rounds of revisions is the limit before we quote extra time.</li>
    <li>Don't have an ABN or don't hold a current licence for a licensed trade — I can't knowingly market you as something you're not. Get your paperwork sorted first.</li>
</ul>

<h2>How to contact me</h2>
<p>For a quote or a question: <a href="/#chat">ring up Tradie-Bot</a> (the chat on the home page — it's real AI, not a dumb form) or email <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>. I aim to reply within one business day. Ready to buy? <a href="/signup/" style="color: var(--orange); font-weight: 700;">Sign up here</a> — four steps, takes about ten minutes.</p>

        </div>
    </div>
</article>

<section class="contact-cta">
    <div class="container">
        <div class="section-heading reveal">
            <span class="section-kicker">Ready When You Are</span>
            <h2>Let's get your trade on Google.</h2>
            <p>$200 one-off, or $200 + $80/month hosted. Live in 24 hours.</p>
        </div>
        <div class="cta-row">
            <a href="/signup/" class="btn">Sign up — $200</a>
            <a href="/trades/" class="btn btn-link">See 30 trades →</a>
            <a href="/#chat" class="btn btn-link">Ask Tradie-Bot →</a>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div>
            <h4>Tradie Sites Co.</h4>
            <p style="color: rgba(255,255,255,.55); font-size: .9rem; max-width: 260px;">Done-for-you websites for Australian tradies. $200 setup + $80/month. Live in 24 hours.</p>
        </div>
        <div><h4>Links</h4><a href="/">Home</a><a href="/trades/">Trades</a><a href="/blog/">Blog</a><a href="/about">About</a><a href="/faq">FAQ</a><a href="/signup/">Sign Up</a></div>
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
