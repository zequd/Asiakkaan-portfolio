<section class="hero" id="top">
    <div class="hero__scrim" aria-hidden="true"></div>
    <div class="hero__bloom" aria-hidden="true"></div>

    <div class="orbit" aria-hidden="true">
        <?php
        $seats = array(
            array('size' => 1, 'phase' => 0.875, 'px' => 0.7071, 'py' => -0.7071),
            array('size' => 0.9, 'phase' => 0.125, 'px' => -0.7071, 'py' => -0.7071),
            array('size' => 0.86, 'phase' => 0.375, 'px' => -0.7071, 'py' => 0.7071),
            array('size' => 0.94, 'phase' => 0.625, 'px' => 0.7071, 'py' => 0.7071),
        );
        ?>
        <?php foreach ($experience as $i => $job): ?>
            <?php $seat = $seats[$i % count($seats)]; ?>
            <div class="orbit__chip" style="--size: <?= $seat['size'] ?>; --px: <?= $seat['px'] ?>; --py: <?= $seat['py'] ?>; --phase: <?= -$seat['phase'] ?>">
                <div class="orbit__pop">
                    <img src="<?= e($job['icon']) ?>" alt="<?= e($job['company']) ?>"
                        width="720" height="280" loading="eager" decoding="async" draggable="false">
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="container-x hero__inner">
        <header class="hero__head">
            <h1 class="hero__name" aria-label="<?= e($site['name']) ?>"><?= chars($site['name']) ?></h1>

            <p class="hero__role">
                <?= e($site['role']) ?>
                <span class="hero__sep" aria-hidden="true">|</span>
                <?= e($site['company']) ?>
            </p>
        </header>

        <div class="hero__phone">
            <canvas class="hero__canvas" id="phone-canvas"></canvas>

            <div class="hero__phone-fallback" id="phone-fallback" hidden>
                <img src="/assets/video/screen-poster.jpg" alt="" width="604" height="1314" loading="lazy">
            </div>

            <video class="hero__video-src" id="phone-video" muted loop playsinline
                preload="auto" poster="/assets/video/screen-poster.jpg" aria-hidden="true" tabindex="-1">
                <source src="/assets/video/screen.webm" type="video/webm">
                <source src="/assets/video/screen.mp4" type="video/mp4">
            </video>
        </div>

        <p class="hero__about lead"><?= e($site['about']) ?></p>
    </div>
</section>
