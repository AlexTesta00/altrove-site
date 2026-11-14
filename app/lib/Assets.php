<?php
declare(strict_types=1);
namespace Altrove;
final class Assets {
    private static ?array $manifest = null;
    public static function get(string $name): string {
        if (self::$manifest === null) {
            $file = ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public') . '/assets/build/manifest.json';
            self::$manifest = is_file($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
        }
        $path = self::$manifest[$name] ?? '';
        return preg_match('~^/assets/build/[A-Za-z0-9._/-]+$~D', $path) ? $path : '';
    }
}

