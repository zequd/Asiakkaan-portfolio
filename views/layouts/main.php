<!doctype html>
<html lang="en">
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
</head>
<body>

<?= $content ?>

</body>
</html>
