<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('log_errors', '0'); // Solo codici controllati: gli errori runtime possono contenere dati personali.
error_reporting(E_ALL);
date_default_timezone_set('Europe/Rome');
$config = require __DIR__ . '/config.php';
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Altrove\\')) {
        $file = __DIR__ . '/lib/' . substr($class, 8) . '.php';
        if (is_file($file)) require $file;
    }
});
if (is_file(__DIR__ . '/vendor/autoload.php')) require __DIR__ . '/vendor/autoload.php';
foreach (['ratelimit', 'candidature', 'cache', 'logs', 'sessions'] as $directory) {
    $path = $config['storage'] . '/' . $directory;
    if (!is_dir($path)) @mkdir($path, 0700, true);
}
set_exception_handler(static function (Throwable $exception) use ($config): void {
    Altrove\Log::write($config, 'runtime_error');
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo '<!doctype html><html lang="it"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Altrove · Riprova tra poco</title><main><h1>Un momento di pausa</h1><p>Non riesco a completare la richiesta. Riprova tra poco.</p><a href="/">Torna ad Altrove</a></main></html>';
});
$nonce = base64_encode(random_bytes(24));
Altrove\SecurityHeaders::send($config, $nonce);
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function whatsapp(array $config, string $message = 'Ciao Alessia, vorrei sapere di più su Altrove.'): string {
    return $config['whatsapp'] === '' ? '' : 'https://wa.me/' . $config['whatsapp'] . '?text=' . rawurlencode($message);
}
function start_session(array $config): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '7200');
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
    session_save_path($config['storage'] . '/sessions');
    session_name($config['local'] ? 'altrove_locale' : '__Host-altrove');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => !$config['local'], 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
    if (isset($_SESSION['touched']) && $_SESSION['touched'] < time() - 7200) { $_SESSION = []; session_regenerate_id(true); }
    foreach (['handoff', 'flash'] as $temporary) {
        if (isset($_SESSION[$temporary]) && ($_SESSION[$temporary]['expires'] ?? 0) < time()) unset($_SESSION[$temporary]);
    }
    $_SESSION['touched'] = time();
}

