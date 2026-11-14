<?php use Altrove\Assets; use Altrove\Seo; ?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['description']) ?>">
<link rel="canonical" href="<?= e($config['url'] . $path) ?>">
<?php if (!empty($page['noindex'])): ?><meta name="robots" content="noindex, follow"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:locale" content="it_IT">
<meta property="og:site_name" content="Altrove Podcast">
<meta property="og:title" content="<?= e($page['title']) ?>">
<meta property="og:description" content="<?= e($page['description']) ?>">
<meta property="og:url" content="<?= e($config['url'] . $path) ?>">
<meta property="og:image" content="<?= e($config['url']) ?>/assets/og/altrove.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Altrove Podcast · Storie oltre il visibile">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page['title']) ?>">
<meta name="twitter:description" content="<?= e($page['description']) ?>">
<meta name="twitter:image" content="<?= e($config['url']) ?>/assets/og/altrove.jpg">
<link rel="icon" href="/assets/favicon/favicon.ico" sizes="any">
<link rel="icon" href="/assets/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#14151b">
<?php if (Assets::get('css')): ?><link rel="stylesheet" href="<?= e(Assets::get('css')) ?>"><?php endif; ?>
<script type="application/ld+json" nonce="<?= e($nonce) ?>"><?= json_encode(Seo::graph($config,$page,$path), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php if (Assets::get('js')): ?><script type="module" src="<?= e(Assets::get('js')) ?>" nonce="<?= e($nonce) ?>"></script><?php endif; ?>
<?php if ($id === 'candidati' && $config['turnstile']): ?><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" nonce="<?= e($nonce) ?>" async defer></script><?php endif; ?>
<?php if ($config['umami_id'] !== ''): ?><script defer src="<?= e($config['umami_src']) ?>" data-website-id="<?= e($config['umami_id']) ?>" data-do-not-track="true" nonce="<?= e($nonce) ?>"></script><?php endif; ?>
</head>
<body>
<a class="skip-link" href="#contenuto">Vai al contenuto</a>
<header class="site-header wrap">
<a class="wordmark" href="/" aria-label="Altrove Podcast, pagina iniziale">ALTROVE <span>Podcast</span></a>
<nav aria-label="Navigazione principale">
<a href="/alessia-ventura/"<?= $id==='alessia'?' aria-current="page"':'' ?>>Alessia</a>
<a class="button button-small" href="/candidati/"<?= $id==='candidati'?' aria-current="page"':'' ?>>Candidati</a>
</nav>
</header>
<main id="contenuto" class="wrap">
<?php if ($id !== 'home' && $id !== '404'): ?><nav class="breadcrumb" aria-label="Percorso"><a href="/">Altrove</a><span aria-hidden="true">/</span><span><?= e($page['h1']) ?></span></nav><?php endif; ?>
<?php require __DIR__.'/pages/page.php'; ?>
</main>
<footer class="site-footer wrap">
<p>Altrove Podcast · Alessia Ventura</p>
<nav aria-label="Informazioni e social">
<a href="/privacy/">Privacy</a><a href="/cookie/">Cookie</a>
<a href="https://www.youtube.com/@altrovepodcast" target="_blank" rel="noopener noreferrer">YouTube <span class="sr-only">(nuova scheda)</span></a>
<a href="https://www.instagram.com/altrove_podcast/" target="_blank" rel="noopener noreferrer">Instagram <span class="sr-only">(nuova scheda)</span></a>
</nav>
<p class="small">P. IVA <?= e($config['owner']['vat']) ?></p>
</footer>
<?php if (whatsapp($config)): ?><a class="whatsapp-fixed" data-whatsapp="fisso" href="<?= e(whatsapp($config)) ?>" target="_blank" rel="noopener noreferrer" aria-label="Scrivimi su WhatsApp (nuova scheda)">WhatsApp</a><?php endif; ?>
</body>
</html>

