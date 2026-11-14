<?php
declare(strict_types=1);
namespace Altrove;
final class Turnstile {
    public static function verify(array $config, string $token): bool {
        if (!$config['turnstile']) return true;
        if ($token === '' || strlen($token) > 2048) return false;
        $curl = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['secret' => $config['turnstile_secret'], 'response' => $token]), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        $result = is_string($response) ? json_decode($response, true) : null;
        return $status === 200 && is_array($result) && ($result['success'] ?? false) === true && ($result['hostname'] ?? '') === parse_url($config['url'], PHP_URL_HOST) && ($result['action'] ?? '') === 'candidatura';
    }
}

