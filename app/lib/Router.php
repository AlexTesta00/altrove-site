<?php
declare(strict_types=1);
namespace Altrove;
final class Router {
    public const ROUTES = [
        '/' => 'home', '/esperienze-pre-morte/' => 'nde', '/medium-e-sensitivi/' => 'medium',
        '/channeling-e-coscienza-stellare/' => 'channeling', '/mentalismo-e-ipnosi/' => 'mentalismo',
        '/alessia-ventura/' => 'alessia', '/candidati/' => 'candidati',
        '/privacy/' => 'privacy', '/cookie/' => 'cookie', '/proposte/' => 'proposte', '/grazie/' => 'grazie'
    ];
    public const ORIGINS = ['home', 'esperienze-pre-morte', 'medium-e-sensitivi', 'channeling-e-coscienza-stellare', 'mentalismo-e-ipnosi', 'alessia-ventura', 'candidati'];
    public static function origin(mixed $value): string { return is_string($value) && in_array($value, self::ORIGINS, true) ? $value : 'candidati'; }
    public static function sitemap(array $config): string {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach (self::ROUTES as $path => $id) {
            if (in_array($id, ['grazie', 'proposte'], true)) continue;
            $source = $config['root'] . '/content/' . $id . '.php';
            $modified = date('Y-m-d', is_file($source) ? filemtime($source) : filemtime($config['root'] . '/content/pages.php'));
            $xml .= '<url><loc>' . htmlspecialchars($config['url'] . $path, ENT_XML1) . '</loc><lastmod>' . $modified . '</lastmod></url>';
        }
        return $xml . '</urlset>';
    }
}

