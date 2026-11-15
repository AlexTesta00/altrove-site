<?php
declare(strict_types=1);
foreach (['Validator','Csrf','RateLimit','Backup','Router','Log','Privacy','ParticipationPlans','WhatsAppMessage'] as $class) require dirname(__DIR__).'/app/lib/'.$class.'.php';
use Altrove\{Validator,Csrf,RateLimit,Backup,Router};
$count=0;
$assert=static function(bool $result,string $message) use(&$count): void { if(!$result)throw new RuntimeException($message); $count++; };
$directory=dirname(__DIR__).'/.runtime/unit-'.bin2hex(random_bytes(5));
foreach(['ratelimit','candidature','logs','sessions'] as $part)mkdir($directory.'/'.$part,0700,true);
try {
    $input=['nome'=>"E\u{0301}lise",'cognome'=>"D’Amico",'telefono'=>'333 123 4567','esperienza'=>str_repeat('Una storia di prova. ',4),'origine'=>'home','veste'=>'esperienza','tema'=>'nde','consenso'=>'1'];
    [$data,$errors]=Validator::validate($input,Router::ORIGINS);
    $assert($errors===[],'Dati validi rifiutati');
    $assert($data['nome']==='Élise','NFC non applicata');
    $assert($data['telefono']==='+393331234567','Normalizzazione E164 errata');
    $data['piano']='completo';
    $message=Altrove\WhatsAppMessage::build($data);
    $assert(str_contains($message['text'],$data['esperienza']),'Storia troncata nel messaggio');
    $assert(str_contains($message['text'],Altrove\Privacy::CONSENT),'Consenso esplicito assente nel messaggio consegnato alla persona');
    $assert(str_contains($message['text'],Altrove\Privacy::VERSION),'Versione informativa assente dal messaggio');
    $assert(!str_contains($message['text'],'appena inviato'),'Il messaggio dichiara un invio inesistente');
    $assert($message['blocks'][4]['value']==='Esperienze di pre-morte','Etichetta tema non leggibile');
    foreach (['essenziale'=>[200,1,false],'completo'=>[250,1,true],'amplifica'=>[350,3,true]] as $id=>[$price,$reels,$spotify]) {
        $planMessage=Altrove\WhatsAppMessage::build(array_replace($data,['piano'=>$id,'prezzo'=>1,'proposta'=>['nome'=>'Inventato']]));
        $assert(str_contains($planMessage['text'],$price.' € (importo finale)'),'Importo del catalogo non incluso');
        $assert(str_contains($planMessage['text'],$reels.' Reel'),'Numero di Reel errato');
        $assert(str_contains($planMessage['text'],'Spotify')===$spotify,'Canali del piano errati');
        $assert(!str_contains($planMessage['text'],'Inventato'),'Contenuti manipolati accettati');
    }
    foreach ([null,[], 'inesistente'] as $id) {
        $assert(Altrove\ParticipationPlans::find($id)===null,'Piano non valido accettato');
        try { Altrove\WhatsAppMessage::build(array_replace($data,['piano'=>$id])); $rejected=false; }
        catch (InvalidArgumentException) { $rejected=true; }
        $assert($rejected,'Messaggio costruito senza una scelta valida');
    }
    $long=$data;
    $long['esperienza']=str_repeat('🌙漢é ',1000);
    $longMessage=Altrove\WhatsAppMessage::build($long);
    $assert($longMessage['copy_required']===true,'Fallback copia assente per URL lungo');
    $assert(str_contains($longMessage['text'],$long['esperienza']),'Storia Unicode lunga troncata');
    [$bad,$errors]=Validator::validate(['nome'=>['x'],'telefono'=>['x'],'esperienza'=>"\x00",'consenso'=>[]],Router::ORIGINS);
    $assert(isset($errors['nome'],$errors['cognome'],$errors['telefono'],$errors['esperienza'],$errors['consenso']),'Payload array non rifiutato');
    $_SESSION=[];
    $secret=str_repeat('s',64);
    $stamp=Csrf::stamp($secret,1700000000);
    $assert(Csrf::valid(Csrf::token()),'Token valido rifiutato');
    $assert(!Csrf::valid('errato'),'Token errato accettato');
    $assert(!Csrf::validStamp($stamp,$secret,1700000002),'Invio veloce accettato');
    $assert(Csrf::validStamp($stamp,$secret,1700000003),'Tempo minimo errato');
    $assert(!Csrf::validStamp($stamp,$secret,1700007201),'Timestamp scaduto accettato');
    for($i=0;$i<3;$i++)$assert(RateLimit::allow($directory.'/ratelimit','127.0.0.1',$secret,1700000000),'Limite anticipato');
    $assert(!RateLimit::allow($directory.'/ratelimit','127.0.0.1',$secret,1700000000),'Quarto tentativo accettato');
    for($i=3;$i<10;$i++)$assert(RateLimit::allow($directory.'/ratelimit','127.0.0.1',$secret,1700000000+($i-2)*601),'Limite giornaliero anticipato');
    $assert(!RateLimit::allow($directory.'/ratelimit','127.0.0.1',$secret,1700010000),'Undicesimo tentativo accettato');
    $assert(RateLimit::allow($directory.'/ratelimit','127.0.0.1',$secret,1700100000),'Limite non scaduto');
    $pair=sodium_crypto_box_keypair();
    $cfg=['backup_key'=>base64_encode(sodium_crypto_box_publickey($pair)),'storage'=>$directory];
    $assert(Backup::save($cfg,$data),'Backup cifrato fallito');
    $file=glob($directory.'/candidature/*.seal')[0];
    $sealed=file_get_contents($file);
    $assert(!str_contains($sealed,'Élise'),'Dati in chiaro nel backup');
    $decoded=json_decode(sodium_crypto_box_seal_open($sealed,$pair),true,512,JSON_THROW_ON_ERROR);
    $assert($decoded===$data,'Backup non reversibile con chiave privata');
    $other=sodium_crypto_box_keypair();
    $assert(sodium_crypto_box_seal_open($sealed,$other)===false,'Chiave privata errata accettata');
    touch($file,time()-91*86400);
    clearstatcache(true,$file);
    Backup::purge($directory);
    $assert(!is_file($file),'Retention backup non applicata');
    echo "OK: $count verifiche PHP (validazione, CSRF, limiti, cifratura, conservazione).\n";
} finally {
    $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file){if($file->isDir())rmdir($file->getPathname());else unlink($file->getPathname());}
    rmdir($directory);
}

