<?php
// Подключаемся к базе данных
$host = 'localhost';
$dbname = 'resume_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔄 Создаю таблицу admin...\n\n";
    
    // Создаём таблицу для администратора
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )
    ");
    
    echo "✅ Таблица admin создана!\n";
    
    // Добавляем администратора (пароль: admin123)
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (:username, :password) ON DUPLICATE KEY UPDATE password = :password");
    $stmt->execute(['username' => $username, 'password' => $password]);
    
    echo "✅ Администратор добавлен!\n";
    echo "\n==========================\n";
    echo "Логин: admin\n";
    echo "Пароль: admin123\n";
    echo "==========================\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>
