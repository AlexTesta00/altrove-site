<?php
declare(strict_types=1);
namespace Altrove;
final class Log {
    public static function write(array $config, string $event): void {
        $allowed = ['runtime_error', 'mail_failed', 'backup_failed', 'received', 'prepared', 'rate_storage_failed', 'feed_failed', 'turnstile_failed'];
        if (!in_array($event, $allowed, true)) return;
        @file_put_contents($config['storage'] . '/logs/' . date('Y-m-d') . '.log', gmdate('c') . ' ' . $event . "\n", FILE_APPEND | LOCK_EX);
        foreach (glob($config['storage'] . '/logs/*.log') ?: [] as $file) if (filemtime($file) < time() - 30 * 86400) @unlink($file);
    }
}

