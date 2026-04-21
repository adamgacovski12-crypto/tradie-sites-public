<?php
$pageTitle = 'Privacy Policy | Tradie Sites Co.';
$pageDesc  = 'How Tradie Sites Co. collects, uses and protects the personal information you give us. Plain-English summary + full policy.';
$pageUrl   = 'https://site.tradiebud.tech/privacy';
$effectiveDate = '20 April 2026';
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
                <li aria-current="page">Privacy</li>
            </ol>
        </nav>
        <span class="eyebrow">Privacy Policy</span>
        <h1>How We Handle Your Info</h1>
        <p class="hero-sub">We collect what's needed to build and run your website. Nothing more. We don't sell your data, ever. Here's the full picture.</p>
    </div>
</section>

<article class="section-pad bg-cream">
    <div class="container narrow">
        <div class="post-body reveal is-visible">

<p style="color: var(--gray-700); font-size: .92rem;"><strong>Effective:</strong> <?= htmlspecialchars($effectiveDate) ?> &nbsp;·&nbsp; <strong>Governed by:</strong> Australian Privacy Principles (Privacy Act 1988 (Cth)).</p>

<h2>The short version</h2>
<ul>
    <li>We collect your <strong>name, email, phone, ABN, trade, suburbs, licence number, and photos</strong> when you sign up or ask for a quote.</li>
    <li>We use it to <strong>build your website, run the hosting, send invoices, and occasionally email you about changes to your service</strong>. That's it.</li>
    <li>We <strong>don't sell it, rent it, or share it</strong> beyond the specific third parties listed below (hosting, email, chatbot).</li>
    <li>You can <strong>ask for access, correction, or deletion</strong> at any time by emailing <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>.</li>
    <li>If you stop using our service, we keep records for <strong>7 years</strong> for tax and ABN compliance, then delete them.</li>
</ul>

<h2>1. Who we are</h2>
<p><strong>Tradie Sites Co.</strong> (ABN 41 670 505 816) is the "we" throughout this policy. You can reach us at <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>. We're based in Australia and run the business from Australia.</p>

<h2>2. What personal information we collect</h2>
<p>We collect only what's needed to deliver our service:</p>
<ul>
    <li><strong>Signup data</strong>: business name, your name, mobile phone, email address, ABN, trade, suburbs you service, licence number (for licensed trades), business tagline, years in business, existing website URL, existing Facebook page URL.</li>
    <li><strong>Uploaded assets</strong>: your logo and photos of your work.</li>
    <li><strong>Chat conversations</strong>: messages you send to our chatbot, including any phone number you provide when booking a callback.</li>
    <li><strong>Payment references</strong>: the bank reference code from your $200 setup transfer (we don't see your BSB or account number — only what you include in the transfer description).</li>
    <li><strong>Technical data</strong>: your IP address when you submit a form (used only for rate-limiting / anti-spam, auto-deleted every hour).</li>
</ul>
<p>We do <strong>not</strong> collect credit card numbers, banking credentials, or any sensitive information (Privacy Act s. 6(1) definition) such as health, religious beliefs, or racial origin.</p>

<h2>3. How we collect it</h2>
<p>You give it to us, knowingly, through one of these channels:</p>
<ul>
    <li>The signup form at <a href="/signup/">/signup/</a></li>
    <li>The contact form on the home page</li>
    <li>The Tradie-Bot chatbot</li>
    <li>Direct email, phone or SMS you initiate</li>
</ul>
<p>We don't use cookies to track you across sites. We don't use advertising pixels. The only cookie we set is a session cookie that keeps you logged in during a signup and protects against cross-site request forgery — it's deleted when you close the browser.</p>

<h2>4. Why we collect it and how we use it</h2>
<ul>
    <li><strong>To build your website</strong> — we use your business info, trade, suburbs, licence, photos etc. to write the copy and design the site.</li>
    <li><strong>To run your hosting</strong> — if you're on the Hosted plan, we keep the site online at Cloudflare and use your contact details to invoice you.</li>
    <li><strong>To contact you about your service</strong> — receipts, go-live notifications, hosting invoices, breakage alerts.</li>
    <li><strong>To improve the product</strong> — aggregated, de-identified patterns (e.g. "how many tradies signed up this month") inform our development roadmap.</li>
    <li><strong>To meet legal obligations</strong> — tax records, GST reporting, ABR verification.</li>
</ul>
<p>We do <strong>not</strong> send you unsolicited marketing. If we ever add a newsletter, it will be opt-in only and the unsubscribe link in every email will actually work.</p>

<h2>5. Who we share it with</h2>
<p>We use a small number of third-party services to run the business. They each process some of your data on our behalf:</p>
<ul>
    <li><strong>Cloudflare (Pages + DNS)</strong> — hosts your website files if you're on the Hosted plan. May process data in Australia, the US, or other Cloudflare regions.</li>
    <li><strong>VentraIP</strong> — hosts our own server infrastructure (in Australia) and relays our outbound transactional emails (receipts, invoices, go-live notifications) via Exim from the info@tradiebud.tech mailbox.</li>
    <li><strong>Groq Cloud</strong> — processes chatbot conversations (your messages are sent to Groq's LLM API to generate replies). Groq's servers are in the US; messages are processed transiently and not used to train models.</li>
    <li><strong>Formspree</strong> — processes contact form submissions on delivered client sites.</li>
</ul>
<p>We don't share your data with anyone else unless:</p>
<ul>
    <li>You explicitly ask us to (e.g. "please forward my site files to my new web developer").</li>
    <li>We're required by law (subpoena, court order, ATO audit).</li>
    <li>It's necessary to prevent serious harm to life or property.</li>
</ul>
<p>We don't sell, rent, or trade your personal information. Full stop.</p>

<h2>6. Where your data lives</h2>
<p>Your signup records and uploaded photos are stored on our server in Australia (VentraIP). Outbound transactional emails are also relayed by VentraIP's Australian mail servers. Your delivered website, if hosted, lives on Cloudflare's edge network which includes servers in Australia, the US, Europe and Asia — standard for any modern CDN-hosted website. Chatbot messages are processed on Groq's US infrastructure.</p>
<p>Cross-border transfers are covered by Australian Privacy Principle 8. By using our service, you acknowledge that your data may be processed outside Australia by these specific service providers, each of which has its own privacy standards.</p>

<h2>7. How long we keep it</h2>
<ul>
    <li><strong>Active clients</strong> — as long as you're with us, plus 7 years after you leave (for tax, ABR, and GST compliance).</li>
    <li><strong>Chatbot conversations</strong> — 90 days, unless you book a callback, in which case we retain the conversation snippet indefinitely as part of your lead record.</li>
    <li><strong>Rate-limit logs</strong> — deleted every hour.</li>
    <li><strong>Uploaded photos and logos</strong> — kept for as long as we're building or hosting your site, deleted within 30 days of cancellation.</li>
</ul>

<h2>8. Your rights</h2>
<p>Under the Australian Privacy Principles, you can:</p>
<ul>
    <li><strong>Ask what we have about you</strong> — email us and we'll send you everything within 30 days, free.</li>
    <li><strong>Correct anything that's wrong</strong> — email us and we'll fix it within 7 days.</li>
    <li><strong>Request deletion</strong> — we'll delete what we can, immediately; records we have to keep for tax purposes (see §7) stay archived for 7 years and then go.</li>
    <li><strong>Complain</strong> — first to us (<a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>), then if you're not satisfied, to the Office of the Australian Information Commissioner at <a href="https://www.oaic.gov.au/" target="_blank" rel="noopener">oaic.gov.au</a> or 1300 363 992.</li>
</ul>

<h2>9. Security</h2>
<p>We take reasonable steps (APP 11) to protect your data:</p>
<ul>
    <li>All traffic to this site is HTTPS-encrypted.</li>
    <li>Signup records and uploaded files are stored in a server-side directory blocked from direct web access.</li>
    <li>Only Adam has administrative access to the admin dashboard, which is password-protected.</li>
    <li>Third-party service accounts use unique, rotated passwords.</li>
    <li>Our rate-limit and CSRF protections mitigate common form-abuse attacks.</li>
</ul>
<p>We're honest: no system is bulletproof. If we ever experience a notifiable data breach under Part IIIC of the Privacy Act, we'll notify affected users and the OAIC within 30 days as required.</p>

<h2>10. Changes to this policy</h2>
<p>If we change this policy materially (e.g. we add a new third party who processes your data, or change data retention periods), we'll email everyone with an active account at least 14 days before the change takes effect. Minor clarifications or typo fixes won't trigger a notice. The "Effective" date at the top of the policy always shows the current version.</p>

<h2>11. Contact us</h2>
<p>Privacy questions, access requests, corrections, deletions, or complaints: <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>. We'll respond within 7 business days.</p>

<p style="margin-top: 40px; padding-top: 20px; border-top: 2px solid var(--gray-700); font-size: .88rem; color: var(--gray-700);">This policy is written in plain English on purpose. If anything's unclear, ask us and we'll explain.</p>

        </div>
    </div>
</article>

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
