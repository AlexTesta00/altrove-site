<?php
declare(strict_types=1);
$appRoot = __DIR__;
$fallback = basename($appRoot) === '_app';
$envFile = getenv('ALTROVE_ENV_FILE') ?: dirname($appRoot, $fallback ? 2 : 1) . '/.env';
$env = [];
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) $value = substr($value, 1, -1);
        if (preg_match('/^[A-Z][A-Z0-9_]*$/D', trim($key))) $env[trim($key)] = $value;
    }
}
$get = static fn(string $name, string $default = ''): string => (($v = getenv($name)) !== false ? $v : ($env[$name] ?? $default));
$url = rtrim($get('APP_URL', 'https://altrove.space'), '/');
$local = $get('APP_ENV', 'production') === 'local' && in_array(parse_url($url, PHP_URL_HOST), ['localhost', '127.0.0.1', '[::1]'], true);
$umami = $get('UMAMI_SRC');
$umamiOrigin = '';
if (preg_match('~^https://[a-zA-Z0-9.-]+(?::[0-9]+)?/[^\s"<>]*$~D', $umami)) {
    $parts = parse_url($umami);
    $umamiOrigin = 'https://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
}
return [
    'root' => $appRoot, 'storage' => $appRoot . '/storage', 'url' => $url, 'local' => $local,
    'secret' => $get('APP_SECRET'), 'ip_salt' => $get('IP_SALT'),
    'whatsapp' => preg_match('/^[1-9][0-9]{7,14}$/D', $get('WHATSAPP_NUMBER')) ? $get('WHATSAPP_NUMBER') : '',
    'turnstile_site' => $get('TURNSTILE_SITE_KEY'), 'turnstile_secret' => $get('TURNSTILE_SECRET'),
    'turnstile' => $get('TURNSTILE_SITE_KEY') !== '' && $get('TURNSTILE_SECRET') !== '',
    'backup_key' => $get('BACKUP_PUBLIC_KEY'),
    'youtube' => preg_match('/^UC[A-Za-z0-9_-]{22}$/D', $get('YOUTUBE_CHANNEL_ID')) ? $get('YOUTUBE_CHANNEL_ID') : '',
    'umami_id' => $umamiOrigin !== '' && preg_match('/^[a-f0-9-]{36}$/iD', $get('UMAMI_WEBSITE_ID')) ? $get('UMAMI_WEBSITE_ID') : '',
    'umami_src' => $umamiOrigin !== '' ? $umami : '', 'umami_origin' => $umamiOrigin,
    'owner' => ['name' => $get('TITOLARE_NOME', 'Alessia Ventura'), 'vat' => $get('TITOLARE_PIVA', '07446010485'), 'email' => filter_var($get('TITOLARE_EMAIL'), FILTER_VALIDATE_EMAIL) ? $get('TITOLARE_EMAIL') : ''],
];

