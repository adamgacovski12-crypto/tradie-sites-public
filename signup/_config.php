<?php
/**
 * Tradie Sites Co. — signup / payment config.
 *
 * REPLACE THE PLACEHOLDERS in BANK_DETAILS below with Adam's real CommBank
 * details. This file is the single source of truth — everywhere else reads
 * from here.
 *
 * Blocked from web by the ^_ rule in /.htaccess.
 */

return [
    /* ── Bank details. Replace the placeholders. ── */
    'bank' => [
        'account_name'   => '{{BANK_ACCOUNT_NAME}}',
        'bsb'            => '{{BANK_BSB}}',
        'account_number' => '{{BANK_ACCOUNT_NUMBER}}',
    ],

    /* ── Plans ──
     * self_host: $200 one-time. We build it, hand over the files, they host it themselves. No ongoing fee, no ongoing service.
     * hosted:    $200 setup + $80/month. We host it on Cloudflare, monitor uptime, fix breakages. Stop paying = site goes offline.
     * New features or content changes are quoted separately on BOTH plans.
     */
    'plans' => [
        'self_host' => [
            'key'     => 'self_host',
            'label'   => 'Self-host',
            'setup'   => 200,
            'is_hosted' => false,
            'recurring_amount'   => 0,
            'recurring_interval' => null,
            'recurring_label'    => 'No ongoing fee',
            'headline' => 'Own the files, host wherever you like. One-off $200 and it is yours.',
            'sub'      => '$200 one-time',
            'includes' => [
                'Custom 5-page website (same build as hosted)',
                'Professional copywriting',
                'Domain setup &amp; DNS guidance',
                'Full source files handed over',
                'Live within 24 hours',
            ],
            'excludes' => [
                'Hosting (you arrange it)',
                'Monitoring / breakage fixes',
                'Future changes or new features',
            ],
        ],
        'hosted' => [
            'key'     => 'hosted',
            'label'   => 'Hosted',
            'setup'   => 200,
            'is_hosted' => true,
            'recurring_amount'   => 80,
            'recurring_interval' => '+1 month',
            'recurring_label'    => '$80/month',
            'headline' => 'We host and look after it. Stop paying and the site goes offline.',
            'sub'      => '$200 setup + $80/month',
            'includes' => [
                'Custom 5-page website',
                'Professional copywriting',
                'Domain setup &amp; DNS',
                'Fast Cloudflare hosting + SSL',
                'Uptime monitoring',
                'Breakage fixes (stuff that breaks on its own)',
                'Email &amp; phone support',
            ],
            'excludes' => [
                'Content edits or new pages (quoted separately)',
                'New features or redesigns (quoted separately)',
            ],
        ],
    ],

    /* ── Emails ── */
    'admin_email' => 'info@tradiebud.tech',
    'from_email'  => 'info@tradiebud.tech',
    'from_name'   => 'Tradie Sites Co.',

    /* ── GST handling ──
     * Flip 'gst_enabled' to true once the business is GST-registered (annual
     * turnover ≥ $75,000). When true, all customer-facing receipts and invoices
     * gain a GST line and the Terms-page summary updates to reflect tax-inclusive
     * pricing. Until then, prices are shown GST-free per s. 9-5 GST Act.
     *
     * IMPORTANT: when you flip this on, also (a) re-quote any in-flight signups,
     * (b) update the .htaccess pricing copy that says "we don't charge GST yet",
     * and (c) talk to your accountant about the effective date. */
    'gst_enabled' => false,
    'gst_rate'    => 0.10,         /* 10% — Australia */
    'abn'         => '41 670 505 816',

    /* ── Licensed trades: licence number is required for these ── */
    'licence_required_slugs' => ['plumber', 'electrician', 'gas-fitter', 'builder'],

    /* ── Rate limit: max signups per IP per hour ── */
    'rate_limit_per_hour' => 5,

    /* ── Upload limits (bytes) ── */
    'logo_max_bytes'  => 2 * 1024 * 1024,
    'photo_max_bytes' => 5 * 1024 * 1024,
    'logo_exts'       => ['png', 'jpg', 'jpeg', 'svg'],
    'photo_exts'      => ['jpg', 'jpeg', 'png'],

    /* ── Data paths (absolute, resolved once here) ── */
    'paths' => [
        'signups_dir'    => dirname(__DIR__) . '/signups',
        'records_dir'    => dirname(__DIR__) . '/signups/records',
        'assets_dir'     => dirname(__DIR__) . '/signups/assets',
        'ratelimits_dir' => dirname(__DIR__) . '/signups/ratelimits',
        'csv_file'       => dirname(__DIR__) . '/signups/signups.csv',
    ],
];
