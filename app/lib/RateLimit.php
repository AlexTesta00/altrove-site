<?php
declare(strict_types=1);
namespace Altrove;
final class RateLimit {
    public static function allow(string $directory, string $ip, string $salt, ?int $now = null): bool {
        $now ??= time();
        $file = $directory . '/' . hash_hmac('sha256', $ip, $salt) . '.json';
        $handle = @fopen($file, 'c+');
        if (!$handle || !flock($handle, LOCK_EX)) { if ($handle) fclose($handle); throw new \RuntimeException('rate_storage_failed'); }
        try {
            $raw = stream_get_contents($handle);
            $events = json_decode($raw ?: '[]', true);
            if (!is_array($events)) throw new \RuntimeException('rate_storage_failed');
            $events = array_values(array_filter($events, static fn($time) => is_int($time) && $time > $now - 86400 && $time <= $now));
            $allowed = count($events) < 10 && count(array_filter($events, static fn($time) => $time > $now - 600)) < 3;
            if ($allowed) $events[] = $now;
            rewind($handle);
            if (!ftruncate($handle, 0) || fwrite($handle, json_encode($events, JSON_THROW_ON_ERROR)) === false || !fflush($handle)) throw new \RuntimeException('rate_storage_failed');
            @chmod($file, 0600);
            return $allowed;
        } finally { flock($handle, LOCK_UN); fclose($handle); }
    }
}

