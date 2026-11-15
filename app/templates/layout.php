<?php
use Altrove\Assets; use Altrove\Seo;
require __DIR__.'/partials/icons.php';
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page['title']) ?></title><meta name="description" content="<?= e($page['description']) ?>"><link rel="canonical" href="<?= e($config['url'].$path) ?>">
<?php if(!empty($page['noindex'])): ?><meta name="robots" content="noindex, follow"><?php endif; ?>
<meta property="og:type" content="website"><meta property="og:locale" content="it_IT"><meta property="og:site_name" content="Altrove Podcast"><meta property="og:title" content="<?= e($page['title']) ?>"><meta property="og:description" content="<?= e($page['description']) ?>"><meta property="og:url" content="<?= e($config['url'].$path) ?>"><meta property="og:image" content="<?= e($config['url']) ?>/assets/og/<?= e($id==='proposte'?'candidati':$id) ?>.jpg"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta property="og:image:alt" content="Altrove Podcast · <?= e($page['h1']) ?>">
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="<?= e($page['title']) ?>"><meta name="twitter:description" content="<?= e($page['description']) ?>"><meta name="twitter:image" content="<?= e($config['url']) ?>/assets/og/<?= e($id==='proposte'?'candidati':$id) ?>.jpg">
<link rel="icon" href="/assets/favicon/favicon.ico" sizes="any"><link rel="icon" href="/assets/favicon/favicon.svg" type="image/svg+xml"><link rel="apple-touch-icon" href="/assets/favicon/apple-touch-icon.png"><link rel="manifest" href="/site.webmanifest"><meta name="theme-color" content="#110C2E">
<link rel="preload" href="/assets/fonts/tenor-sans-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
<?php if(Assets::get('css')): ?><link rel="stylesheet" href="<?= e(Assets::get('css')) ?>"><?php endif; ?>
<script type="application/ld+json" nonce="<?= e($nonce) ?>"><?= json_encode(Seo::graph($config,$page,$path),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<?php if(Assets::get('js')): ?><script type="module" src="<?= e(Assets::get('js')) ?>" nonce="<?= e($nonce) ?>"></script><?php endif; ?>
<?php if($id==='candidati'&&$config['turnstile']): ?><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" nonce="<?= e($nonce) ?>" async defer></script><?php endif; ?>
<?php if($config['umami_id']!==''): ?><script defer src="<?= e($config['umami_src']) ?>" data-website-id="<?= e($config['umami_id']) ?>" data-do-not-track="true" nonce="<?= e($nonce) ?>"></script><?php endif; ?>
</head>
<body class="pg<?= in_array($id,['candidati','proposte','grazie'],true)?' page-candidati':'' ?>" data-page="<?= e($id) ?>">
<a class="skip-link" href="#contenuto">Vai al contenuto</a>
<div class="sky-stage" aria-hidden="true"><div class="sky"></div><div class="sky b"></div><canvas class="skyc" data-galaxy></canvas></div>
<header class="nav"><a class="brand" href="/" aria-label="Altrove Podcast, pagina iniziale"><span class="lamp" aria-hidden="true"></span><span class="serif">ALTROVE</span></a><div class="nr"><button class="motion-toggle ib" type="button" data-motion-toggle hidden aria-pressed="false" aria-label="Metti in pausa le animazioni"><?= icon('pause') ?></button><a class="pill" href="/candidati/"<?= $id==='candidati'?' aria-current="page"':'' ?>>Candidati</a></div><span class="nn" aria-hidden="true"></span></header>
<main id="contenuto"<?= in_array($id,['candidati','proposte','grazie'],true)?' class="cand'.($id==='proposte'?' plans-page':'').'"':'' ?>><?php require __DIR__.'/pages/page.php'; ?></main>
<div class="creator-wrap"><?php require __DIR__.'/partials/creator.php'; ?></div>
<footer class="ft"><?php if(!in_array($id,['candidati','proposte','grazie'],true)): ?><p class="serif fw"><b>ALTROVE</b> <span class="mute">· Storie oltre il visibile</span></p><?php endif; ?><nav class="fl" aria-label="Collegamenti legali"><a href="/privacy/">Privacy</a><a href="/cookie/">Cookie</a><button class="privacy-control" type="button" data-video-revoke hidden>Disattiva i video YouTube</button></nav><p class="legal">Alessia Ventura · P.IVA <?= e($config['owner']['vat']) ?></p><p class="small" data-video-status role="status" aria-live="polite" hidden></p></footer>
</body></html>
