<?php if(!empty($page['faq'])): ?>
<section class="sec reading faq" aria-labelledby="faq-titolo"><h2 class="serif h2" id="faq-titolo">Le domande <span class="mute">che potresti avere.</span></h2><div class="faq-list"><?php foreach($page['faq'] as [$question,$answer]): ?><details><summary><span class="serif"><?= e($question) ?></span><?= icon('plus') ?></summary><p><?= e($answer) ?></p></details><?php endforeach; ?></div></section>
<?php endif; ?>
