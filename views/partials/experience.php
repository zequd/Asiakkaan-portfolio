<section class="section" id="experience">
    <div class="container-x">
        <header class="section-head reveal">
            <p class="eyebrow"><?= words("Where I've worked") ?></p>
            <h2 class="section-title"><?= words('My Experience') ?></h2>
        </header>

        <div class="cards" role="list">
            <?php foreach ($experience as $job): ?>
                <div class="card" role="listitem" tabindex="0"
                    aria-label="<?= e($job['company']) ?>, <?= e($job['period']) ?>"
                    style="background-image: url('<?= e($job['card']) ?>')">
                    <div class="card__shade"></div>

                    <div class="card__body">
                        <p class="card__period"><?= e($job['period']) ?></p>
                        <h3 class="card__company"><?= e($job['company']) ?></h3>
                        <p class="card__role"><?= e($job['role']) ?></p>
                        <p class="card__summary"><?= e($job['summary']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
