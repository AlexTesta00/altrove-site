<?php
declare(strict_types=1);
// Router del solo server PHP di sviluppo: il server web di produzione usa .htaccess.
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
$webroot = realpath(__DIR__ . '/../public');
$path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$static = in_array($path, ['/robots.txt', '/favicon.ico', '/favicon.svg', '/site.webmanifest'], true)
    || preg_match('~^/assets/[^\x00]+\.(?:css|js|woff2|avif|webp|jpg|jpeg|png|svg|ico)$~i', $path);
if ($static && !str_contains($path, '\\')) {
    $file = realpath($webroot . $path);
    if ($file !== false && str_starts_with($file, $webroot . DIRECTORY_SEPARATOR) && is_file($file)) return false;
}
require $webroot . '/index.php';
