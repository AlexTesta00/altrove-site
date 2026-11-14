<?php if($episodes): ?>
<section class="section" aria-labelledby="puntate-titolo">
<h2 id="puntate-titolo">Le voci di Altrove</h2>
<p>Le ultime pubblicazioni dal canale, tra puntate ed estratti.</p>
<div class="episodes">
<?php foreach($episodes as $episode): ?>
<article class="episode">
<a href="https://www.youtube.com/watch?v=<?= e($episode['id']) ?>" data-video="<?= e($episode['id']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Guarda <?= e($episode['title']) ?>">
<img src="https://i.ytimg.com/vi/<?= e($episode['id']) ?>/hqdefault.jpg" width="480" height="360" alt="" loading="lazy" decoding="async">
<span class="play-label">Guarda la puntata</span>
</a>
<h3><?= e($episode['title']) ?></h3>
<?php if(preg_match('/^\d{4}-\d{2}-\d{2}$/D',$episode['date'])): ?><time datetime="<?= e($episode['date']) ?>"><?= e(date('d/m/Y',strtotime($episode['date']))) ?></time><?php endif; ?>
</article>
<?php endforeach; ?>
</div>
<p class="small">Il video si carica quando lo scegli. <a href="/cookie/">Informazioni sui servizi esterni</a>.</p>
</section>
<?php endif; ?>

