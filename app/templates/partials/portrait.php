<figure class="ph">
<picture>
<source type="image/avif" srcset="<?= e(\Altrove\Assets::image('alessia-ventura-portrait-480.avif')) ?> 480w, <?= e(\Altrove\Assets::image('alessia-ventura-portrait-800.avif')) ?> 800w, <?= e(\Altrove\Assets::image('alessia-ventura-portrait-1000.avif')) ?> 1000w" sizes="(min-width:900px) 440px, 90vw">
<source type="image/webp" srcset="<?= e(\Altrove\Assets::image('alessia-ventura-portrait-480.webp')) ?> 480w, <?= e(\Altrove\Assets::image('alessia-ventura-portrait-800.webp')) ?> 800w, <?= e(\Altrove\Assets::image('alessia-ventura-portrait-1000.webp')) ?> 1000w" sizes="(min-width:900px) 440px, 90vw">
<img src="<?= e(\Altrove\Assets::image('alessia-ventura-portrait-800.webp')) ?>" width="800" height="1000" alt="Alessia Ventura nello studio di Altrove, davanti al microfono"<?= $id==='alessia'?' loading="eager" fetchpriority="high"':' loading="lazy"' ?> decoding="async">
</picture>
<figcaption><span class="lamp sm" aria-hidden="true"></span>In studio con Alessia</figcaption>
</figure>
