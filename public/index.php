<?php
declare(strict_types=1);
$appPath = is_dir(dirname(__DIR__) . '/app') ? dirname(__DIR__) . '/app' : __DIR__ . '/_app';
require $appPath . '/bootstrap.php';
use Altrove\Router;
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($path)) $path = '/404/';
if (in_array($path, ['/api/candidatura', '/api/candidatura/', '/api/candidatura.php'], true)) { require $appPath . '/candidatura.php'; exit; }
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) { header('Allow: GET, HEAD'); http_response_code(405); exit; }
if (!$config['local']) {
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $expectedHost = strtolower((string)parse_url($config['url'], PHP_URL_HOST));
    if ($host !== $expectedHost || ($_SERVER['HTTPS'] ?? '') !== 'on') { header('Location: ' . $config['url'] . (isset(Router::ROUTES[$path]) ? $path : '/'), true, 301); exit; }
}
if ($path === '/sitemap.xml') { header('Content-Type: application/xml; charset=utf-8'); echo Router::sitemap($config); exit; }
if ($path !== '/' && !str_ends_with($path, '/') && isset(Router::ROUTES[$path . '/'])) { header('Location: ' . $path . '/', true, 301); exit; }
$id = Router::ROUTES[$path] ?? '404';
if ($id === '404') http_response_code(404);
$pages = require $appPath . '/content/pages.php';
$page = $pages[$id];
$page['id'] = $id;
if (in_array($id, ['nde', 'medium', 'channeling', 'mentalismo'], true)) $page = array_merge($page, require $appPath . '/content/' . $id . '.php');
$common = require $appPath . '/content/common.php';
$origin = $path === '/' ? 'home' : trim($path, '/');
$flash = []; $old = []; $errors = [];
if (in_array($id, ['candidati', 'grazie'], true)) {
    start_session($config);
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    $old = $flash['old'] ?? [];
    $errors = $flash['errors'] ?? [];
    $origin = Router::origin($old['origine'] ?? $_GET['origine'] ?? 'candidati');
}
$episodes = isset($page['episodes']) ? Altrove\YouTubeFeed::get($config, $page['episodes']) : [];
require $appPath . '/templates/layout.php';

