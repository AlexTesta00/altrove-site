<?php
declare(strict_types=1);
namespace Altrove;
final class Backup {
    public static function save(array $config, array $data): bool {
        try {
            $key = base64_decode($config['backup_key'], true);
            if ($key === false || strlen($key) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) throw new \RuntimeException('backup_key');
            $encrypted = sodium_crypto_box_seal(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), $key);
            $file = $config['storage'] . '/candidature/' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(16)) . '.seal';
            $handle = fopen($file, 'xb');
            if (!$handle) throw new \RuntimeException('backup_open');
            try { if (fwrite($handle, $encrypted) !== strlen($encrypted) || !fflush($handle)) throw new \RuntimeException('backup_write'); }
            finally { fclose($handle); }
            chmod($file, 0600);
            self::purge($config['storage']);
            return true;
        } catch (\Throwable) { Log::write($config, 'backup_failed'); return false; }
    }
    public static function purge(string $storage): int {
        $removed = 0;
        foreach (glob($storage . '/candidature/*.seal') ?: [] as $file) if (filemtime($file) < time() - 90 * 86400 && @unlink($file)) $removed++;
        foreach (glob($storage . '/ratelimit/*.json') ?: [] as $file) if (filemtime($file) < time() - 2 * 86400) @unlink($file);
        foreach (glob($storage . '/sessions/sess_*') ?: [] as $file) if (filemtime($file) < time() - 7200) @unlink($file);
        return $removed;
    }
}

