<?php

class Router
{
    private $routes;

    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    public function run($uri)
    {
        $path = $this->path($uri);

        if (!isset($this->routes[$path])) {
            $this->notFound();
            return;
        }

        $class = $this->routes[$path][0];
        $method = $this->routes[$path][1];

        $file = __DIR__ . '/../Controllers/' . $class . '.php';

        if (!file_exists($file)) {
            $this->notFound();
            return;
        }

        require_once $file;

        $controller = new $class();
        $controller->$method();
    }

    private function path($uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if ($path === false || $path === null || $path === '') {
            return '/';
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    private function notFound()
    {
        http_response_code(404);
        echo 'Page not found';
    }
}
