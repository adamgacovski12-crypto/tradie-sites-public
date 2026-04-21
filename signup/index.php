<?php
require __DIR__ . '/_helpers.php';
$cfg = tsc_cfg();
$trades = require __DIR__ . '/../trades/_trades.php';
$csrf = tsc_csrf_token();
$licenceSlugs = $cfg['licence_required_slugs'];

$pageTitle = 'Sign Up — $200 Setup, $80/month | Tradie Sites Co.';
$pageDesc  = 'Sign up for your tradie website. $200 setup + $80/month. Four quick steps, live in 24 hours after payment lands.';
$pageUrl   = 'https://site.tradiebud.tech/signup/';
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tsc_h($pageTitle) ?></title>
    <meta name="description" content="<?= tsc_h($pageDesc) ?>">
    <meta name="robots" content="noindex,follow">
    <link rel="canonical" href="<?= tsc_h($pageUrl) ?>">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preload" href="/assets/fonts/barlow-condensed-800.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/fonts/fonts.css">
    <link rel="stylesheet" href="/assets/site.css">
    <style>
        .signup-steps-nav { display: flex; gap: 8px; justify-content: center; margin: 0 auto 32px; flex-wrap: wrap; }
        .signup-steps-nav .pip {
            background: var(--white); border: 3px solid var(--black);
            padding: 8px 16px;
            font-family: 'Barlow Condensed', sans-serif; font-weight: 800;
            letter-spacing: 2px; text-transform: uppercase; font-size: .82rem;
            color: var(--black);
        }
        .signup-steps-nav .pip.is-active { background: var(--orange); color: var(--black); box-shadow: 4px 4px 0 var(--black); }
        .signup-steps-nav .pip.is-done { background: var(--black); color: var(--orange); }
        .signup-step { display: none; }
        .signup-step.is-active { display: block; }
        .plan-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .plan-card {
            background: var(--white); border: 4px solid var(--black);
            padding: 32px 28px; text-align: center;
            box-shadow: 8px 8px 0 var(--black);
            transition: transform .12s, box-shadow .12s;
            cursor: pointer;
        }
        .plan-card:hover { transform: translate(-3px,-3px); box-shadow: 11px 11px 0 var(--orange); }
        .plan-card h3 { font-size: 2rem; color: var(--black); letter-spacing: 1px; }
        .plan-card .price { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 3.2rem; color: var(--orange); margin: 4px 0 2px; letter-spacing: 1px; }
        .plan-card .price small { font-size: 1rem; color: var(--gray-700); font-weight: 600; letter-spacing: 0; }
        .plan-card .tagline { color: var(--gray-700); font-size: .98rem; margin: 12px 0 14px; min-height: 44px; }
        .plan-card .plan-includes, .plan-card .plan-excludes { list-style: none; padding: 0; margin: 0 0 14px; text-align: left; }
        .plan-card .plan-includes li { padding: 4px 0 4px 22px; position: relative; font-size: .92rem; color: var(--black); }
        .plan-card .plan-includes li::before { content: '✓'; position: absolute; left: 0; color: var(--orange); font-weight: 800; }
        .plan-card .plan-excludes { margin-top: 6px; padding-top: 10px; border-top: 2px dashed var(--gray-700); }
        .plan-card .plan-excludes li.heading { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: var(--gray-700); font-size: .78rem; padding: 0 0 4px; }
        .plan-card .plan-excludes li { padding: 3px 0 3px 22px; position: relative; font-size: .88rem; color: var(--gray-700); }
        .plan-card .plan-excludes li:not(.heading)::before { content: '×'; position: absolute; left: 0; color: var(--gray-700); font-weight: 800; }
        .plan-disclosure {
            background: #fff7d6; border: 3px solid var(--black); padding: 14px 18px;
            margin-top: 20px; font-size: .9rem; color: var(--black);
        }
        .plan-disclosure strong { color: var(--black); }
        .plan-card .choose {
            display: inline-block; background: var(--black); color: var(--orange);
            padding: 14px 22px; font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: .95rem;
            border: 3px solid var(--black);
        }
        .plan-card.is-selected { background: var(--orange); }
        .plan-card.is-selected .price { color: var(--black); }
        .plan-card.is-selected .tagline { color: var(--black); }
        .signup-form-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px 24px;
        }
        .signup-form-grid .full { grid-column: 1 / -1; }
        .signup-field { display: flex; flex-direction: column; gap: 6px; }
        .signup-field label {
            font-family: 'Barlow Condensed', sans-serif; font-weight: 800;
            letter-spacing: 2px; text-transform: uppercase; font-size: .82rem;
            color: var(--black);
        }
        .signup-field .hint { color: var(--gray-700); font-size: .82rem; font-weight: 500; letter-spacing: 0; text-transform: none; }
        .signup-field input[type=text], .signup-field input[type=email], .signup-field input[type=tel], .signup-field input[type=url], .signup-field input[type=number], .signup-field select, .signup-field textarea {
            background: var(--white); border: 3px solid var(--black); padding: 12px 14px;
            font-family: inherit; font-size: 1rem; color: var(--black);
        }
        .signup-field input:focus, .signup-field select:focus, .signup-field textarea:focus {
            outline: 3px solid var(--orange); outline-offset: 1px;
        }
        .signup-field textarea { min-height: 90px; resize: vertical; }
        .signup-field.has-error input, .signup-field.has-error select, .signup-field.has-error textarea { border-color: #c0392b; }
        .signup-field .err-msg { color: #c0392b; font-size: .82rem; display: none; font-weight: 600; letter-spacing: 0; text-transform: none; }
        .signup-field.has-error .err-msg { display: block; }
        .signup-field input[type=file] {
            padding: 10px; background: var(--cream); border: 3px dashed var(--black);
            font-family: inherit; font-size: .9rem;
        }
        .signup-nav {
            display: flex; gap: 14px; margin-top: 32px; flex-wrap: wrap;
        }
        .signup-nav button { font-family: 'Barlow Condensed', sans-serif; cursor: pointer; }
        .btn-back {
            background: var(--white); color: var(--black); border: 3px solid var(--black);
            padding: 14px 22px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;
        }
        .btn-next, .btn-submit {
            background: var(--orange); color: var(--black); border: 3px solid var(--black);
            padding: 14px 28px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;
            box-shadow: 4px 4px 0 var(--black); font-size: 1rem;
        }
        .btn-next:hover, .btn-submit:hover { transform: translate(-2px,-2px); box-shadow: 6px 6px 0 var(--black); }
        .btn-submit { background: var(--black); color: var(--orange); font-size: 1.15rem; padding: 18px 32px; }
        .review-block {
            background: var(--cream); border: 3px solid var(--black); padding: 24px 26px;
            margin-bottom: 18px;
        }
        .review-block h3 {
            font-size: 1.1rem; letter-spacing: 1.5px; text-transform: uppercase;
            color: var(--orange); margin-bottom: 10px;
        }
        .review-block dl { margin: 0; }
        .review-block dt { font-weight: 700; margin-top: 8px; }
        .review-block dd { margin: 0 0 4px; color: var(--gray-900); }
        .licence-field { display: none; }
        .licence-field.is-visible { display: flex; }
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
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li aria-current="page">Sign Up</li>
            </ol>
        </nav>
        <span class="eyebrow">Start your tradie website</span>
        <h1>Four quick steps. Live in 24 hours.</h1>
        <p class="hero-sub">Pick your plan, tell us about your business, upload a few photos, pay the $200 setup. Your site goes live within 24 hours of the payment landing.</p>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container narrow">
        <nav class="signup-steps-nav" aria-label="Signup steps">
            <span class="pip is-active" id="pip-1">1 · Plan</span>
            <span class="pip" id="pip-2">2 · Business</span>
            <span class="pip" id="pip-3">3 · Site content</span>
            <span class="pip" id="pip-4">4 · Review &amp; pay</span>
        </nav>

        <form id="signupForm" action="/signup/submit.php" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf" value="<?= tsc_h($csrf) ?>">
            <input type="hidden" name="plan" id="plan" value="">

            <!-- STEP 1 ── PLAN -->
            <div class="signup-step is-active" data-step="1">
                <div class="section-heading reveal is-visible" style="text-align: center; margin-bottom: 28px;">
                    <span class="section-kicker">Step 1</span>
                    <h2>Pick your plan</h2>
                </div>
                <div class="plan-cards">
<?php foreach ($cfg['plans'] as $p): ?>
                    <div class="plan-card" data-plan="<?= tsc_h($p['key']) ?>" tabindex="0" role="button" aria-label="Choose <?= tsc_h($p['label']) ?>">
                        <h3><?= tsc_h(strtoupper($p['label'])) ?></h3>
                        <div class="price"><?= tsc_h($p['sub']) ?></div>
                        <div class="tagline"><?= tsc_h($p['headline']) ?></div>
                        <ul class="plan-includes">
<?php foreach (($p['includes'] ?? []) as $inc): ?>
                            <li><?= $inc /* pre-sanitised entities in config */ ?></li>
<?php endforeach; ?>
                        </ul>
                        <ul class="plan-excludes">
                            <li class="heading">Not included</li>
<?php foreach (($p['excludes'] ?? []) as $exc): ?>
                            <li><?= $exc ?></li>
<?php endforeach; ?>
                        </ul>
                        <span class="choose">Choose <?= tsc_h(strtoupper($p['label'])) ?></span>
                    </div>
<?php endforeach; ?>
                </div>
                <div class="plan-disclosure">
                    <strong>What you're agreeing to:</strong> New pages, content edits and new features are quoted separately on both plans — $80/month covers hosting, monitoring and breakage fixes only. If you pick <strong>Hosted</strong> and stop paying, the site goes offline. That's standard for any hosting provider — disclosed here upfront so there's no surprises.
                </div>
            </div>

            <!-- STEP 2 ── BUSINESS -->
            <div class="signup-step" data-step="2">
                <div class="section-heading reveal is-visible" style="text-align: center; margin-bottom: 28px;">
                    <span class="section-kicker">Step 2</span>
                    <h2>Your business</h2>
                </div>
                <div class="signup-form-grid">
                    <div class="signup-field full">
                        <label for="f-business">Business name *</label>
                        <input type="text" id="f-business" name="business_name" required maxlength="120" autocomplete="organization">
                        <span class="err-msg">Enter your business name.</span>
                    </div>
                    <div class="signup-field">
                        <label for="f-contact">Your name *</label>
                        <input type="text" id="f-contact" name="contact_name" required maxlength="80" autocomplete="name">
                        <span class="err-msg">Enter your name.</span>
                    </div>
                    <div class="signup-field">
                        <label for="f-phone">Mobile phone *</label>
                        <input type="tel" id="f-phone" name="phone" required maxlength="25" autocomplete="tel" inputmode="tel" placeholder="04xx xxx xxx">
                        <span class="err-msg">Enter a valid Australian phone number.</span>
                    </div>
                    <div class="signup-field">
                        <label for="f-email">Email *</label>
                        <input type="email" id="f-email" name="email" required maxlength="120" autocomplete="email" inputmode="email">
                        <span class="err-msg">Enter a valid email.</span>
                    </div>
                    <div class="signup-field">
                        <label for="f-abn">ABN * <span class="hint">(11 digits)</span></label>
                        <input type="text" id="f-abn" name="abn" required maxlength="14" inputmode="numeric" placeholder="XX XXX XXX XXX">
                        <span class="err-msg">Enter a valid 11-digit ABN.</span>
                    </div>
                    <div class="signup-field">
                        <label for="f-trade">Trade *</label>
                        <select id="f-trade" name="trade" required>
                            <option value="">Select your trade</option>
<?php foreach ($trades as $slug => $t): ?>
                            <option value="<?= tsc_h($slug) ?>"><?= tsc_h($t['name']) ?></option>
<?php endforeach; ?>
                        </select>
                        <span class="err-msg">Pick your trade.</span>
                    </div>
                    <div class="signup-field full">
                        <label for="f-suburbs">Suburbs you service <span class="hint">(up to 500 characters)</span></label>
                        <textarea id="f-suburbs" name="suburbs" maxlength="500" placeholder="e.g. Parramatta, Westmead, Harris Park, Granville — roughly 20km radius of Parramatta"></textarea>
                    </div>
                    <div class="signup-field full licence-field" id="licence-field">
                        <label for="f-licence">Licence number * <span class="hint">(required for your trade)</span></label>
                        <input type="text" id="f-licence" name="licence" maxlength="40">
                        <span class="err-msg">Licence number is required for your trade.</span>
                    </div>
                </div>
                <div class="signup-nav">
                    <button type="button" class="btn-back" data-back>← Back</button>
                    <button type="button" class="btn-next" data-next>Next: Site content →</button>
                </div>
            </div>

            <!-- STEP 3 ── SITE CONTENT -->
            <div class="signup-step" data-step="3">
                <div class="section-heading reveal is-visible" style="text-align: center; margin-bottom: 28px;">
                    <span class="section-kicker">Step 3</span>
                    <h2>Your site content</h2>
                </div>
                <div class="signup-form-grid">
                    <div class="signup-field full">
                        <label for="f-tagline">Business tagline</label>
                        <input type="text" id="f-tagline" name="tagline" maxlength="140" placeholder="e.g. 24/7 emergency plumbing across Western Sydney">
                    </div>
                    <div class="signup-field">
                        <label for="f-service1">Service 1 *</label>
                        <input type="text" id="f-service1" name="service1" required maxlength="80" placeholder="e.g. Hot water repairs">
                        <span class="err-msg">Add at least one service.</span>
                    </div>
                    <div class="signup-field">
                        <label for="f-service2">Service 2</label>
                        <input type="text" id="f-service2" name="service2" maxlength="80" placeholder="e.g. Burst pipe emergencies">
                    </div>
                    <div class="signup-field">
                        <label for="f-service3">Service 3</label>
                        <input type="text" id="f-service3" name="service3" maxlength="80" placeholder="e.g. Blocked drain clearing">
                    </div>
                    <div class="signup-field">
                        <label for="f-years">Years in business</label>
                        <input type="number" id="f-years" name="years" min="0" max="80" inputmode="numeric">
                    </div>
                    <div class="signup-field">
                        <label for="f-existing-site">Existing website URL</label>
                        <input type="url" id="f-existing-site" name="existing_website" maxlength="200" placeholder="https://">
                    </div>
                    <div class="signup-field">
                        <label for="f-existing-fb">Existing Facebook page URL</label>
                        <input type="url" id="f-existing-fb" name="existing_fb" maxlength="200" placeholder="https://facebook.com/">
                    </div>
                    <div class="signup-field full">
                        <label for="f-logo">Logo <span class="hint">(PNG / JPG / SVG, max 2MB — optional)</span></label>
                        <input type="file" id="f-logo" name="logo" accept="image/png,image/jpeg,image/svg+xml">
                    </div>
                    <div class="signup-field full">
                        <label for="f-photos">Photos of your work <span class="hint">(JPG / PNG, max 5MB each, up to 5 photos — optional)</span></label>
                        <input type="file" id="f-photos" name="photos[]" accept="image/jpeg,image/png" multiple>
                    </div>
                </div>
                <div class="signup-nav">
                    <button type="button" class="btn-back" data-back>← Back</button>
                    <button type="button" class="btn-next" data-next>Next: Review →</button>
                </div>
            </div>

            <!-- STEP 4 ── REVIEW + SUBMIT -->
            <div class="signup-step" data-step="4">
                <div class="section-heading reveal is-visible" style="text-align: center; margin-bottom: 28px;">
                    <span class="section-kicker">Step 4</span>
                    <h2>Review &amp; pay</h2>
                    <p>Double-check everything, then hit Submit &amp; Pay. We'll show you the bank details on the next screen.</p>
                </div>
                <div id="review-output"></div>
                <p style="margin: 20px 0 0; font-size: .88rem; color: var(--gray-700); text-align: center;">By clicking <strong>Submit &amp; Pay</strong> you agree to our <a href="/terms" target="_blank" rel="noopener" style="color: var(--orange); font-weight: 700;">Terms of Service</a> and <a href="/privacy" target="_blank" rel="noopener" style="color: var(--orange); font-weight: 700;">Privacy Policy</a>.</p>
                <div class="signup-nav">
                    <button type="button" class="btn-back" data-back>← Back</button>
                    <button type="submit" class="btn-submit">Submit &amp; Pay →</button>
                </div>
            </div>
        </form>
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

/* ── Signup multi-step logic ── */
(() => {
    const form = document.getElementById('signupForm');
    if (!form) return;
    const licenceSlugs = <?= json_encode($licenceSlugs) ?>;
    const steps = form.querySelectorAll('.signup-step');
    const pips  = document.querySelectorAll('.signup-steps-nav .pip');
    let current = 1;

    function showStep(n) {
        current = n;
        steps.forEach(s => s.classList.toggle('is-active', +s.dataset.step === n));
        pips.forEach((p, i) => {
            p.classList.toggle('is-active', i + 1 === n);
            p.classList.toggle('is-done', i + 1 < n);
        });
        window.scrollTo({ top: form.offsetTop - 20, behavior: 'smooth' });
    }

    /* Plan card click */
    document.querySelectorAll('.plan-card').forEach(card => {
        const pick = () => {
            document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            document.getElementById('plan').value = card.dataset.plan;
            setTimeout(() => showStep(2), 180);
        };
        card.addEventListener('click', pick);
        card.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pick(); } });
    });

    /* Licence toggle */
    const tradeSel = document.getElementById('f-trade');
    const licenceField = document.getElementById('licence-field');
    const licenceInput = document.getElementById('f-licence');
    tradeSel.addEventListener('change', () => {
        const needsLicence = licenceSlugs.includes(tradeSel.value);
        licenceField.classList.toggle('is-visible', needsLicence);
        licenceInput.required = needsLicence;
    });

    /* AU phone validation (permissive) */
    function phoneValid(v) {
        const digits = v.replace(/\D/g, '');
        return (digits.length === 10 && digits[0] === '0') || (digits.length === 11 && digits.startsWith('61'));
    }
    function emailValid(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
    function abnValid(v)   { return v.replace(/\D/g, '').length === 11; }

    function setErr(field, on) { field.classList.toggle('has-error', on); }

    function validateStep(n) {
        if (n === 1) {
            if (!document.getElementById('plan').value) { alert('Pick a plan first.'); return false; }
            return true;
        }
        if (n === 2) {
            let ok = true;
            const required = ['f-business','f-contact','f-phone','f-email','f-abn','f-trade'];
            required.forEach(id => {
                const el = document.getElementById(id);
                const field = el.closest('.signup-field');
                const bad = el.value.trim() === '';
                setErr(field, bad);
                if (bad) ok = false;
            });
            if (ok) {
                const phoneEl = document.getElementById('f-phone');
                if (!phoneValid(phoneEl.value)) { setErr(phoneEl.closest('.signup-field'), true); ok = false; }
                const emailEl = document.getElementById('f-email');
                if (!emailValid(emailEl.value)) { setErr(emailEl.closest('.signup-field'), true); ok = false; }
                const abnEl = document.getElementById('f-abn');
                if (!abnValid(abnEl.value)) { setErr(abnEl.closest('.signup-field'), true); ok = false; }
                if (licenceInput.required && licenceInput.value.trim() === '') { setErr(licenceField, true); ok = false; }
            }
            return ok;
        }
        if (n === 3) {
            const svc1 = document.getElementById('f-service1');
            const bad = svc1.value.trim() === '';
            setErr(svc1.closest('.signup-field'), bad);
            return !bad;
        }
        return true;
    }

    function renderReview() {
        const get = id => document.getElementById(id).value.trim();
        const planKey = document.getElementById('plan').value;
        const planLabel = planKey === 'self_host'
            ? 'Self-host — $200 one-time (we build + hand over files, you host it)'
            : 'Hosted — $200 setup + $80/month (we host, monitor and fix breakages)';
        const logo = document.getElementById('f-logo').files[0];
        const photos = document.getElementById('f-photos').files;
        const photoNames = [];
        for (let i = 0; i < photos.length; i++) photoNames.push(photos[i].name);
        const wrap = document.getElementById('review-output');
        const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        wrap.innerHTML =
            '<div class="review-block"><h3>Plan</h3><p><strong>' + esc(planLabel) + '</strong></p></div>' +
            '<div class="review-block"><h3>Your business</h3><dl>' +
            '<dt>Business name</dt><dd>' + esc(get('f-business') || '—') + '</dd>' +
            '<dt>Your name</dt><dd>' + esc(get('f-contact') || '—') + '</dd>' +
            '<dt>Phone</dt><dd>' + esc(get('f-phone') || '—') + '</dd>' +
            '<dt>Email</dt><dd>' + esc(get('f-email') || '—') + '</dd>' +
            '<dt>ABN</dt><dd>' + esc(get('f-abn') || '—') + '</dd>' +
            '<dt>Trade</dt><dd>' + esc(document.getElementById('f-trade').selectedOptions[0]?.text || '—') + '</dd>' +
            '<dt>Suburbs</dt><dd>' + esc(get('f-suburbs') || '—') + '</dd>' +
            (licenceInput.required ? '<dt>Licence</dt><dd>' + esc(get('f-licence') || '—') + '</dd>' : '') +
            '</dl></div>' +
            '<div class="review-block"><h3>Site content</h3><dl>' +
            '<dt>Tagline</dt><dd>' + esc(get('f-tagline') || '—') + '</dd>' +
            '<dt>Services</dt><dd>' + [get('f-service1'), get('f-service2'), get('f-service3')].filter(Boolean).map(esc).join(' · ') + '</dd>' +
            '<dt>Years in business</dt><dd>' + esc(get('f-years') || '—') + '</dd>' +
            '<dt>Existing website</dt><dd>' + esc(get('f-existing-site') || '—') + '</dd>' +
            '<dt>Existing Facebook</dt><dd>' + esc(get('f-existing-fb') || '—') + '</dd>' +
            '<dt>Logo</dt><dd>' + (logo ? esc(logo.name) : '—') + '</dd>' +
            '<dt>Photos</dt><dd>' + (photoNames.length ? photoNames.map(esc).join(', ') : '—') + '</dd>' +
            '</dl></div>';
    }

    form.querySelectorAll('[data-next]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!validateStep(current)) return;
            if (current === 3) renderReview();
            showStep(current + 1);
        });
    });
    form.querySelectorAll('[data-back]').forEach(btn => {
        btn.addEventListener('click', () => { if (current > 1) showStep(current - 1); });
    });

    form.addEventListener('submit', e => {
        if (!validateStep(2) || !validateStep(3) || !document.getElementById('plan').value) {
            e.preventDefault();
            alert('Check that all required fields are filled in before submitting.');
        }
    });
})();
</script>

</body>
</html>
