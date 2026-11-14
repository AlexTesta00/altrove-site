<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
if (count($argv) !== 4) { fwrite(STDERR, "Uso: php tools/leggi-candidature.php CHIAVE_PRIVATA FILE.seal OUTPUT.json\n"); exit(1); }
if (file_exists($argv[3])) { fwrite(STDERR, "Il file di destinazione esiste già.\n"); exit(1); }
try {
    $key = base64_decode(trim(file_get_contents($argv[1])), true);
    if ($key === false || strlen($key) !== SODIUM_CRYPTO_BOX_KEYPAIRBYTES) throw new RuntimeException();
    $plain = sodium_crypto_box_seal_open(file_get_contents($argv[2]), $key);
    if ($plain === false) throw new RuntimeException();
    json_decode($plain, true, 512, JSON_THROW_ON_ERROR);
    $out = fopen($argv[3], 'xb');
    fwrite($out, $plain);
    fclose($out);
    chmod($argv[3], 0600);
    sodium_memzero($key);
    echo "Copia decifrata nel file locale indicato. Proteggilo ed eliminalo quando non serve più.\n";
} catch (Throwable) { fwrite(STDERR, "Impossibile decifrare la copia con la chiave fornita.\n"); exit(1); }

