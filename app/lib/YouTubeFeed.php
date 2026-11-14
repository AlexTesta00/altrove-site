<?php
declare(strict_types=1);
namespace Altrove;
final class YouTubeFeed {
    public static function get(array $config, array $keywords = []): array {
        if ($config['youtube'] === '' || !function_exists('curl_init')) return [];
        $file = $config['storage'] . '/cache/youtube.json';
        $cache = is_file($file) ? json_decode(file_get_contents($file), true) : null;
        $items = is_array($cache['items'] ?? null) ? $cache['items'] : [];
        $attemptFile = $config['storage'] . '/cache/youtube.attempt';
        $lastAttempt = is_file($attemptFile) ? (int)filemtime($attemptFile) : 0;
        if ((!is_array($cache) || ($cache['fetched'] ?? 0) < time() - 21600) && $lastAttempt < time() - 600) {
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
                    if ($ok === false || $status !== 200 || stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) throw new \RuntimeException('feed');
                    $previous = libxml_use_internal_errors(true);
                    try { $xml = simplexml_load_string($body, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA); }
                    finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
                    if ($xml === false) throw new \RuntimeException('feed');
                    $fresh = [];
                    foreach ($xml->entry as $entry) {
                        $yt = $entry->children('http://www.youtube.com/xml/schemas/2015');
                        $id = (string)$yt->videoId;
                        if (!preg_match('/^[A-Za-z0-9_-]{11}$/D', $id)) continue;
                        $date = strtotime((string)$entry->published);
                        $fresh[] = ['id'=>$id, 'title'=>(string)$entry->title, 'date'=>$date ? date('Y-m-d', $date) : ''];
                    }
                    $items = $fresh;
                    $tmp = $file . '.tmp';
                    if (file_put_contents($tmp, json_encode(['fetched'=>time(),'items'=>$items], JSON_THROW_ON_ERROR), LOCK_EX) !== false) rename($tmp, $file);
                } catch (\Throwable) { Log::write($config, 'feed_failed'); }
                finally { flock($lock, LOCK_UN); fclose($lock); }
            } elseif ($lock) fclose($lock);
        }
        $items = array_filter($items, static fn($item) => is_array($item) && is_string($item['id'] ?? null) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $item['id']) && is_string($item['title'] ?? null) && is_string($item['date'] ?? null));
        if ($keywords) $items = array_filter($items, static function ($item) use ($keywords) { foreach ($keywords as $word) if (mb_stripos($item['title'], $word) !== false) return true; return false; });
        return array_slice(array_values($items), 0, 6);
    }
}

