<?php
declare(strict_types=1);
namespace Altrove;
final class Csrf {
    public static function token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
    public static function valid(string $token): bool { return isset($_SESSION['csrf']) && $token !== '' && hash_equals($_SESSION['csrf'], $token); }
    public static function stamp(string $secret, ?int $now = null): string {
        $time = (string)($now ?? time());
        return $time . '.' . hash_hmac('sha256', $time . ':' . self::token(), $secret);
    }
    public static function validStamp(string $stamp, string $secret, ?int $now = null): bool {
        if (!preg_match('/^([0-9]{10})\.([a-f0-9]{64})$/D', $stamp, $m)) return false;
        $age = ($now ?? time()) - (int)$m[1];
        return $age >= 3 && $age <= 7200 && hash_equals(hash_hmac('sha256', $m[1] . ':' . self::token(), $secret), $m[2]);
    }
}

