<?php

$envFile = __DIR__ . '/../.env';
$env = array();

if (file_exists($envFile)) {
    $parsed = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (is_array($parsed)) {
        $env = $parsed;
    }
}

$host = isset($env['DB_HOST']) && $env['DB_HOST'] !== ''
    ? trim((string) $env['DB_HOST'])
    : 'localhost';

$port = isset($env['DB_PORT']) && $env['DB_PORT'] !== ''
    ? (int) $env['DB_PORT']
    : 3306;

$dbname = isset($env['DB_NAME']) && $env['DB_NAME'] !== ''
    ? trim((string) $env['DB_NAME'])
    : 'resume_db';

$user = isset($env['DB_USER']) && $env['DB_USER'] !== ''
    ? (string) $env['DB_USER']
    : 'root';

$password = isset($env['DB_PASS'])
    ? (string) $env['DB_PASS']
    : '';

if (!preg_match('/^[A-Za-z0-9_]+$/', $dbname)) {
    die('Неверное имя базы данных в .env. Используй только буквы, цифры и _.');
}

try {
    /*
     * Сначала подключаемся к самому MySQL-серверу.
     * Если базы ещё нет, создаём её автоматически.
     */
    $serverPdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $user,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );

    $serverPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `{$dbname}` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );

    $pdo->exec('SET NAMES utf8mb4');

} catch (PDOException $e) {
    $message = $e->getMessage();

    die(
        'Ошибка подключения к MySQL.<br><br>' .
        '<strong>Проверь:</strong><br>' .
        '1. Запущен ли MySQL.<br>' .
        '2. Правильные ли DB_HOST, DB_PORT, DB_USER и DB_PASS в .env.<br>' .
        '3. Есть ли у пользователя права на создание базы данных.<br><br>' .
        '<small>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small>'
    );
}
