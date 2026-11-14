<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/lib/Backup.php';
$config = require __DIR__ . '/config.php';
Altrove\Backup::purge($config['storage']);
foreach (glob($config['storage'].'/logs/*.log') ?: [] as $file) if (filemtime($file)<time()-30*86400) @unlink($file);
echo "Pulizia dei dati scaduti completata.\n";

