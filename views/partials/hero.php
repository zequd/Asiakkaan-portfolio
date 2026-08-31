<section class="hero" id="top">
    <div class="container-x hero__inner">
        <div class="hero__portrait">
            <img src="/assets/img/avatar.webp" alt="<?= e($site['name']) ?>" width="400" height="400">
        </div>

        <header class="hero__head">
            <h1 class="hero__name" aria-label="<?= e($site['name']) ?>"><?= chars($site['name']) ?></h1>

            <p class="hero__role">
                <?= e($site['role']) ?>
                <span class="hero__sep" aria-hidden="true">|</span>
                <?= e($site['company']) ?>
            </p>
        </header>

        <p class="hero__about lead"><?= e($site['about']) ?></p>

        <div class="hero__actions">
            <a class="hero__cta" href="https://t.me/<?= e($site['telegram']) ?>" target="_blank" rel="noopener noreferrer">Message on Telegram</a>
            <a class="hero__cta hero__cta--ghost" href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
        </div>

        <div class="hero__worked">
            <p class="hero__worked-label">Worked with</p>

            <ul class="hero__logos">
                <?php foreach ($experience as $job): ?>
                    <li>
                        <img src="<?= e($job['icon']) ?>" alt="<?= e($job['company']) ?>"
                            width="720" height="280" loading="lazy">
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
