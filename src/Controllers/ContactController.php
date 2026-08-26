<?php

require_once __DIR__ . '/../Services/TelegramService.php';

class ContactController
{
    public function send()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Bad method');
            return;
        }

        if ($this->field('website') !== '') {
            $this->respond(true, '');
            return;
        }

        $name = $this->field('name');
        $contact = $this->field('contact');
        $message = $this->field('message');

        $error = $this->validate($name, $contact, $message);

        if ($error !== '') {
            $this->respond(false, $error);
            return;
        }

        if ($this->tooSoon()) {
            $this->respond(false, 'Too fast');
            return;
        }

        $telegram = $this->makeTelegram();

        if (!$telegram->send($this->buildText($name, $contact, $message))) {
            $this->respond(false, 'Send failed');
            return;
        }

        $_SESSION['contact_last_time'] = time();
        $this->respond(true, '');
    }

    private function field($name)
    {
        if (!isset($_POST[$name])) {
            return '';
        }

        return trim($_POST[$name]);
    }

    private function validate($name, $contact, $message)
    {
        if ($name === '' || $contact === '' || $message === '') {
            return 'Empty fields';
        }

        if (mb_strlen($name) > 100) {
            return 'Name too long';
        }

        if (mb_strlen($contact) > 100) {
            return 'Contact too long';
        }

        if (mb_strlen($message) > 2000) {
            return 'Message too long';
        }

        return '';
    }

    private function tooSoon()
    {
        if (!isset($_SESSION['contact_last_time'])) {
            return false;
        }

        return time() - $_SESSION['contact_last_time'] < 30;
    }

    private function buildText($name, $contact, $message)
    {
        return '<b>New message from portfolio</b>' . "\n\n"
            . '<b>Name:</b> ' . htmlspecialchars($name) . "\n"
            . '<b>Contact:</b> ' . htmlspecialchars($contact) . "\n\n"
            . htmlspecialchars($message);
    }

    private function makeTelegram()
    {
        $env = parse_ini_file(__DIR__ . '/../../.env');

        $token = isset($env['TELEGRAM_BOT_TOKEN']) ? $env['TELEGRAM_BOT_TOKEN'] : '';
        $chatId = isset($env['TELEGRAM_CHAT_ID']) ? $env['TELEGRAM_CHAT_ID'] : '';

        return new TelegramService($token, $chatId);
    }

    private function isAjax()
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return false;
        }

        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function respond($ok, $error)
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json');

            if ($ok) {
                echo json_encode(array('ok' => true));
            } else {
                echo json_encode(array('ok' => false, 'error' => $error));
            }

            return;
        }

        if ($ok) {
            header('Location: /?sent=1');
        } else {
            header('Location: /?sent=0');
        }
    }
}
