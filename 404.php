<?php
http_response_code(404);
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page not found — Tradie Sites Co.</title>
    <meta name="description" content="That link doesn't go anywhere. Head back to the home page or browse the 30 trades we build sites for.">
    <meta name="robots" content="noindex,follow">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
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
            <a href="/signup/" class="nav-cta">Sign Up</a>
        </div>
    </div>
</nav>

<section class="hero hero-trade stripe-corner">
    <div class="hero-content">
        <span class="eyebrow">404 — wrong turn</span>
        <h1>This page doesn't exist.</h1>
        <p class="hero-sub">Could be we moved it, could be the link was typed wrong, could be a dud URL someone shared. Whichever it is — here's the way back.</p>
        <div class="hero-ctas">
            <a href="/" class="btn btn-orange">Back to home</a>
            <a href="/trades/" class="btn btn-ghost">See 30 trades</a>
        </div>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container narrow">
        <div class="section-heading reveal is-visible">
            <span class="section-kicker">Probably looking for</span>
            <h2>Where to next?</h2>
        </div>
        <div class="cta-row" style="flex-wrap: wrap; gap: 12px;">
            <a href="/#pricing" class="btn">See pricing</a>
            <a href="/trades/" class="btn">All 30 trades</a>
            <a href="/blog/" class="btn">Blog posts</a>
            <a href="/signup/" class="btn btn-orange">Sign up — $200</a>
            <a href="/#chat" class="btn btn-link">Ask Tradie-Bot →</a>
        </div>
        <p style="text-align: center; margin-top: 28px; color: var(--gray-700); font-size: .92rem;">If you clicked a broken link on our own site, <a href="mailto:info@tradiebud.tech" style="color: var(--orange); font-weight: 700;">email info@tradiebud.tech</a> and we'll sort it.</p>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div>
            <h4>Tradie Sites Co.</h4>
            <p style="color: rgba(255,255,255,.55); font-size: .9rem; max-width: 260px;">Done-for-you websites for Australian tradies. $200 setup + $80/month. Live in 24 hours.</p>
        </div>
        <div><h4>Links</h4><a href="/">Home</a><a href="/trades/">Trades</a><a href="/blog/">Blog</a><a href="/#pricing">Pricing</a><a href="/signup/">Sign Up</a></div>
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
