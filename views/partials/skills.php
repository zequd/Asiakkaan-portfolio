<section class="section section--tight" id="skills">
    <div class="container-x">
        <header class="section-head reveal">
            <p class="eyebrow"><?= words('What I bring') ?></p>
            <h2 class="section-title"><?= words('My Skills') ?></h2>
        </header>

        <div class="skills">
            <?php foreach ($skills as $i => $group): ?>
                <div class="skills__group reveal-up">
                    <p class="skills__index"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></p>
                    <h3 class="skills__title"><?= e($group['title']) ?></h3>
                    <p class="skills__note"><?= e($group['note']) ?></p>

                    <ul class="skills__list">
                        <?php foreach ($group['items'] as $item): ?>
                            <li class="skills__item">
                                <span class="skills__marker" aria-hidden="true"></span>
                                <?= e($item) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
