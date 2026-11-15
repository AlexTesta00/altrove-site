<?php if($id==='home'): ?>
<section class="hero" id="top">
<h1 class="serif h1"><span class="word-group"><?php foreach(explode(' ',$home['hero'][0]) as $word): ?><span class="w"><?= e($word) ?></span> <?php endforeach; ?></span><span class="mute"><?php foreach(explode(' ',$home['hero'][1]) as $word): ?><span class="w"><?= e($word) ?></span> <?php endforeach; ?></span></h1>
<div class="hc"><p class="lead"><?= e($home['lead']) ?></p><a class="cta" data-magnet href="/candidati/?origine=home"><span class="disc wv"><?= icon('mic') ?></span>Racconta la tua storia</a><p class="hint"><?= e($home['hint']) ?></p></div>
<?php require __DIR__.'/../partials/tuner.php'; ?>
</section>
<div class="hz" aria-hidden="true"><span class="sun"></span><span class="big serif">ALTR<span class="o"></span>VE</span></div>
<section class="sec" id="frequenze">
<h2 class="serif h2">Quattro frequenze. <span class="mute">Una sola regola: la tua verità.</span></h2>
<div class="fqs"><?php foreach($home['frequenze'] as $i=>$frequency): $episode=$frequencyEpisodes[$frequency['id']][0]??null; ?>
<article class="fq<?= $i===0?' on':'' ?>" id="frequenza-<?= e($frequency['id']) ?>" data-frequency="<?= $i ?>"><span class="tk" aria-hidden="true"></span><div><h3 class="serif"><a href="/<?= e($frequency['slug']) ?>/"><?= e($frequency['name']) ?></a></h3><p><?= e($frequency['description']) ?></p>
<?php if($episode): ?><a class="ep" href="https://www.youtube.com/watch?v=<?= e($episode['id']) ?>"><?= icon('play') ?>Ascolta «<?= e($episode['title']) ?>»</a><?php else: ?><a class="ep" href="/<?= e($frequency['slug']) ?>/"><?= icon('arrow') ?>Esplora questa frequenza</a><?php endif; ?>
</div></article><?php endforeach; ?></div>
</section>
<section class="sec yt" id="youtube"><div class="sh"><h2 class="serif h2">Le storie, in video. <span class="mute">Tutte le puntate sono su YouTube.</span></h2><a class="cta" data-magnet href="https://www.youtube.com/@altrovepodcast?sub_confirmation=1"><span class="disc wv"><?= icon('youtube') ?></span>Iscriviti al canale</a></div>
<?php if($episodes): $feature=$episodes[0]; ?><div class="ytg">
<article class="ytf"><div class="video-facade"><a href="https://www.youtube.com/watch?v=<?= e($feature['id']) ?>" data-video="<?= e($feature['id']) ?>" aria-label="Guarda <?= e($feature['title']) ?>"><span class="cov"><img src="https://i.ytimg.com/vi/<?= e($feature['id']) ?>/hqdefault.jpg" width="480" height="360" alt="" loading="lazy" decoding="async"><span class="pl"><?= icon('play') ?></span></span></a></div><h3 class="yt-t"><?= e($feature['title']) ?></h3><?php if(isset($feature['views'])&&is_int($feature['views'])): ?><p class="yt-v"><?= e(number_format($feature['views'],0,',','.')) ?> visualizzazioni</p><?php endif; ?></article>
<ol class="ytl"><?php foreach(array_slice($episodes,1,4) as $episode): ?><li><div class="video-facade"><a href="https://www.youtube.com/watch?v=<?= e($episode['id']) ?>" data-video="<?= e($episode['id']) ?>"><span class="cov sm"><img src="https://i.ytimg.com/vi/<?= e($episode['id']) ?>/hqdefault.jpg" width="480" height="360" alt="" loading="lazy" decoding="async"></span><span><span class="yt-t"><?= e($episode['title']) ?></span><?php if(isset($episode['views'])&&is_int($episode['views'])): ?><span class="yt-v"><?= e(number_format($episode['views'],0,',','.')) ?> visualizzazioni</span><?php endif; ?></span></a></div></li><?php endforeach; ?></ol>
</div><?php endif; ?>
</section>
<section class="sec voce voce-home"><?php require __DIR__.'/../partials/portrait.php'; ?><div class="vt"><h2 class="serif h2"><?= e($home['presentazione_titolo'][0]) ?> <span class="mute"><?= e($home['presentazione_titolo'][1]) ?></span></h2><?php foreach($home['alessia'] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?><ul class="facts"><?php foreach($home['fatti'] as $fact): ?><li><?= e($fact) ?></li><?php endforeach; ?></ul><a class="ep" href="/alessia-ventura/"><?= icon('arrow') ?>Conosci Alessia</a></div></section>
<section class="sec ig" id="instagram"><h2 class="serif h2">Dietro le quinte. <span class="mute">Seguici su Instagram.</span></h2><div class="igg">
<a class="igc" href="https://www.instagram.com/altrove_podcast/"><span class="av" aria-hidden="true"><span class="avr"></span><span class="avi mark"></span></span><span><span class="igh">@altrove_podcast</span><span class="igd">Il podcast: anteprime, clip e frammenti delle puntate.</span></span><span class="pill2"><?= icon('instagram') ?>Segui Altrove</span></a>
<a class="igc" href="https://www.instagram.com/alessia.ventura23/"><span class="av" aria-hidden="true"><span class="avr"></span><img class="avi" src="<?= e(\Altrove\Assets::image('alessia-ventura-square-480.webp')) ?>" alt="" width="480" height="480" loading="lazy" decoding="async"></span><span><span class="igh">@alessia.ventura23</span><span class="igd">Alessia dietro le quinte: le registrazioni e quello che non va in onda.</span></span><span class="pill2"><?= icon('instagram') ?>Segui Alessia</span></a>
</div></section>
<section class="sec"><h2 class="serif h2">Come funziona</h2><ol class="steps"><?php foreach($home['passi'] as $i=>[$heading,$paragraph]): ?><li><span class="n"><?= $i+1 ?></span><div><h3><?= e($heading) ?></h3><p><?= e($paragraph) ?></p></div></li><?php endforeach; ?></ol></section>
<?php require __DIR__.'/../partials/invito.php'; ?>
<?php elseif($id==='candidati'): ?>
<h1 class="serif h1">Partecipa ad <span class="mute">ALTROVE Podcast</span></h1><div class="pol"><?php foreach($common['candidatiIntro'] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?></div><h2 class="serif ask">Hai una storia da raccontare? <span class="mute">Presentaci la tua candidatura.</span></h2>
<?php require __DIR__.'/../partials/form.php'; ?>
<?php elseif($id==='grazie'): ?>
<?php $handoff=$_SESSION['handoff']??[]; if(($handoff['expires']??0)<time()){unset($_SESSION['handoff']);$handoff=[];} ?>
<section class="panel done"><span class="lamp" aria-hidden="true"></span><h1 class="serif q">Ultimo passo: <span class="mute">mandamela su WhatsApp.</span></h1>
<?php if(!empty($handoff['text'])): ?>
<p><?= !empty($handoff['copy_required'])?'La tua storia è lunga: copiala per intero e incollala in WhatsApp. Non ne lasciamo indietro una parola.':'Ho preparato il messaggio con le tue risposte. Apri WhatsApp e premi Invia per farmelo arrivare.' ?></p>
<div class="bub" aria-label="Anteprima del messaggio WhatsApp"><?php foreach($handoff['blocks']??[['title'=>'','value'=>$handoff['text']]] as $block): ?><div class="bk"><?php if($block['title']!==''): ?><strong class="bh"><?= e($block['title']) ?></strong><?php endif; ?><?php if($block['value']!==''): ?><span class="bv"><?= e($block['value']) ?></span><?php endif; ?></div><?php endforeach; ?></div>
<div class="copy-message"><label class="lbl" for="messaggio-completo">Il messaggio completo, pronto da copiare</label><textarea class="fld" id="messaggio-completo" readonly rows="5"><?= e($handoff['text']) ?></textarea><button class="ghost" type="button" data-copy-message hidden><?= icon('copy') ?>Copia il messaggio completo</button><p class="small" data-copy-status role="status" aria-live="polite"></p></div>
<a class="cta" data-whatsapp="candidatura" href="<?= e(whatsapp($config,!empty($handoff['copy_required'])?'':$handoff['text'])) ?>"><span class="disc"><?= icon('whatsapp') ?></span>Apri WhatsApp e invia</a>
<?php if(!empty($handoff['copy_required'])): ?><p class="small">Dopo aver aperto WhatsApp, incolla il messaggio che hai copiato e premi Invia.</p><?php endif; ?>
<p class="small">Il messaggio resta disponibile per quindici minuti. Aprendo il collegamento, condividi i dati con WhatsApp.</p>
<?php else: ?><p>Per proteggere i tuoi dati, il messaggio non è più disponibile. Puoi ripartire dal modulo.</p><a class="cta" href="/candidati/"><span class="disc"><?= icon('arrow') ?></span>Torna alla candidatura</a><?php endif; ?>
<a class="home" href="/">Torna ad Altrove</a></section>
<?php elseif(isset($page['sections'])): ?>
<?php $subtitles=['nde'=>'Raccontami cosa hai visto oltre.','medium'=>'Diamo voce a quello che senti.','channeling'=>'Troviamo le parole per raccontarlo.','mentalismo'=>'La mente oltre i suoi confini.']; ?>
<section class="page-hero"><h1 class="serif h1"><?= e($page['h1']) ?>. <span class="mute"><?= e($subtitles[$id]??'') ?></span></h1><?php require __DIR__.'/../partials/tuner.php'; ?></section>
<div class="reading long-copy"><?php foreach($page['sections'] as [$heading,$paragraph]): ?><section class="reading-section"><h2 class="serif"><?= e($heading) ?></h2><p><?= e($paragraph) ?></p></section><?php endforeach; ?></div>
<?php require __DIR__.'/../partials/episodes.php'; require __DIR__.'/../partials/faq.php'; require __DIR__.'/../partials/invito.php'; ?>
<?php elseif($id==='alessia'): ?>
<section class="page-hero"><h1 class="serif h1">Sono Alessia Ventura. <span class="mute">Dall’altra parte ci sono io.</span></h1></section>
<section class="sec voce alessia-story"><?php require __DIR__.'/../partials/portrait.php'; ?><div class="vt">
<?php foreach(require $config['root'].'/content/alessia.php' as $section): [$heading,$paragraph]=$section; ?>
<section class="reading-section"><h2 class="serif"><?= e($heading) ?></h2><p><?= e($paragraph) ?></p>
<?php if(!empty($section['sources'])): ?><div class="fl"><?php foreach($section['sources'] as [$label,$url]): ?><a href="<?= e($url) ?>"><?= e($label) ?></a><?php endforeach; ?></div><?php endif; ?>
</section><?php endforeach; ?>
<ul class="facts"><?php foreach($home['fatti'] as $fact): ?><li><?= e($fact) ?></li><?php endforeach; ?></ul><div class="fl"><a href="https://www.instagram.com/alessia.ventura23/">Instagram</a><a href="https://www.youtube.com/@altrovepodcast">YouTube</a></div></div></section>
<?php require __DIR__.'/../partials/invito.php'; ?>
<?php elseif(in_array($id,['privacy','cookie'],true)): ?>
<section class="page-hero reading"><h1 class="serif h1"><?= e($page['h1']) ?></h1><p class="small">Ultimo aggiornamento: <time datetime="<?= e(Altrove\Privacy::VERSION) ?>">15 novembre 2026</time>.</p></section>
<div class="reading long-copy"><?php foreach(require $config['root'].'/content/'.$id.'.php' as $section): [$heading,$paragraph]=$section; ?>
<section class="reading-section"><h2 class="serif"><?= e($heading) ?></h2><p><?= e($paragraph) ?></p>
<?php if(!empty($section['sources'])): ?><ul class="policy-links"><?php foreach($section['sources'] as [$label,$url]): ?><li><a href="<?= e($url) ?>"><?= e($label) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
</section><?php endforeach; ?><p><a href="/<?= $id==='privacy'?'cookie':'privacy' ?>/"><?= $id==='privacy'?'Cookie e servizi esterni':'Informativa privacy' ?></a></p></div>
<?php else: ?>
<section class="page-hero"><h1 class="serif h1">Questa frequenza <span class="mute">è muta.</span></h1><p class="lead">La pagina che cerchi non è qui. Sintonizzati su una delle storie di Altrove, oppure raccontami la tua.</p><?php require __DIR__.'/../partials/tuner.php'; ?><a class="cta" href="/candidati/"><span class="disc"><?= icon('mic') ?></span>Candidati</a></section>
<?php endif; ?>
