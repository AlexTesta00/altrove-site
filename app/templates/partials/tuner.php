<?php
$frequencies=$home['frequenze'];
$activeIndex=0;
foreach($frequencies as $i=>$frequency)if($frequency['id']===$id)$activeIndex=$i;
$isHome=$id==='home';
?>
<nav class="tuner" data-tuner data-active="<?= $activeIndex ?>"<?= !$isHome?' data-static':'' ?> aria-label="Sintonizza un tema">
<div class="rdo">
<span class="sig" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
<span class="rd serif"><?php foreach($frequencies as $i=>$frequency): ?><span class="rn<?= $i===$activeIndex?' on':'' ?>"<?= $i!==$activeIndex?' aria-hidden="true"':'' ?>><?= e($frequency['name']) ?></span><?php endforeach; ?></span>
<span class="eq" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
</div>
<div class="dial" data-dial<?= $isHome?' tabindex="0" role="slider" aria-label="Frequenza" aria-valuemin="0" aria-valuemax="3" aria-valuenow="0" aria-valuetext="Esperienze di pre-morte"':' aria-hidden="true"' ?>><div class="ticks"></div><span class="needle"></span></div>
<div class="sts"><?php foreach($frequencies as $i=>$frequency): ?><a class="st<?= $i===$activeIndex?' on':'' ?>" data-station="<?= $i ?>" href="<?= $isHome?'#frequenza-'.e($frequency['id']):'/'.e($frequency['slug']).'/' ?>"<?= !$isHome&&$frequency['id']===$id?' aria-current="page"':'' ?>><?= e($frequency['short']) ?></a><?php endforeach; ?></div>
<?php if($isHome): ?>
<div class="nps"><?php foreach($frequencies as $i=>$frequency): $playing=$frequencyEpisodes[$frequency['id']][0]??null; ?>
<a class="np" data-now-playing="<?= $i ?>" href="<?= $playing?'https://www.youtube.com/watch?v='.e($playing['id']):'#frequenza-'.e($frequency['id']) ?>"<?= $i!==$activeIndex?' hidden':'' ?>><?= icon('play') ?><span><b><?= $playing?'In onda':'Sintonizzati' ?></b> · <?= e($playing?$playing['title']:$frequency['name']) ?></span></a>
<?php endforeach; ?></div>
<p class="tuner-help">Trascina l’ago o usa le frecce per cambiare frequenza.</p>
<?php endif; ?>
</nav>
