<section class="section section--tight" id="credentials">
    <div class="container-x">
        <hr class="rule" aria-hidden="true">

        <ul class="creds">
            <?php foreach ($credentials as $item): ?>
                <li class="creds__item reveal-up">
                    <p class="creds__kind"><?= e($item['kind']) ?></p>
                    <h3 class="creds__title"><?= e($item['title']) ?></h3>
                    <p class="creds__issuer"><?= e($item['issuer']) ?></p>
                    <p class="creds__date"><?= e($item['date']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
