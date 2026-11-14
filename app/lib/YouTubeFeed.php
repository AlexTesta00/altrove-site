<?php
declare(strict_types=1);
namespace Altrove;
final class YouTubeFeed {
    public static function get(array $config, array $keywords = []): array {
        $items = array_filter(self::load($config), static fn($item) => !$item['short']);
        if ($keywords) $items = array_filter($items, static function ($item) use ($keywords) { foreach ($keywords as $word) if (is_string($word) && $word !== '' && preg_match('/' . preg_quote($word, '/') . '/iu', $item['title']) === 1) return true; return false; });
        return array_slice(array_values($items), 0, 6);
    }

    /** Più visti tra i video presenti nel feed corrente, non nell'intero canale. */
    public static function getPopular(array $config, int $limit = 5): array {
        $items = array_values(array_filter(self::load($config), static fn($item) => !$item['short']));
        usort($items, static function ($a, $b) {
            // Un dato assente resta ignoto: viene dopo anche a uno zero verificato.
            if ($a['views'] === null && $b['views'] !== null) return 1;
            if ($b['views'] === null && $a['views'] !== null) return -1;
            return (($b['views'] ?? -1) <=> ($a['views'] ?? -1)) ?: strcmp($b['date'], $a['date']) ?: strcmp($a['id'], $b['id']);
        });
        return array_slice($items, 0, max(0, min(15, $limit)));
    }

    /** Parser condiviso con il bootstrap CLI, senza rete o accesso al filesystem. */
    public static function parse(string $body): array {
        if (strlen($body) > 524288 || stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) throw new \RuntimeException('feed');
        $previous = libxml_use_internal_errors(true);
        try { $xml = simplexml_load_string($body, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        if ($xml === false || $xml->getName() !== 'feed') throw new \RuntimeException('feed');
        $items = [];
        foreach ($xml->entry as $entry) {
            $yt = $entry->children('http://www.youtube.com/xml/schemas/2015');
            $id = (string)$yt->videoId;
            if (!preg_match('/^[A-Za-z0-9_-]{11}$/D', $id)) continue;
            $date = strtotime((string)$entry->published);
            $media = $entry->children('http://search.yahoo.com/mrss/');
            $statistics = $media->group->community->statistics;
            $views = isset($statistics[0]) ? (string)($statistics->attributes()['views'] ?? '') : '';
            $short = false;
            foreach ($entry->link as $link) {
                if ((string)$link['rel'] === 'alternate') {
                    $path = parse_url((string)$link['href'], PHP_URL_PATH);
                    $short = is_string($path) && str_starts_with($path, '/shorts/');
                    break;
                }
            }
            $items[] = ['id'=>$id, 'title'=>(string)$entry->title, 'date'=>$date ? date('Y-m-d', $date) : '', 'views'=>self::views($views), 'short'=>$short];
        }
        return $items;
    }

    private static function views(mixed $value): ?int {
        if (is_int($value)) return $value >= 0 ? $value : null;
        if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]*)$/D', $value)) return null;
        $validated = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]);
        return $validated === false ? null : $validated;
    }

    private static function load(array $config): array {
        if ($config['youtube'] === '') return [];
        $file = $config['storage'] . '/cache/youtube.json';
        $cache = is_file($file) ? json_decode(file_get_contents($file), true) : null;
        $items = is_array($cache['items'] ?? null) ? $cache['items'] : [];
        $attemptFile = $config['storage'] . '/cache/youtube.attempt';
        $lastAttempt = is_file($attemptFile) ? (int)filemtime($attemptFile) : 0;
        if (function_exists('curl_init') && (!is_array($cache) || ($cache['fetched'] ?? 0) < time() - 21600) && $lastAttempt < time() - 600) {
            $lock = @fopen($config['storage'] . '/cache/youtube.lock', 'c');
            if ($lock && flock($lock, LOCK_EX | LOCK_NB)) {
                try {
                    // Un guasto remoto non deve rallentare ogni visita successiva.
                    if (!touch($attemptFile)) throw new \RuntimeException('feed_attempt');
                    $curl = curl_init('https://www.youtube.com/feeds/videos.xml?channel_id=' . $config['youtube']);
                    $body = '';
                    curl_setopt_array($curl, [CURLOPT_TIMEOUT_MS => 1500, CURLOPT_CONNECTTIMEOUT_MS => 750, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_USERAGENT => 'AltroveFeed/1.0', CURLOPT_WRITEFUNCTION => static function ($ch, $chunk) use (&$body) { if (strlen($body) + strlen($chunk) > 524288) return 0; $body .= $chunk; return strlen($chunk); }]);
                    $ok = curl_exec($curl);
                    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
                    curl_close($curl);
                    if ($ok === false || $status !== 200) throw new \RuntimeException('feed');
                    $items = self::parse($body);
                    $tmp = $file . '.tmp';
                    if (file_put_contents($tmp, json_encode(['fetched'=>time(),'items'=>$items], JSON_THROW_ON_ERROR), LOCK_EX) !== false) rename($tmp, $file);
                } catch (\Throwable) { Log::write($config, 'feed_failed'); }
                finally { flock($lock, LOCK_UN); fclose($lock); }
            } elseif ($lock) fclose($lock);
        }
        $items = array_filter($items, static fn($item) => is_array($item) && is_string($item['id'] ?? null) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $item['id']) && is_string($item['title'] ?? null) && is_string($item['date'] ?? null));
        return array_values(array_map(static fn($item) => ['id'=>$item['id'], 'title'=>$item['title'], 'date'=>$item['date'], 'views'=>self::views($item['views'] ?? null), 'short'=>($item['short'] ?? false) === true], $items));
    }
}

