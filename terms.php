<?php
$pageTitle = 'Terms of Service | Tradie Sites Co.';
$pageDesc  = 'The terms you agree to when signing up with Tradie Sites Co. Two plans, what each includes, cancellation, refunds — in plain English.';
$pageUrl   = 'https://site.tradiebud.tech/terms';
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
            <a href="/signup/" class="nav-cta">Sign Up</a>
        </div>
    </div>
</nav>

<section class="hero hero-trade stripe-corner">
    <div class="hero-content">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li aria-current="page">Terms</li>
            </ol>
        </nav>
        <span class="eyebrow">Terms of Service</span>
        <h1>The Deal, In Writing.</h1>
        <p class="hero-sub">What we do for you, what you pay us, what happens if either of us wants out. Plain English. No tricks.</p>
    </div>
</section>

<article class="section-pad bg-cream">
    <div class="container narrow">
        <div class="post-body reveal is-visible">

<p style="color: var(--gray-700); font-size: .92rem;"><strong>Effective:</strong> <?= htmlspecialchars($effectiveDate) ?> &nbsp;·&nbsp; <strong>Provider:</strong> Tradie Sites Co. (ABN 41 670 505 816), Australia.</p>

<h2>Summary — the bits that matter</h2>
<ul>
    <li><strong>Two plans</strong>: <em>Self-host</em> ($200 one-off, we build and hand over the files) or <em>Hosted</em> ($200 setup + $80/month, we host and look after it).</li>
    <li><strong>Content edits, new pages and new features</strong> are quoted separately on both plans. $80/month covers hosting, monitoring and breakage fixes only.</li>
    <li><strong>Hosted plan cancellation</strong>: no lock-in, cancel any time. <strong>If you stop paying, the site goes offline</strong> — that's how hosting works, disclosed upfront.</li>
    <li><strong>Refunds</strong>: $200 setup fee is refundable within 72 hours of the first draft if you don't approve it. After approval or go-live, the setup fee is non-refundable.</li>
    <li><strong>You own your domain and content</strong>; we retain the template code structure.</li>
    <li><strong>Australian Consumer Law</strong> applies. Nothing in these terms limits your statutory rights.</li>
</ul>

<h2>1. What this is</h2>
<p>These terms form a binding agreement between you (the customer) and Tradie Sites Co. (us) when you sign up at <a href="/signup/">/signup/</a>. By submitting the signup form or transferring the $200 setup fee, you agree to these terms.</p>

<h2>2. What we provide</h2>
<p>We build a 5-page tradie website (home, about, services, gallery, contact) for your business. Depending on the plan you pick:</p>
<h3>Self-host ($200 one-off)</h3>
<ul>
    <li>Custom 5-page website built to your business, trade and service area.</li>
    <li>Professional copywriting in Australian English.</li>
    <li>Domain setup + DNS guidance.</li>
    <li>Full source files (HTML, CSS, images, fonts) handed over to you.</li>
    <li>Target turnaround: site live within 24 hours of the setup fee landing.</li>
    <li>After handover, you host the site wherever you choose. No ongoing fee from us.</li>
    <li>After handover, we have no ongoing obligation to maintain, update, or fix the site unless you engage us separately.</li>
</ul>
<h3>Hosted ($200 setup + $80/month)</h3>
<ul>
    <li>Everything in Self-host, plus:</li>
    <li>Fast hosting on Cloudflare Pages with SSL certificate.</li>
    <li>Uptime monitoring.</li>
    <li>Breakage fixes — if something stops working by itself (e.g. a dependency update breaks the site), we fix it without extra charge.</li>
    <li>Email and phone support during Australian business hours (Mon–Fri, 9am–5pm AEST/AEDT).</li>
    <li>No minimum term. Cancel any time by emailing <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>.</li>
</ul>

<h2>3. What's NOT included (both plans)</h2>
<p>The following are quoted separately and billed as separate work, regardless of plan:</p>
<ul>
    <li>Content edits after go-live (copy changes, price changes, new photos, new testimonials, new service descriptions).</li>
    <li>New pages beyond the original 5.</li>
    <li>New features (booking systems, e-commerce, calculators, integrations, blog set-up, newsletter capture, etc.).</li>
    <li>Redesigns or re-brands.</li>
    <li>Search Engine Optimisation campaigns (the site ships with basic on-page SEO; we don't run paid SEO campaigns as part of the $80/month).</li>
    <li>Google Ads, Facebook Ads, or any paid advertising setup.</li>
    <li>Written content beyond the initial 5 pages (blog posts, service descriptions, about copy expansions).</li>
</ul>
<p>If you need any of the above, email us and we'll give you a quote. No surprises, no automatic charges.</p>

<h2>4. Pricing, payment, and what happens if you stop paying</h2>
<p>The setup fee is $200 and is due before we start building. Payment is by Australian bank transfer to the account shown on your signup confirmation page and receipt email. All prices are in Australian dollars.</p>
<p><strong>GST:</strong> if and when our annual turnover crosses $75,000 we will register for GST and add it to subsequent invoices. Prior to that, prices listed are GST-free.</p>
<p>For the Hosted plan, the $80/month recurring fee covers the current calendar month of hosting, monitoring and support. We invoice one week before each due date. Payment is due by the stated due date.</p>
<p style="background: #fff7d6; border: 3px solid var(--black); padding: 18px; margin: 20px 0;"><strong>Stop-paying clause (Hosted plan only):</strong> if a hosting invoice is unpaid for more than 14 days past the due date, we will take the website offline until payment is received. Your files and content are preserved for 90 days after the site goes offline — pay the overdue amount within that window and the site goes back up. After 90 days of non-payment, your files may be deleted. This is standard practice for hosted services and is disclosed here upfront. It is not a lock-in or a cancellation penalty; cancelling is always free.</p>

<h2>5. Cancellation</h2>
<p><strong>Hosted plan</strong>: cancel any time by emailing <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>. We'll process the cancellation within 2 business days. Your hosting continues until the end of the last paid month, then the site goes offline. No cancellation fee, no "notice period" trick. You keep your domain and we'll export your site files on request for free within 30 days of cancellation.</p>
<p><strong>Self-host plan</strong>: there's nothing to cancel — you bought files, not a subscription.</p>

<h2>6. Refunds</h2>
<ul>
    <li><strong>$200 setup fee</strong>: refundable in full if requested within 72 hours of us sending you the first draft of the site, AND you have not approved or deployed the site. Simply reply to the draft email with "please refund" and we'll return the full amount within 5 business days.</li>
    <li>Once you've approved the draft or the site has gone live (whichever is earlier), the setup fee is non-refundable because the work has been delivered.</li>
    <li><strong>$80/month hosting fees</strong>: we don't pro-rata refund mid-month fees — hosting is billed monthly in advance. If this seems unfair in your circumstances, email us and we'll work it out; Australian Consumer Law rights are never affected by this clause.</li>
</ul>

<h2>7. Ownership</h2>
<ul>
    <li><strong>Your domain</strong>: you own it. Your name on the registry, your renewal, your control.</li>
    <li><strong>Your content</strong>: all copy, photos, logos, business info you supplied remain yours. We have a licence to use them for the purpose of delivering your website service.</li>
    <li><strong>The website code</strong>: on Self-host, the full source is handed over and is yours to modify, distribute, or re-host. On Hosted, the deployed copy is yours; the template code and design system we use internally to build efficiently remains our intellectual property — if you cancel and want to re-host elsewhere, we'll give you a static export of your deployed site free of charge within 30 days of cancellation request.</li>
</ul>

<h2>8. Your responsibilities</h2>
<ul>
    <li>Provide accurate business information (ABN, licence number, contact details).</li>
    <li>Own or legally hold rights to the photos and logos you upload.</li>
    <li>Not ask us to publish misleading, illegal, defamatory, or regulator-prohibited content (e.g. claiming qualifications you don't hold, unsubstantiated claims).</li>
    <li>For licensed trades: keep your licence current. If it expires or is suspended, tell us immediately; we'll take the site offline until the licence is restored. Running a website that claims you hold a licence you don't is your legal exposure, not ours.</li>
    <li>Pay invoices on time.</li>
</ul>

<h2>9. Our responsibilities</h2>
<ul>
    <li>Build your site in good faith to the standard shown in our trade examples.</li>
    <li>On the Hosted plan, keep your site reasonably available. "Reasonably available" means we target 99.9% uptime (about 9 hours downtime per year) but can't promise it — CDN outages, DNS incidents and force majeure happen.</li>
    <li>Comply with the Australian Privacy Principles in handling your data (see our <a href="/privacy">Privacy Policy</a>).</li>
    <li>Respond to support emails within 2 business days on the Hosted plan.</li>
    <li>Give you written notice at least 14 days before any material change to these terms that affects your rights.</li>
</ul>

<h2>10. Consumer guarantees and warranties</h2>
<p><strong>Nothing in these terms excludes or limits any rights you have under the Australian Consumer Law (ACL).</strong> Specifically, services supplied to a consumer under the ACL come with guarantees including: due care and skill; fitness for a disclosed purpose; and reasonable time for supply. If we breach these, your remedies may include a refund, re-performance of the service, or compensation for foreseeable loss.</p>
<p>Beyond the ACL guarantees, we don't offer additional warranties. We specifically don't warrant that:</p>
<ul>
    <li>Your site will rank on Google at any particular position, for any keyword, within any timeframe. SEO outcomes depend on your competitors, Google's algorithm and your suburbs.</li>
    <li>You'll receive a specific number of enquiries or conversions from the site. That depends on your pricing, reviews, competition and market.</li>
</ul>

<h2>11. Liability</h2>
<p>To the maximum extent permitted by law (and subject to the non-excludable ACL guarantees above), our total liability to you for any and all claims under or in connection with these terms is capped at the total amount you have paid us in the 12 months before the claim arose. We are not liable for indirect or consequential losses (lost profits, reputational damage, business interruption) except where the ACL specifically provides otherwise.</p>
<p>This cap is a usual one for small-business SaaS. If you need a higher cap because your business depends heavily on the website, tell us before you sign up and we can discuss a custom agreement.</p>

<h2>12. Privacy</h2>
<p>How we handle your data is set out in our <a href="/privacy">Privacy Policy</a>. The Privacy Policy is part of this agreement.</p>

<h2>13. Disputes</h2>
<p>If something's gone wrong, email us first at <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>. We'll try to resolve it within 14 days. If that doesn't work, your options include:</p>
<ul>
    <li>Contacting your state or territory consumer affairs office (e.g. NSW Fair Trading, Consumer Affairs Victoria, Office of Fair Trading QLD).</li>
    <li>Contacting the <a href="https://www.accc.gov.au/" target="_blank" rel="noopener">ACCC</a> for serious Australian Consumer Law breaches.</li>
    <li>Small claims through your state or territory's Civil and Administrative Tribunal (e.g. NCAT, VCAT, QCAT).</li>
    <li>Ordinary courts as a last resort.</li>
</ul>
<p>We won't force mandatory arbitration on you. Nothing takes away your right to approach the consumer regulator or tribunal in your state.</p>

<h2>14. Governing law</h2>
<p>These terms are governed by the law of New South Wales, Australia, and any court proceedings (if needed) take place in New South Wales — but your Australian Consumer Law rights apply wherever you are in Australia.</p>

<h2>15. Changes to these terms</h2>
<p>We may update these terms to reflect changes in the service, new third-party dependencies, or legal requirements. Material changes will be emailed to all active customers at least 14 days before they take effect. If a change disadvantages you materially, you can cancel within that 14-day window and the new terms won't apply to you before cancellation. Minor drafting clarifications won't trigger a notice. The "Effective" date at the top always shows the current version.</p>

<h2>16. Severability</h2>
<p>If any clause in these terms is unenforceable in a court, the rest of the terms stay in force. The unenforceable clause is to be read down (or struck) to the minimum extent needed to make the rest valid.</p>

<h2>17. Contact</h2>
<p>All notices, cancellation requests, refund requests, access requests, and questions go to <a href="mailto:info@tradiebud.tech">info@tradiebud.tech</a>. We'll respond within 2 business days.</p>

<p style="margin-top: 40px; padding-top: 20px; border-top: 2px solid var(--gray-700); font-size: .88rem; color: var(--gray-700);">These terms are written in plain English on purpose. If any clause is unclear, ask and we'll explain it. We're tradies' own, not a lawyer trying to win the contract.</p>

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
