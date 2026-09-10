<a class="skip-link" href="#experience">Skip to content</a>

<nav class="rail" aria-label="Section navigation">
    <div class="rail__track">
        <span class="rail__line" aria-hidden="true"></span>
        <span class="rail__fill" aria-hidden="true"></span>

        <?php foreach ($sections as $section): ?>
            <button type="button" class="rail__stop" data-target="<?= e($section['id']) ?>">
                <span class="rail__stop-label"><?= e($section['label']) ?></span>
                <span class="rail__dot" aria-hidden="true"></span>
            </button>
        <?php endforeach; ?>

        <svg class="rail__goo" aria-hidden="true">
            <defs>
                <filter id="rail-goo" x="-100%" y="-100%" width="300%" height="300%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="3" result="blur" />
                    <feColorMatrix in="blur" mode="matrix"
                        values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 20 -9" />
                </filter>
                <linearGradient id="rail-drop" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#2563eb" />
                    <stop offset="100%" stop-color="#00d2ff" />
                </linearGradient>
            </defs>
            <g filter="url(#rail-goo)">
                <ellipse id="rail-bubble" cx="28" cy="0" rx="8" ry="8" fill="url(#rail-drop)" />
            </g>
        </svg>

        <button class="rail__grip" type="button" role="slider"
            aria-label="Scroll position" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></button>
    </div>
</nav>
