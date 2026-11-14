<?php
$value = static fn(string $key): string => is_string($old[$key] ?? null) ? $old[$key] : '';
$error = static fn(string $key): string => is_string($errors[$key] ?? null) ? $errors[$key] : '';
?>
<section class="section form-section" aria-labelledby="form-titolo">
<h2 id="form-titolo">Hai una storia da raccontare? Presentaci la tua candidatura.</h2>
<p>Compila il modulo con ciò che desideri condividere. Poi si aprirà WhatsApp: potrai rileggere il messaggio e premere Invia.</p>
<p>Non inserire documenti clinici o dati che permettono di riconoscere altre persone. Tutti i campi sono obbligatori.</p>
<form id="candidatura" action="/api/candidatura" method="post" data-candidatura>
<input type="hidden" name="csrf" value="<?= e(Altrove\Csrf::token()) ?>">
<input type="hidden" name="stamp" value="<?= e(Altrove\Csrf::stamp($config['secret'])) ?>">
<input type="hidden" name="origine" value="<?= e($origin) ?>">
<div class="honeypot" aria-hidden="true"><label for="sito_web">Lascia vuoto questo campo</label><input type="text" name="sito_web" id="sito_web" tabindex="-1" autocomplete="off"></div>
<div class="error-summary" id="errori-modulo" tabindex="-1" role="status" aria-live="polite"<?= !$errors?' hidden':'' ?>>
<p><?= e($errors['form'] ?? ($errors ? 'Controlla i campi indicati e riprova.' : '')) ?></p>
<ul><?php foreach($errors as $key=>$message): if($key==='form')continue; ?><li><a href="#<?= e($key) ?>"><?= e($message) ?></a></li><?php endforeach; ?></ul>
<?php if(whatsapp($config)): ?><a data-whatsapp="errore" href="<?= e(whatsapp($config)) ?>" target="_blank" rel="noopener noreferrer">Puoi anche scrivermi su WhatsApp</a><?php endif; ?>
</div>
<p class="step-status" data-step-status hidden aria-live="polite"></p>
<fieldset data-step>
<legend tabindex="-1">In che veste vuoi partecipare?</legend>
<?php foreach(['esperienza'=>'Voglio raccontare un’esperienza','professionista'=>'Voglio presentare il mio percorso professionale','progetto'=>'Voglio proporre un progetto'] as $key=>$label): ?>
<label class="choice"><input type="radio" name="veste"<?= $key==='esperienza'?' id="veste"':'' ?> value="<?= e($key) ?>"<?= $value('veste')===$key?' checked':'' ?> required aria-describedby="errore-veste"><span><?= e($label) ?></span></label>
<?php endforeach; ?>
<p class="field-error" id="errore-veste" data-error="veste"><?= e($error('veste')) ?></p>
</fieldset>
<fieldset data-step>
<legend tabindex="-1">Qual è il tema della tua storia?</legend>
<label for="tema">Il tema più vicino a te</label>
<select id="tema" name="tema" required aria-describedby="errore-tema">
<option value="">Scegli un tema</option>
<?php foreach(['nde'=>'Esperienze di pre-morte','medium'=>'Medium e sensitivi','channeling'=>'Channeling e coscienza stellare','mentalismo'=>'Mentalismo e ipnosi','altro'=>'Un’altra storia'] as $key=>$label): ?><option value="<?= e($key) ?>"<?= $value('tema')===$key?' selected':'' ?>><?= e($label) ?></option><?php endforeach; ?>
</select>
<p class="field-error" id="errore-tema" data-error="tema"><?= e($error('tema')) ?></p>
</fieldset>
<fieldset data-step>
<legend tabindex="-1">Raccontami la tua storia</legend>
<label for="esperienza">La tua esperienza</label>
<p id="aiuto-esperienza" class="small">Parti dal momento che ti ha portato qui. Puoi scrivere da 30 a 4.000 caratteri.</p>
<textarea name="esperienza" id="esperienza" rows="8" minlength="30" maxlength="4000" required aria-describedby="aiuto-esperienza contatore errore-esperienza"><?= e($value('esperienza')) ?></textarea>
<p id="contatore" class="small" data-counter><?= mb_strlen($value('esperienza')) ?> / 4.000 caratteri</p>
<p class="field-error" id="errore-esperienza" data-error="esperienza"><?= e($error('esperienza')) ?></p>
</fieldset>
<fieldset data-step>
<legend tabindex="-1">Come ti chiami?</legend>
<div class="field"><label for="nome">Nome</label><input name="nome" id="nome" autocomplete="given-name" minlength="2" maxlength="60" value="<?= e($value('nome')) ?>" required aria-describedby="errore-nome"><p class="field-error" id="errore-nome" data-error="nome"><?= e($error('nome')) ?></p></div>
<div class="field"><label for="cognome">Cognome</label><input name="cognome" id="cognome" autocomplete="family-name" minlength="2" maxlength="60" value="<?= e($value('cognome')) ?>" required aria-describedby="errore-cognome"><p class="field-error" id="errore-cognome" data-error="cognome"><?= e($error('cognome')) ?></p></div>
</fieldset>
<fieldset data-step>
<legend tabindex="-1">Dove posso ricontattarti?</legend>
<label for="telefono">Telefono</label>
<input type="tel" inputmode="tel" autocomplete="tel" name="telefono" id="telefono" maxlength="30" value="<?= e($value('telefono')) ?>" required aria-describedby="aiuto-telefono errore-telefono">
<p id="aiuto-telefono" class="small">Se il tuo numero non è italiano, aggiungi il prefisso internazionale.</p>
<p class="field-error" id="errore-telefono" data-error="telefono"><?= e($error('telefono')) ?></p>
<label class="choice consent"><input type="checkbox" id="consenso" name="consenso" value="1" required<?= $value('consenso')==='1'?' checked':'' ?> aria-describedby="errore-consenso"><span><?= e($common['consenso']) ?></span></label>
<a href="/privacy/" target="_blank" rel="noopener">Leggi l’informativa privacy <span class="sr-only">(nuova scheda)</span></a>
<p class="field-error" id="errore-consenso" data-error="consenso"><?= e($error('consenso')) ?></p>
<p class="small">Aprendo WhatsApp, il messaggio e i dati che hai scritto saranno comunicati a quel servizio. Il sito non invia il messaggio al posto tuo.</p>
<?php if($config['turnstile']): ?><div class="cf-turnstile" data-sitekey="<?= e($config['turnstile_site']) ?>" data-action="candidatura" data-theme="dark" data-size="flexible"></div><noscript><p>La verifica anti-spam richiede JavaScript. Puoi contattarmi direttamente usando il collegamento WhatsApp.</p></noscript><?php endif; ?>
<button class="button" type="submit" data-submit>Continua su WhatsApp</button>
<p data-send-status role="status" aria-live="polite"></p>
</fieldset>
<div class="step-controls" data-step-controls hidden><button class="button button-secondary" type="button" data-back>Indietro</button><button class="button" type="button" data-next>Continua</button></div>
</form>
<p class="small"><?= e($common['nota']) ?></p>
</section>

