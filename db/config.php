<?php
session_start();

$host = 'localhost';
$dbname = 'resume_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Определяем базовый путь
define('BASE_URL', '/Site/Asiakkaan-portfolio-main/db');
?>