<?php
$value=static fn(string $key):string=>is_string($old[$key]??null)?$old[$key]:'';
$error=static fn(string $key):string=>is_string($errors[$key]??null)?$errors[$key]:'';
$roles=['esperienza'=>["Ho vissuto un'esperienza",'Una NDE, un contatto, un evento che non riesci a spiegare.'],'professionista'=>['Sono un professionista','Medium, sensitivo, channeler, ipnotista, mentalista, operatore olistico.'],'progetto'=>['Ho un progetto da proporre','Un libro, un percorso, un evento, una collaborazione.']];
$themes=['nde'=>['Esperienze di pre-morte','Hai attraversato la soglia e sei tornato.'],'medium'=>['Medium e sensitivi','Senti, vedi, ricevi.'],'channeling'=>['Channeling e coscienza stellare','Guide, energie, memorie lontane.'],'mentalismo'=>['Mentalismo e ipnosi','La mente oltre i suoi confini.'],'altro'=>['Altro','Qualcosa che non sta in nessuna di queste.']];
?>
<section class="panel" aria-label="Candidatura ad Altrove">
<form id="candidatura" action="/api/candidatura" method="post" data-candidatura>
<input type="hidden" name="csrf" value="<?= e(Altrove\Csrf::token()) ?>"><input type="hidden" name="stamp" value="<?= e(Altrove\Csrf::stamp($config['secret'])) ?>"><input type="hidden" name="origine" value="<?= e($origin) ?>">
<div class="honeypot" aria-hidden="true"><label for="sito_web">Lascia vuoto questo campo</label><input type="text" name="sito_web" id="sito_web" tabindex="-1" autocomplete="off"></div>
<div class="error-summary" id="errori-modulo" tabindex="-1" role="status" aria-live="polite"<?= !$errors?' hidden':'' ?>><p><?= e($errors['form']??($errors?'Controlla i campi indicati e riprova.':'')) ?></p><ul><?php foreach($errors as $key=>$message): if($key==='form')continue; ?><li><a href="#<?= e($key) ?>"><?= e($message) ?></a></li><?php endforeach; ?></ul></div>
<p class="step-status" data-step-status hidden aria-live="polite"><span data-step-count>Passo 1 di 5</span><span data-step-percent>20%</span></p>
<div class="scale" data-step-scale hidden aria-hidden="true"><div class="ticks"></div><span class="needle" data-step-needle></span></div>
<fieldset data-step>
<legend class="serif q" tabindex="-1">In che veste ti candidi?</legend><p class="qh">Tocca un'opzione per continuare.</p>
<div class="opts"><?php foreach($roles as $key=>[$label,$description]): ?><label class="opt"><input type="radio" name="veste" id="<?= $key==='esperienza'?'veste':'veste-'.$key ?>" value="<?= e($key) ?>"<?= $value('veste')===$key?' checked':'' ?> required aria-describedby="errore-veste"><span class="ot"><?= e($label) ?></span><span class="od"><?= e($description) ?></span></label><?php endforeach; ?></div><p class="field-error" id="errore-veste" data-error="veste"><?= e($error('veste')) ?></p>
</fieldset>
<fieldset data-step>
<legend class="serif q" tabindex="-1">Di cosa vuoi parlare?</legend><p class="qh">Scegli il tema più vicino alla tua storia.</p>
<div class="opts"><?php foreach($themes as $key=>[$label,$description]): ?><label class="opt"><input type="radio" name="tema" id="<?= $key==='nde'?'tema':'tema-'.$key ?>" value="<?= e($key) ?>"<?= $value('tema')===$key?' checked':'' ?> required aria-describedby="errore-tema"><span class="ot"><?= e($label) ?></span><span class="od"><?= e($description) ?></span></label><?php endforeach; ?></div><p class="field-error" id="errore-tema" data-error="tema"><?= e($error('tema')) ?></p>
</fieldset>
<fieldset data-step>
<legend class="serif q" tabindex="-1">Cosa è successo?</legend><p class="qh" id="aiuto-esperienza">Con le tue parole, da dove vuoi. Racconta soltanto ciò che desideri condividere, senza dati di altre persone.</p>
<div class="wave" aria-hidden="true"><?php for($i=0;$i<36;$i++): ?><span class="bar"></span><?php endfor; ?></div>
<label class="lbl" for="esperienza">La tua esperienza</label><textarea class="fld" name="esperienza" id="esperienza" rows="8" minlength="30" maxlength="8000" placeholder="Cosa è successo, quando, come ti ha cambiato." required aria-describedby="aiuto-esperienza contatore errore-esperienza"><?= e($value('esperienza')) ?></textarea><p id="contatore" class="cnt" data-counter><?= mb_strlen($value('esperienza')) ?> / 4.000 caratteri · almeno 30</p><p class="field-error" id="errore-esperienza" data-error="esperienza"><?= e($error('esperienza')) ?></p>
</fieldset>
<fieldset data-step>
<legend class="serif q" tabindex="-1">Come ti chiami?</legend>
<label class="lbl" for="nome">Nome</label><input class="fld" name="nome" id="nome" autocomplete="given-name" minlength="2" maxlength="60" value="<?= e($value('nome')) ?>" required aria-describedby="errore-nome"><p class="field-error" id="errore-nome" data-error="nome"><?= e($error('nome')) ?></p>
<label class="lbl" for="cognome">Cognome</label><input class="fld" name="cognome" id="cognome" autocomplete="family-name" minlength="2" maxlength="60" value="<?= e($value('cognome')) ?>" required aria-describedby="errore-cognome"><p class="field-error" id="errore-cognome" data-error="cognome"><?= e($error('cognome')) ?></p>
</fieldset>
<fieldset data-step>
<legend class="serif q" tabindex="-1">Dove posso scriverti?</legend><p class="qh">Alla fine si apre WhatsApp con il messaggio già pronto: lo invii tu.</p>
<label class="lbl" for="telefono">Telefono</label><input class="fld" type="tel" inputmode="tel" autocomplete="tel" name="telefono" id="telefono" maxlength="30" value="<?= e($value('telefono')) ?>" placeholder="+39 333 123 4567" required aria-describedby="aiuto-telefono errore-telefono"><p id="aiuto-telefono" class="small">Aggiungi il prefisso internazionale se il numero non è italiano.</p><p class="field-error" id="errore-telefono" data-error="telefono"><?= e($error('telefono')) ?></p>
<label class="ck"><input type="checkbox" id="consenso" name="consenso" value="1" required<?= $value('consenso')==='1'?' checked':'' ?> aria-describedby="errore-consenso"><span><?= e($common['consenso']) ?> Al termine scelgo di inviare la candidatura ad Alessia su WhatsApp.</span></label><a class="privacy-link" href="/privacy/" target="_blank" rel="noopener">Leggi l’informativa privacy <span class="sr-only">(nuova scheda)</span></a><p class="field-error" id="errore-consenso" data-error="consenso"><?= e($error('consenso')) ?></p>
<p class="note"><span class="lamp" aria-hidden="true"></span><span><?= e($common['nota']) ?></span></p><p class="small">Aprendo WhatsApp, il messaggio e i dati che hai scritto saranno comunicati a quel servizio. Il sito non preme Invia al posto tuo.</p>
<?php if($config['turnstile']): ?><div class="cf-turnstile" data-sitekey="<?= e($config['turnstile_site']) ?>" data-action="candidatura" data-theme="dark" data-size="flexible"></div><noscript><p>La verifica anti-spam richiede JavaScript. Attivalo e ricarica questa pagina per proseguire.</p></noscript><?php endif; ?>
<button class="cta form-submit" type="submit" data-submit><span class="disc"><?= icon('arrow') ?></span>Prepara e apri WhatsApp</button><p data-send-status role="status" aria-live="polite"></p>
</fieldset>
<div class="acts" data-step-controls hidden><button class="cta" type="button" data-next disabled><span class="disc"><?= icon('arrow') ?></span>Continua</button><button class="ghost" type="button" data-back><?= icon('back') ?>Indietro</button></div>
</form>
</section>
