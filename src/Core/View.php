<?php

class View
{

    public static function page($name, $data = array())
    {
        $data['content'] = self::render('pages/' . $name, $data);

        echo self::render('layouts/main', $data);
    }

    public static function render($template, $data = array())
    {
        $file = __DIR__ . '/../../views/' . $template . '.php';

        if (!file_exists($file)) {
            return '';
        }

        extract($data);

        ob_start();
        require $file;

        return ob_get_clean();
    }
}
