<?php
$host = 'localhost';
$dbname = 'resume_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔄 Обновляю базу данных...\n\n";
    
    // Создаём таблицу пользователей
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Таблица users создана!\n";
    
    // Добавляем колонку user_id в таблицу resumes (если её нет)
    $columns = $pdo->query("SHOW COLUMNS FROM resumes LIKE 'user_id'")->fetchAll();
    if (count($columns) == 0) {
        $pdo->exec("ALTER TABLE resumes ADD COLUMN user_id INT DEFAULT NULL");
        echo "✅ Колонка user_id добавлена в таблицу resumes!\n";
    } else {
        echo "✅ Колонка user_id уже существует!\n";
    }
    
    // Создаём администратора (если нет)
    $admin_username = 'admin';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role) VALUES (:username, :password, 'admin')");
    $stmt->execute(['username' => $admin_username, 'password' => $admin_password]);
    
    echo "✅ Администратор создан (admin / admin123)\n";
    
    echo "\n==========================\n";
    echo "База данных обновлена!\n";
    echo "==========================\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>