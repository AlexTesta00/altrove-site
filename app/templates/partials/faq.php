<?php if (!empty($page['faq'])): ?>
<section class="section prose" aria-labelledby="faq-titolo">
<h2 id="faq-titolo">Le domande che potresti avere</h2>
<?php foreach($page['faq'] as [$question,$answer]): ?>
<details><summary><?= e($question) ?></summary><p><?= e($answer) ?></p></details>
<?php endforeach; ?>
</section>
<?php endif; ?>

