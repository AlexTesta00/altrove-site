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

    /**
     * Indirizzo di un'immagine generata dalla build, con una versione che cambia
     * quando cambia il file: browser e CDN tengono le immagini 7 giorni, così una
     * foto sostituita viene scaricata subito invece di restare quella vecchia.
     */
    public static function image(string $file): string {
        if (!preg_match('~^[A-Za-z0-9._-]+\.(?:avif|webp|jpe?g|png)$~D', $file)) {
            return '';
        }
        $path = ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public') . '/assets/images/' . $file;
        $version = is_file($path) ? filemtime($path) : false;
        return '/assets/images/' . $file . ($version ? '?v=' . $version : '');
    }
}

