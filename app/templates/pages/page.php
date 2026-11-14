<section class="page-heading">
<h1><?= e($page['h1']) ?></h1>
<?php if(isset($page['intro'])): ?><p class="lead"><?= e($page['intro']) ?></p><a class="button" href="/candidati/?origine=home">Racconta la tua storia</a><?php endif; ?>
</section>
<?php if($id==='home'): ?>
<section class="section about-preview">
<picture><source type="image/avif" srcset="/assets/img/alessia-480.avif 480w, /assets/img/alessia-800.avif 800w, /assets/img/alessia-1254.avif 1254w" sizes="(min-width: 900px) 400px, 90vw"><source type="image/webp" srcset="/assets/img/alessia-480.webp 480w, /assets/img/alessia-800.webp 800w, /assets/img/alessia-1254.webp 1254w" sizes="(min-width: 900px) 400px, 90vw"><img src="/assets/img/alessia-800.jpg" width="800" height="800" alt="Alessia Ventura, autrice e conduttrice di Altrove Podcast" loading="eager" fetchpriority="high" decoding="async"></picture>
<div><h2>Prima di tutto, ascoltare</h2><p>Sono Alessia, content creator e mamma. Con Altrove ho aperto uno spazio per le storie che non trovano sempre un posto nelle conversazioni di ogni giorno.</p><a class="text-link" href="/alessia-ventura/">Ti racconto chi sono</a></div>
</section>
<section class="section" aria-labelledby="temi-titolo"><h2 id="temi-titolo">Da dove inizia la tua storia?</h2><ul class="theme-list"><?php foreach($common['temi'] as $slug=>$label): ?><li><a href="/<?= e($slug) ?>/"><?= e($label) ?><span aria-hidden="true">→</span></a></li><?php endforeach; ?></ul></section>
<section class="section prose"><h2>Come ci incontriamo</h2><ol class="process"><li><h3>Raccontami</h3><p>Scegli il tema e scrivi la tua storia nel modulo.</p></li><li><h3>Continua su WhatsApp</h3><p>Rileggi il messaggio preparato e premi Invia su WhatsApp.</p></li><li><h3>Ci conosciamo</h3><p>Leggo la tua candidatura e ti ricontatto se desidero approfondire.</p></li></ol><p><?= e($common['selezione']) ?></p></section>
<?php require __DIR__.'/../partials/episodes.php'; require __DIR__.'/../partials/faq.php'; require __DIR__.'/../partials/invito.php'; ?>
<?php elseif(isset($page['sections'])): ?>
<div class="prose"><?php foreach($page['sections'] as [$heading,$paragraph]): ?><section class="reading-section"><h2><?= e($heading) ?></h2><p><?= e($paragraph) ?></p></section><?php endforeach; ?></div>
<?php require __DIR__.'/../partials/episodes.php'; require __DIR__.'/../partials/faq.php'; ?>
<section class="section"><h2>Altre voci, altri percorsi</h2><ul class="related-links"><?php foreach($common['temi'] as $slug=>$label): if('/'.$slug.'/'===$path)continue; ?><li><a href="/<?= e($slug) ?>/"><?= e($label) ?></a></li><?php endforeach; ?></ul></section>
<?php require __DIR__.'/../partials/invito.php'; ?>
<?php elseif($id==='alessia'): ?>
<div class="prose"><?php foreach(require $config['root'].'/content/alessia.php' as [$heading,$paragraph]): ?><section class="reading-section"><h2><?= e($heading) ?></h2><p><?= e($paragraph) ?></p></section><?php endforeach; ?><p><a href="https://www.instagram.com/alessia.ventura23/" target="_blank" rel="noopener noreferrer">Il mio profilo Instagram</a> · <a href="https://www.youtube.com/@altrovepodcast" target="_blank" rel="noopener noreferrer">Le puntate di Altrove su YouTube</a></p></div>
<?php require __DIR__.'/../partials/invito.php'; ?>
<?php elseif($id==='candidati'): ?>
<div class="prose"><?php foreach($common['candidatiIntro'] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?></div>
<?php require __DIR__.'/../partials/form.php'; ?>
<?php elseif(in_array($id,['privacy','cookie'],true)): ?>
<!-- Bozza informativa da verificare con un professionista prima della pubblicazione. -->
<div class="prose"><?php foreach(require $config['root'].'/content/'.$id.'.php' as [$heading,$paragraph]): ?><section class="reading-section"><h2><?= e($heading) ?></h2><p><?= e($paragraph) ?></p></section><?php endforeach; ?>
<p><a href="/<?= $id==='privacy'?'cookie':'privacy' ?>/"><?= $id==='privacy'?'Cookie e servizi esterni':'Informativa privacy' ?></a></p>
<?php if(whatsapp($config)): ?><p><a data-whatsapp="privacy" href="<?= e(whatsapp($config,'Ciao Alessia, desidero informazioni sul trattamento dei miei dati personali.')) ?>" target="_blank" rel="noopener noreferrer">Contatta Alessia su WhatsApp per i tuoi dati</a></p><?php endif; ?>
</div>
<?php elseif($id==='grazie'): ?>
<?php $handoff=$_SESSION['handoff']??[]; if(($handoff['expires']??0)<time()){unset($_SESSION['handoff']);$handoff=[];} ?>
<div class="prose">
<?php if(!empty($handoff['text'])): ?>
<p>Ho preparato il messaggio con la tua storia. Per farmelo arrivare, aprilo su WhatsApp e premi Invia. Puoi rileggerlo e modificarlo prima.</p>
<details class="message-preview"><summary>Rileggi il messaggio</summary><pre><?= e($handoff['text']) ?></pre></details>
<a class="button" data-whatsapp="candidatura" href="<?= e(whatsapp($config,$handoff['text'])) ?>">Apri la candidatura su WhatsApp</a>
<p class="small">Il collegamento resta disponibile per quindici minuti. Aprendolo, condividi con WhatsApp il contenuto che hai scritto.</p>
<?php else: ?>
<p>Per proteggere i tuoi dati, il messaggio preparato non è più disponibile. Puoi compilare una nuova candidatura oppure scrivermi direttamente su WhatsApp.</p>
<a class="button" href="/candidati/">Torna al modulo</a>
<?php endif; ?>
<?php if(whatsapp($config)): ?><p><a data-whatsapp="grazie-generico" href="<?= e(whatsapp($config,'Ciao Alessia, vorrei presentare la mia candidatura per Altrove.')) ?>">Scrivimi su WhatsApp</a></p><?php endif; ?>
<p><?= e($common['selezione']) ?></p>
</div>
<?php else: ?>
<div class="prose"><p>Non trovo l’indirizzo che stai cercando. Puoi ripartire dalle storie di Altrove o presentarmi la tua.</p><a class="button" href="/">Torna ad Altrove</a><p><a href="/candidati/">Raccontami la tua storia</a></p><ul><?php foreach($common['temi'] as $slug=>$label): ?><li><a href="/<?= e($slug) ?>/"><?= e($label) ?></a></li><?php endforeach; ?></ul></div>
<?php endif; ?>

