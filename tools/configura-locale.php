<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
if (is_file($root.'/.env')) { fwrite(STDERR, ".env esiste già: non lo sovrascrivo.\n"); exit(1); }
$env = file_get_contents($root.'/.env.example');
$env = str_replace(['APP_ENV=production','APP_URL=https://altrove.space','APP_SECRET=','IP_SALT=','WHATSAPP_NUMBER='], ['APP_ENV=local','APP_URL=http://localhost:8000','APP_SECRET='.bin2hex(random_bytes(32)), 'IP_SALT='.bin2hex(random_bytes(32)), 'WHATSAPP_NUMBER=393272433925'], $env);
file_put_contents($root.'/.env', $env, LOCK_EX);
chmod($root.'/.env', 0600);
echo "Creato .env locale, senza invii automatici o email.\n";

