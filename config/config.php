<?php

function config($key, $default = '')
{
    static $env = null;

    if ($env === null) {
        $file = __DIR__ . '/../.env';

        if (file_exists($file)) {
            $env = parse_ini_file($file);
        } else {
            $env = array();
        }
    }

    if (isset($env[$key]) && $env[$key] !== '') {
        return $env[$key];
    }

    return $default;
}
