<?php
require __DIR__ . '/config.php';

// Проверяем, что пользователь вошёл
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Получаем резюме
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
$stmt->execute(['id' => $id]);
$resume = $stmt->fetch();

if (!$resume) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Проверяем права: админ или автор
$is_admin = $_SESSION['is_admin'] ?? false;
$is_author = $resume['user_id'] === $_SESSION['user_id'];

if ($is_admin || $is_author) {
    // Удаляем резюме
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

// Перенаправляем обратно
header('Location: ' . BASE_URL . '/index.php');
exit;
?>