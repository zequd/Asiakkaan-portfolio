<main>
    <section class="hero" id="top">
        <div class="container-x hero__inner">
            <header class="hero__head">
                <h1 class="hero__name"><?= e($site['name']) ?></h1>

                <p class="hero__role">
                    <?= e($site['role']) ?>
                    <span class="hero__sep" aria-hidden="true">|</span>
                    <?= e($site['company']) ?>
                </p>
            </header>

            <p class="hero__about lead"><?= e($site['about']) ?></p>
        </div>
    </section>
</main>
