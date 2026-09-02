```php
<?php
// Подключаемся к MySQL БЕЗ указания базы
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $password);
    
    // Создаём базу данных
    $sql = "CREATE DATABASE IF NOT EXISTS resume_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    echo "✅ База данных resume_db создана!";
    
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>
```

---
