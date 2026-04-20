<?php
/**
 * Dynamic sitemap.
 * Served at /sitemap.xml via .htaccess RewriteRule.
 */

header('Content-Type: application/xml; charset=UTF-8');

$base = 'https://site.tradiebud.tech';
$today = date('Y-m-d');

$urls = [
    ['loc' => "{$base}/",         'lastmod' => $today, 'changefreq' => 'weekly',  'priority' => '1.0'],
    ['loc' => "{$base}/trades/",  'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.9'],
    ['loc' => "{$base}/signup/",  'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.9'],
    ['loc' => "{$base}/blog/",    'lastmod' => $today, 'changefreq' => 'weekly',  'priority' => '0.8'],
];

/* Trade pages */
$trades = require __DIR__ . '/trades/_trades.php';
foreach (array_keys($trades) as $slug) {
    $urls[] = [
        'loc' => "{$base}/trades/{$slug}",
        'lastmod' => $today,
        'changefreq' => 'monthly',
        'priority' => '0.8',
    ];
}

/* Blog posts */
foreach (glob(__DIR__ . '/blog/posts/*.md') ?: [] as $f) {
    $name = basename($f, '.md');
    if (!preg_match('/^(\d{4}-\d{2}-\d{2})-(.+)$/', $name, $m)) continue;
    $urls[] = [
        'loc' => "{$base}/blog/{$m[2]}",
        'lastmod' => $m[1],
        'changefreq' => 'monthly',
        'priority' => '0.7',
    ];
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $u) {
    echo "  <url>";
    echo "<loc>" . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>";
    echo "<lastmod>{$u['lastmod']}</lastmod>";
    echo "<changefreq>{$u['changefreq']}</changefreq>";
    echo "<priority>{$u['priority']}</priority>";
    echo "</url>\n";
}
echo "</urlset>\n";
