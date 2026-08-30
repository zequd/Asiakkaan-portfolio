<!doctype html>
<html lang="en" data-motion="on">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($site['name']) ?> — <?= e($site['role']) ?></title>
<meta name="description" content="<?= e($site['meta']) ?>">
<meta name="theme-color" content="#111318">

<meta property="og:type" content="profile">
<meta property="og:title" content="<?= e($site['name']) ?> — <?= e($site['role']) ?>">
<meta property="og:description" content="<?= e($site['meta']) ?>">

<link rel="preload" href="/assets/fonts/Outfit-Variable.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/Satoshi-Variable.woff2" as="font" type="font/woff2" crossorigin>

<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/hero.css">

<script>
(function () {
    try {
        var param = new URLSearchParams(location.search).get('motion');
        var saved = localStorage.getItem('motion-preference');
        var local = /^(localhost|127\.0\.0\.1|\[::1\])$/.test(location.hostname);
        var on;

        if (param === 'on' || param === 'off') {
            on = param === 'on';
        } else if (saved === 'on' || saved === 'off') {
            on = saved === 'on';
        } else if (local) {
            on = true;
        } else {
            on = !matchMedia('(prefers-reduced-motion: reduce)').matches;
        }

        document.documentElement.dataset.motion = on ? 'on' : 'off';
    } catch (err) {
        document.documentElement.dataset.motion = 'on';
    }
})();
</script>
</head>
<body>

<?= $content ?>

<script src="/assets/js/main.js"></script>
<script src="/assets/js/orbit.js"></script>

<script type="importmap">
{
    "imports": {
        "three": "/assets/lib/three/three.module.min.js",
        "three/addons/": "/assets/lib/three/jsm/"
    }
}
</script>
<script type="module" src="/assets/js/phone.js"></script>

</body>
</html>
