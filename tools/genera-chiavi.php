<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
if (!extension_loaded('sodium')) { fwrite(STDERR, "Abilita l’estensione sodium.\n"); exit(1); }
$directory = $argv[1] ?? '';
if ($directory === '' || !is_dir($directory)) { fwrite(STDERR, "Uso: php tools/genera-chiavi.php CARTELLA_PRIVATA_LOCALE\n"); exit(1); }
$privatePath = rtrim($directory, '/\\') . '/altrove-private.key';
if (file_exists($privatePath)) { fwrite(STDERR, "La chiave privata esiste già: non la sovrascrivo.\n"); exit(1); }
$keypair = sodium_crypto_box_keypair();
$handle = fopen($privatePath, 'xb');
fwrite($handle, base64_encode($keypair));
fclose($handle);
chmod($privatePath, 0600);
echo "BACKUP_PUBLIC_KEY=" . base64_encode(sodium_crypto_box_publickey($keypair)) . "\n";
echo "Chiave privata salvata localmente. Non caricarla sul server o su GitHub.\n";
sodium_memzero($keypair);

