<?php

class HomeController
{
    public function index()
    {
        $content = require __DIR__ . '/../../config/content.php';

        View::page('home', $content);
    }
}
