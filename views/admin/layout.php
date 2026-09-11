<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Admin</title>
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<header class="top">
    <div class="wrap">
        <a class="brand" href="/admin">Admin</a>

        <?php foreach ($menu as $item): ?>
            <a href="<?= e($item['url']) ?>"<?= $item['key'] === $active ? ' class="on"' : '' ?>><?= e($item['label']) ?></a>
        <?php endforeach; ?>

        <a class="spacer" href="/" target="_blank">View site</a>
    </div>
</header>

<main class="wrap">
    <h1><?= e($title) ?></h1>

    <?= $body ?>
</main>

</body>
</html>
