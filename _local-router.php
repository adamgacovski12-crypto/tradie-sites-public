<?php
/**
 * Dev-only router — simulates the Apache rewrites for PHP's built-in server.
 * Only loaded when the server is started with `php -S ... _local-router.php`.
 * Not used in production (Apache reads .htaccess instead).
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve real files/directories as-is
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// /trades/ -> /trades/index.php
if (rtrim($uri, '/') === '/trades') {
    include __DIR__ . '/trades/index.php';
    return true;
}

// /trades/[slug] -> /trades/template.php with slug
if (preg_match('#^/trades/([a-z][a-z0-9-]*)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    include __DIR__ . '/trades/template.php';
    return true;
}

return false;
