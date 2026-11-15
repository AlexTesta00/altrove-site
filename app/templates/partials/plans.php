<?php
$application = $_SESSION['application'] ?? null;
$planError = $_SESSION['plan_error']['message'] ?? '';
unset($_SESSION['plan_error']);
?>
<?php if (!is_array($application)): ?>
<section class="panel done"><h1 class="serif q">Ripartiamo <span class="mute">dalla tua storia.</span></h1>
<p>Per scegliere un piano, completa prima la candidatura. Se l’avevi già compilata, la sessione potrebbe essere scaduta: i dati restano disponibili per quindici minuti.</p>
<a class="cta" href="/candidati/"><span class="disc"><?= icon('arrow') ?></span>Vai alla candidatura</a></section>
<?php else: ?>
<nav aria-label="Percorso della candidatura"><ol class="proposal-progress"><li><a href="/candidati/#candidatura">1. La tua storia</a></li><li aria-current="step">2. Il tuo piano</li><li>3. WhatsApp</li></ol></nav>
<header class="proposal-intro"><h1 class="serif">La tua storia è pronta.<br><span class="mute">Scegli come raccontarla.</span></h1>
<p>Hai completato il modulo. Ora scegli la proposta più adatta a te: la aggiungeremo alle tue risposte nel messaggio per Alessia.</p>
<p>Ogni piano comprende la conversazione con Alessia, la realizzazione della puntata e dei Reel previsti e la pubblicazione sui canali indicati. Cambiano i canali e il numero di estratti social.</p></header>
<form id="scelta-piano" action="/api/piano" method="post" data-plans>
<input type="hidden" name="csrf" value="<?= e(Altrove\Csrf::token()) ?>">
<input type="hidden" name="candidatura" value="<?= e($application['id']) ?>">
<fieldset class="plan-fieldset" aria-describedby="plan-amounts plan-error">
<legend class="serif">Quale proposta fa per te?</legend>
<p id="plan-amounts" class="plan-amounts">Importi finali, per singola collaborazione.</p>
<p id="plan-error" class="field-error" role="status"<?= $planError === '' ? ' hidden' : '' ?>><?= e($planError) ?></p>
<div class="plan-options">
<?php foreach (Altrove\ParticipationPlans::all() as $planId => $plan): ?>
<label class="plan-option<?= $plan['recommended'] ? ' plan-recommended' : '' ?>">
<span class="plan-heading"><span class="plan-name serif" id="plan-name-<?= e($planId) ?>"><?= e($plan['name']) ?></span><span class="plan-price" id="plan-price-<?= e($planId) ?>"><?= e($plan['price']) ?> €</span></span>
<span class="plan-includes" role="list" id="plan-includes-<?= e($planId) ?>"><?php foreach ($plan['items'] as $item): ?><span role="listitem"><?= e($item) ?></span><?php endforeach; ?></span>
<span class="plan-purpose"><?= e($plan['for']) ?></span>
<span class="plan-difference"><?= e($plan['difference']) ?></span>
<span class="plan-select"><input type="radio" name="piano" value="<?= e($planId) ?>" required<?= ($application['plan'] ?? '') === $planId ? ' checked' : '' ?> aria-labelledby="plan-name-<?= e($planId) ?> plan-price-<?= e($planId) ?>" aria-describedby="plan-includes-<?= e($planId) ?>"><span class="plan-idle">Scegli <?= e($plan['name']) ?></span><span class="plan-selected">Piano selezionato</span></span>
</label>
<?php endforeach; ?>
</div></fieldset>
<section class="plan-confirm" aria-labelledby="plan-confirm-title"><h2 class="serif" id="plan-confirm-title">Prima di inviare, <span class="mute">rileggi tutto.</span></h2>
<p>Nel prossimo passaggio vedrai la tua storia e il piano scelto, con prezzo e contenuti inclusi. Alessia riceverà la proposta solo quando premerai Invia su WhatsApp.</p>
<p class="plan-current" data-plan-current role="status" aria-live="polite">Scegli un piano per preparare il messaggio.</p>
<button type="submit" class="cta"><span class="disc"><?= icon('arrow') ?></span>Prepara il messaggio</button>
<a class="plan-back" href="/candidati/#candidatura"><?= icon('back') ?>Modifica le tue risposte</a>
<p class="small"><?= e($common['nota']) ?> Modalità e calendario saranno concordati con Alessia dopo la valutazione. Non è richiesto alcun pagamento in questo passaggio.</p></section>
</form>
<?php endif; ?>
