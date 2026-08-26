<?php

require __DIR__ . '/../src/Services/TelegramService.php';

$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    echo "No .env\n";
    exit(1);
}

$env = parse_ini_file($envFile);

$token = isset($env['TELEGRAM_BOT_TOKEN']) ? $env['TELEGRAM_BOT_TOKEN'] : '';
$chatId = isset($env['TELEGRAM_CHAT_ID']) ? $env['TELEGRAM_CHAT_ID'] : '';

$telegram = new TelegramService($token, $chatId);

if ($telegram->send('Test message from portfolio site')) {
    echo "Sent\n";
    exit(0);
}

echo "Failed\n";
echo $telegram->getLastError() . "\n";
exit(1);
