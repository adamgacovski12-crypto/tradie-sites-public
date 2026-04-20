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

    /* ── Plans ── */
    'plans' => [
        'monthly' => [
            'key'     => 'monthly',
            'label'   => 'Monthly',
            'setup'   => 200,
            'recurring_amount'   => 80,
            'recurring_interval' => '+1 month',
            'recurring_label'    => '$80/month',
            'headline' => 'Most flexible — cancel anytime after first month',
            'sub'      => '$200 setup + $80/month',
        ],
        'annual' => [
            'key'     => 'annual',
            'label'   => 'Annual',
            'setup'   => 200,
            'recurring_amount'   => 800,
            'recurring_interval' => '+1 year',
            'recurring_label'    => '$800/year',
            'headline' => 'Most popular — tradies pay less, get more (save $160 / 2 months free)',
            'sub'      => '$200 setup + $800/year',
        ],
    ],

    /* ── Emails ── */
    'admin_email' => 'info@tradiebud.tech',
    'from_email'  => 'info@tradiebud.tech',
    'from_name'   => 'Tradie Sites Co.',

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
