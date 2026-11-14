<?php
declare(strict_types=1);
use Altrove\{Backup, Csrf, Log, RateLimit, Router, Turnstile, Validator, WhatsAppMessage};

$isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
$respond = static function (int $status, array $errors = [], array $old = []) use ($isJson, $config): never {
    if ($isJson) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>$status < 300, 'errors'=>$errors, 'redirect'=>$status < 300 ? '/grazie/' : null, 'copyRequired'=>$_SESSION['handoff']['copy_required']??false, 'whatsapp'=>$status < 300 && isset($_SESSION['handoff']['text']) ? whatsapp($config, $_SESSION['handoff']['text']) : null], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    } elseif (session_status() !== PHP_SESSION_ACTIVE || $status === 405) {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="it"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Altrove · Controlla il modulo</title><main><h1>Controlla il modulo</h1><p>' . e(implode(' ', $errors)) . '</p><a href="/candidati/">Torna alla candidatura</a></main></html>';
    } else {
        if (session_status() === PHP_SESSION_ACTIVE && $status >= 300) $_SESSION['flash'] = ['errors'=>$errors, 'old'=>$old, 'expires'=>time()+900];
        header('Location: ' . ($status < 300 ? '/grazie/' : '/candidati/#candidatura'), true, 303);
    }
    exit;
};
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); $respond(405, ['form'=>'Questo indirizzo accetta soltanto il modulo di candidatura.']); }
$contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
if ($contentType !== 'application/x-www-form-urlencoded') $respond(415, ['form'=>'Il formato del modulo non è valido. Ricarica la pagina e riprova.']);
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) $respond(413, ['form'=>'Il testo è troppo lungo. Accorcialo e riprova.']);
$body = file_get_contents('php://input', false, null, 0, 65537);
if ($body === false || strlen($body) > 65536) $respond(413, ['form'=>'Il testo è troppo lungo. Accorcialo e riprova.']);
$source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
$sourceParts = parse_url($source);
$expectedParts = parse_url($config['url']);
$normalizeOrigin = static fn(array $url): string => strtolower(($url['scheme'] ?? '') . '://' . ($url['host'] ?? '')) . ':' . ($url['port'] ?? (($url['scheme'] ?? '') === 'https' ? 443 : 80));
if (!is_array($sourceParts) || !is_array($expectedParts) || isset($sourceParts['user']) || $normalizeOrigin($sourceParts) !== $normalizeOrigin($expectedParts)) $respond(403, ['form'=>'Apri il modulo dal sito Altrove e riprova.']);
start_session($config);
parse_str($body, $input);
if (!is_array($input)) $input = [];
// Non conservare array, valori senza limite o campi inattesi nella sessione.
$old = [];
foreach (['nome','cognome','telefono','esperienza','origine','veste','tema','consenso'] as $key) $old[$key] = is_string($input[$key] ?? null) ? mb_substr($input[$key], 0, $key === 'esperienza' ? 4000 : 100) : '';
$string = static fn(string $name): string => is_string($input[$name] ?? null) ? $input[$name] : '';
if (strlen($config['secret']) < 32 || strlen($config['ip_salt']) < 32) $respond(503, ['form'=>'Il modulo è momentaneamente in pausa. Riprova tra poco: i dati restano qui.'], $old);
if (!Csrf::valid($string('csrf'))) $respond(419, ['form'=>'La pagina è scaduta. I tuoi dati sono qui: ricarica il modulo e riprova.'], $old);
if ($string('sito_web') !== '') {
    $_SESSION['handoff'] = ['text'=>'Ciao Alessia, vorrei presentare la mia candidatura per Altrove.', 'expires'=>time()+900];
    $respond(200);
}
if (!Csrf::validStamp($string('stamp'), $config['secret'])) $respond(422, ['form'=>'Aspetta qualche secondo prima di inviare. Se la pagina è aperta da molto, ricaricala e riprova.'], $old);
try { $allowed = RateLimit::allow($config['storage'].'/ratelimit', $_SERVER['REMOTE_ADDR'] ?? '', $config['ip_salt']); }
catch (Throwable) { Log::write($config, 'rate_storage_failed'); $respond(503, ['form'=>'Non riesco a verificare il modulo. Riprova tra poco: i dati restano qui.'], $old); }
if (!$allowed) { header('Retry-After: 600'); $respond(429, ['form'=>'Hai già fatto diversi tentativi. Aspetta qualche minuto prima di riprovare.'], $old); }
if (!Turnstile::verify($config, $string('cf-turnstile-response'))) $respond(422, ['form'=>'La verifica anti-spam è scaduta o non è riuscita. Ripetila e riprova.'], $old);
[$data, $errors] = Validator::validate($input, Router::ORIGINS);
if ($errors) $respond(422, $errors, $old);
if ($config['whatsapp'] === '') $respond(503, ['form'=>'Il contatto WhatsApp non è ancora disponibile. Riprova più tardi.'], $old);
$data['data'] = date('c');
$data['informativa'] = '2026-11-14';
$data['stato'] = 'preparata_per_whatsapp_non_inviata';
$backupSaved = $config['backup_key'] !== '' && Backup::save($config, $data);
$_SESSION['handoff'] = WhatsAppMessage::build($data) + ['expires'=>time()+900, 'backup'=>$backupSaved];
unset($_SESSION['flash']);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
session_regenerate_id(true);
Log::write($config, 'prepared');
$respond(200);

