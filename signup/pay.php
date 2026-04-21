<?php
require __DIR__ . '/_helpers.php';
$cfg = tsc_cfg();

$ref = strtoupper(preg_replace('/[^A-Z0-9-]/i', '', (string)($_GET['ref'] ?? '')));
$record = $ref !== '' ? tsc_load_record($ref) : null;

if ($record === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><meta charset=utf-8><title>Signup not found</title>';
    echo '<link rel="stylesheet" href="/assets/site.css">';
    echo '<section class="hero hero-trade stripe-corner"><div class="hero-content"><h1>Signup not found</h1><p class="hero-sub">That reference doesn\'t match any signup. If you just submitted, check your email — the receipt includes your payment link.</p><p><a href="/signup/" class="btn btn-orange">Start again</a></p></div></section>';
    exit;
}

$plan    = $cfg['plans'][$record['plan']] ?? $cfg['plans']['hosted'];
$bank    = $cfg['bank'];
$isHosted = !empty($plan['is_hosted']);
$nextDue = $isHosted
    ? date('j F Y', strtotime(($record['date'] ?? 'now') . ' ' . $plan['recurring_interval']))
    : null;

$pageUrl = 'https://site.tradiebud.tech/signup/pay';
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer $200 — <?= tsc_h($record['reference']) ?> | Tradie Sites Co.</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preload" href="/assets/fonts/barlow-condensed-800.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/fonts/fonts.css">
    <link rel="stylesheet" href="/assets/site.css">
    <style>
        .pay-box {
            background: var(--white); border: 4px solid var(--black);
            padding: 32px 34px; box-shadow: 10px 10px 0 var(--orange);
            margin-bottom: 32px;
        }
        .pay-box h3 {
            font-size: 1.1rem; letter-spacing: 2px; text-transform: uppercase;
            color: var(--orange); margin-bottom: 14px;
        }
        .pay-row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 2px dashed var(--gray-700); flex-wrap: wrap; }
        .pay-row:last-child { border-bottom: 0; }
        .pay-row .k { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: var(--black); font-size: .88rem; }
        .pay-row .v { font-family: 'Courier New', monospace; font-size: 1.1rem; font-weight: 700; color: var(--black); user-select: all; }
        .pay-ref-shout {
            background: var(--orange); color: var(--black);
            border: 4px solid var(--black); padding: 20px 24px; margin: 24px 0;
            box-shadow: 6px 6px 0 var(--black); text-align: center;
        }
        .pay-ref-shout .label { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; font-size: .82rem; }
        .pay-ref-shout .ref { font-family: 'Courier New', monospace; font-size: 2rem; font-weight: 800; letter-spacing: 2px; margin-top: 4px; user-select: all; }
        .pay-warning {
            background: #fff7d6; border: 3px solid var(--black); padding: 14px 18px;
            margin-top: 16px; font-weight: 600;
        }
        .pay-next {
            background: var(--cream); border: 3px solid var(--black); padding: 22px 26px;
        }
        .pay-next ol { margin: 10px 0 0 24px; }
        .pay-next li { margin: 6px 0; }
        .pay-support { text-align: center; margin: 28px 0 12px; font-weight: 600; }
        .pay-alt { text-align: center; color: var(--gray-700); font-size: .88rem; margin-top: 4px; }
    </style>
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
            <a href="/#chat">Chat</a>
            <a href="/about">About</a>
            <a href="/faq">FAQ</a>
            <a href="/signup/" class="nav-cta">Sign Up</a>
        </div>
    </div>
</nav>

<section class="hero hero-trade stripe-corner">
    <div class="hero-content">
        <span class="eyebrow">Reference <?= tsc_h($record['reference']) ?></span>
        <h1>Final step — transfer the setup fee</h1>
        <p class="hero-sub">Your site starts building the moment we see the money land.</p>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container narrow">
        <div class="pay-box">
            <h3>Transfer Details</h3>
            <div class="pay-row"><span class="k">Amount</span><span class="v">$<?= number_format((float)$plan['setup'], 2) ?></span></div>
            <div class="pay-row"><span class="k">Account name</span><span class="v"><?= tsc_h($bank['account_name']) ?></span></div>
            <div class="pay-row"><span class="k">BSB</span><span class="v"><?= tsc_h($bank['bsb']) ?></span></div>
            <div class="pay-row"><span class="k">Account number</span><span class="v"><?= tsc_h($bank['account_number']) ?></span></div>

            <div class="pay-ref-shout">
                <div class="label">Your reference code — type this in the transfer description</div>
                <div class="ref"><?= tsc_h($record['reference']) ?></div>
            </div>

            <div class="pay-warning">
                <strong>MUST include this reference code in the transfer description</strong> — it's how we match your payment to your signup. Without it, we can't confirm your site.
            </div>
        </div>

        <div class="pay-next">
            <h3 style="color: var(--orange); font-size: 1.1rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 10px;">What happens next</h3>
            <ol>
                <li>You transfer <strong>$<?= number_format((float)$plan['setup'], 2) ?></strong> (reference: <strong><?= tsc_h($record['reference']) ?></strong>).</li>
                <li>Adam confirms payment within 24 hours.</li>
                <li>Site goes live within 24 hours of payment confirmation.</li>
<?php if ($isHosted): ?>
                <li>First hosting invoice (<strong>$80</strong>, due <strong><?= tsc_h($nextDue) ?></strong>) lands in your inbox a week before that date. Miss a payment and the site goes offline until it's sorted.</li>
                <li>Want to add pages, change copy, or rebuild a section later? Reply to any of our emails — we quote changes separately.</li>
<?php else: ?>
                <li>We email you the full site source files + DNS setup instructions. From there it's yours to host wherever.</li>
                <li>Want changes or a fresh build later? Reply to any of our emails — we quote changes separately.</li>
<?php endif; ?>
            </ol>
        </div>

        <p class="pay-support">Questions? Email <a href="mailto:info@tradiebud.tech" style="color: var(--orange); font-weight: 700;">info@tradiebud.tech</a></p>
        <p class="pay-alt">Can't pay by bank transfer? Reply to the confirmation email — we'll sort it out.</p>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div>
            <h4>Tradie Sites Co.</h4>
            <p style="color: rgba(255,255,255,.55); font-size: .9rem; max-width: 260px;">Done-for-you websites for Australian tradies. $200 setup + $80/month. Live in 24 hours.</p>
        </div>
        <div><h4>Links</h4><a href="/">Home</a><a href="/trades/">Trades</a><a href="/gallery">Gallery</a><a href="/blog/">Blog</a><a href="/#pricing">Pricing</a><a href="/#chat">Chat</a></div>
        <div><h4>Popular Trades</h4><a href="/trades/plumber">Plumbers</a><a href="/trades/electrician">Electricians</a><a href="/trades/builder">Builders</a><a href="/trades/painter">Painters</a><a href="/trades/roofer">Roofers</a></div>
        <div><h4>Get In Touch</h4><a href="/#contact">Enquiry Form</a><a href="/#chat">Ask Tradie-Bot</a><a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a></div>
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
