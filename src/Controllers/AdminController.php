<?php

class AdminController
{
    public function index()
    {
        $this->show('overview', 'Overview', 'overview');
    }

    public function profile()
    {
        $this->show('profile', 'Profile', 'profile');
    }

    public function experience()
    {
        $this->show('experience', 'Experience', 'experience');
    }

    public function skills()
    {
        $this->show('skills', 'Skills', 'skills');
    }

    public function credentials()
    {
        $this->show('credentials', 'Credentials', 'credentials');
    }

    public function sections()
    {
        $this->show('sections', 'Menu', 'sections');
    }

    public function contact()
    {
        $this->show('contact', 'Contact', 'contact');
    }

    private function show($view, $title, $active)
    {
        if (!$this->allowed()) {
            return;
        }

        $data = require __DIR__ . '/../../config/content.php';

        $data['title'] = $title;
        $data['active'] = $active;
        $data['menu'] = $this->menu();
        $data['telegram'] = $this->telegram();
        $data['body'] = View::render('admin/' . $view, $data);

        echo View::render('admin/layout', $data);
    }

    private function menu()
    {
        return array(
            array('key' => 'overview', 'url' => '/admin', 'label' => 'Overview'),
            array('key' => 'profile', 'url' => '/admin/profile', 'label' => 'Profile'),
            array('key' => 'experience', 'url' => '/admin/experience', 'label' => 'Experience'),
            array('key' => 'skills', 'url' => '/admin/skills', 'label' => 'Skills'),
            array('key' => 'credentials', 'url' => '/admin/credentials', 'label' => 'Credentials'),
            array('key' => 'sections', 'url' => '/admin/sections', 'label' => 'Menu'),
            array('key' => 'contact', 'url' => '/admin/contact', 'label' => 'Contact'),
        );
    }

    private function telegram()
    {
        $env = $this->env();

        return array(
            'chat_id' => isset($env['TELEGRAM_CHAT_ID']) ? $env['TELEGRAM_CHAT_ID'] : '',
        );
    }

    private function env()
    {
        $file = __DIR__ . '/../../.env';

        if (!file_exists($file)) {
            return array();
        }

        $env = parse_ini_file($file);

        if (!is_array($env)) {
            return array();
        }

        return $env;
    }

    private function allowed()
    {
        $env = $this->env();

        $user = isset($env['ADMIN_USER']) ? $env['ADMIN_USER'] : '';
        $hash = isset($env['ADMIN_PASSWORD_HASH']) ? $env['ADMIN_PASSWORD_HASH'] : '';

        $givenUser = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $givenPass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

        if ($user !== '' && $hash !== '' && $givenUser === $user && password_verify($givenPass, $hash)) {
            return true;
        }

        header('WWW-Authenticate: Basic realm="Portfolio admin"');
        http_response_code(401);
        echo 'Access denied';

        return false;
    }
}
