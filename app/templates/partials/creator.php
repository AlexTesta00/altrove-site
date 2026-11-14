<section class="creator" aria-labelledby="creator-title">
<?php if(is_file(($_SERVER['DOCUMENT_ROOT'] ?? dirname($config['root']).'/public').'/assets/images/alex-testa-160.webp')): ?>
<picture class="creator-portrait"><source type="image/avif" srcset="/assets/images/alex-testa-160.avif 160w, /assets/images/alex-testa-320.avif 320w" sizes="80px"><img src="/assets/images/alex-testa-160.webp" srcset="/assets/images/alex-testa-160.webp 160w, /assets/images/alex-testa-320.webp 320w" sizes="80px" alt="Alex Testa" width="80" height="80" loading="lazy" decoding="async"></picture>
<?php endif; ?>
<div><h2 class="serif" id="creator-title">Sito creato da <a href="https://www.instagram.com/alextesta._/" rel="noopener">Alex Testa</a></h2><p>Design e sviluppo web, con attenzione all’identità del progetto e all’esperienza di chi lo visita.</p><a class="creator-link" href="https://www.instagram.com/alextesta._/" rel="noopener"><?= icon('instagram') ?>@alextesta._<span class="sr-only"> su Instagram</span></a></div>
</section>
