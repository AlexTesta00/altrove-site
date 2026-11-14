<?php
declare(strict_types=1);
// Solo sviluppo: il download HTTPS verificato è effettuato dal wrapper PowerShell.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/lib/YouTubeFeed.php';
$source = $argv[1] ?? '';
$channel = $argv[2] ?? '';
$cacheDirectory = dirname(__DIR__) . '/app/storage/cache';
if (!preg_match('/^UC[A-Za-z0-9_-]{22}$/D', $channel) || realpath($source) !== realpath($cacheDirectory . '/youtube-source.xml')) throw new RuntimeException('Sorgente non valida');
$body = file_get_contents($source);
if ($body === false || !str_contains($body, 'https://www.youtube.com/channel/' . $channel)) throw new RuntimeException('Canale inatteso');
$items = Altrove\YouTubeFeed::parse($body);
if (!$items) throw new RuntimeException('Feed vuoto');
$fetched = (int)filemtime($source);
if ($fetched < time() - 300 || $fetched > time() + 60) throw new RuntimeException('Scaricare nuovamente il feed prima del bootstrap');
$payload = ['fetched'=>$fetched, 'source'=>'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channel, 'items'=>$items];
$temporary = $cacheDirectory . '/youtube.json.bootstrap.tmp';
if (file_put_contents($temporary, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), LOCK_EX) === false || !rename($temporary, $cacheDirectory . '/youtube.json')) throw new RuntimeException('Scrittura cache non riuscita');
echo count($items) . " elementi verificati nel feed; " . count(array_filter($items, static fn($item) => !$item['short'])) . " senza link Shorts.\n";
