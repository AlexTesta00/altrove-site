<?php
declare(strict_types=1);
use Altrove\{Backup, Csrf, Log, ParticipationPlans, WhatsAppMessage};

$isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
$respond = static function (int $status, string $error = '') use ($isJson): never {
    if ($isJson) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>$status < 300, 'error'=>$error, 'redirect'=>$status < 300 ? '/grazie/' : null], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    } elseif (session_status() === PHP_SESSION_ACTIVE && $status !== 405) {
        if ($error !== '') $_SESSION['plan_error'] = ['message'=>$error, 'expires'=>time()+900];
        header('Location: ' . ($status < 300 ? '/grazie/' : '/proposte/#scelta-piano'), true, 303);
    } else {
        http_response_code($status);
        echo '<!doctype html><html lang="it"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Altrove · La tua proposta</title><main><h1>Controlla la tua proposta</h1><p>'.e($error).'</p><a href="/candidati/">Torna alla candidatura</a></main></html>';
    }
    exit;
};
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); $respond(405, 'Scegli il piano dal modulo di Altrove.'); }
if (strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])) !== 'application/x-www-form-urlencoded') $respond(415, 'Il formato del modulo non è valido.');
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 4096) $respond(413, 'La richiesta è troppo lunga.');
$body = file_get_contents('php://input', false, null, 0, 4097);
if ($body === false || strlen($body) > 4096) $respond(413, 'La richiesta è troppo lunga.');
$source = parse_url($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
$expected = parse_url($config['url']);
$origin = static fn(array $url): string => strtolower(($url['scheme'] ?? '').'://'.($url['host'] ?? '')).':'.($url['port'] ?? (($url['scheme'] ?? '') === 'https' ? 443 : 80));
if (!is_array($source) || !is_array($expected) || isset($source['user']) || $origin($source) !== $origin($expected)) $respond(403, 'Apri il modulo dal sito Altrove e riprova.');
start_session($config);
parse_str($body, $input);
if (!Csrf::valid(is_string($input['csrf'] ?? null) ? $input['csrf'] : '')) $respond(419, 'La pagina è scaduta. Ricaricala e scegli nuovamente il piano.');
$application = $_SESSION['application'] ?? null;
if (!is_array($application) || !is_array($application['data'] ?? null) || ($application['expires'] ?? 0) <= time()) $respond(410, 'La candidatura è scaduta. Riparti dal modulo per proteggere i tuoi dati.');
if (!is_string($input['candidatura'] ?? null) || !hash_equals($application['id'], $input['candidatura'])) $respond(409, 'La candidatura è cambiata in un’altra scheda. Ricarica la pagina e controlla la scelta.');
$plan = ParticipationPlans::find($input['piano'] ?? null);
if ($plan === null) $respond(422, 'Scegli uno dei tre piani prima di preparare il messaggio.');
if ($config['whatsapp'] === '') $respond(503, 'Il contatto WhatsApp non è disponibile. Riprova tra poco.');
// Il browser invia soltanto l'identificativo: prezzo e contenuti vengono dal catalogo server.
$data = $application['data'];
$data['piano'] = $input['piano'];
$data['proposta'] = ['nome'=>$plan['name'], 'importo_finale_eur'=>$plan['price'], 'contenuti'=>$plan['items']];
$data['stato'] = 'preparata_per_whatsapp_non_inviata';
$backupSaved = false;
if ($application['plan'] !== $data['piano'] && $config['backup_key'] !== '') $backupSaved = Backup::save($config, $data);
$_SESSION['application']['plan'] = $data['piano'];
$_SESSION['handoff'] = WhatsAppMessage::build($data) + ['expires'=>$application['expires'], 'backup'=>$backupSaved];
unset($_SESSION['plan_error']);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
Log::write($config, 'prepared');
$respond(200);
