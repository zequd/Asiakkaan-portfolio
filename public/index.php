<?php

if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/../src/Core/Router.php';
require_once __DIR__ . '/../src/Core/View.php';
require_once __DIR__ . '/../views/helpers.php';

$routes = require __DIR__ . '/../config/routes.php';

$router = new Router($routes);
$router->run($_SERVER['REQUEST_URI']);
