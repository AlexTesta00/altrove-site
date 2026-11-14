<section class="section invite" aria-labelledby="invito-titolo">
<h2 id="invito-titolo"><?= e($common['invitoTitolo']) ?></h2>
<div class="prose"><?php foreach($common['invito'] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?></div>
<a class="button" href="/candidati/?origine=<?= e(Altrove\Router::origin($origin)) ?>">Candidati</a>
<p class="small"><?= e($common['nota']) ?></p>
</section>

