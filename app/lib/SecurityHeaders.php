<?php
declare(strict_types=1);
namespace Altrove;
final class SecurityHeaders {
    public static function send(array $config, string $nonce): void {
        $scripts = "'self' 'nonce-$nonce'";
        $frames = 'https://www.youtube-nocookie.com';
        $connect = "'self'";
        if ($config['turnstile']) { $scripts .= ' https://challenges.cloudflare.com'; $frames .= ' https://challenges.cloudflare.com'; $connect .= ' https://challenges.cloudflare.com'; }
        if ($config['umami_id'] !== '') { $scripts .= ' ' . $config['umami_origin']; $connect .= ' ' . $config['umami_origin']; }
        header("Content-Security-Policy: default-src 'self'; script-src $scripts; style-src 'self' 'nonce-$nonce'; font-src 'self'; img-src 'self' data: https://i.ytimg.com; connect-src $connect; frame-src $frames; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'" . ($config['local'] ? '' : '; upgrade-insecure-requests'));
        if (!$config['local']) header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cache-Control: no-store');
        header('Content-Type: text/html; charset=utf-8');
    }
}

