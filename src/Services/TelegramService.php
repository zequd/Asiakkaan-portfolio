<?php

class TelegramService
{
    private $token;
    private $chatId;
    private $lastError = '';

    public function __construct($token, $chatId)
    {
        $this->token = $token;
        $this->chatId = $chatId;
    }

    public function send($text)
    {
        $this->lastError = '';

        if ($this->token === '' || $this->chatId === '') {
            $this->lastError = 'No token or chat id';
            return false;
        }

        $url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';

        $fields = array(
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if ($response === false) {
            $this->lastError = 'Network: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        $answer = json_decode($response, true);

        if (isset($answer['ok']) && $answer['ok'] === true) {
            return true;
        }

        if (isset($answer['description'])) {
            $this->lastError = 'Telegram: ' . $answer['description'];
        } else {
            $this->lastError = 'Bad response';
        }

        return false;
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}
