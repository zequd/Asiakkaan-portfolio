```php
<?php
// Настройки подключения
$host = 'localhost'; // адрес сервера (всегда localhost для локального)
$dbname = 'resume_db'; // имя базы данных
$user = 'root'; // пользователь (по умолчанию root)
$password = ''; // пароль (по умолчанию пусто)

try {
    // Создаём подключение
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    
    // Настраиваем режим ошибок
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Устанавливаем кодировку (чтобы русские буквы работали)
    $pdo->exec("SET NAMES utf8mb4");
    
} catch (PDOException $e) {
    // Если ошибка — показываем её
    die("Ошибка подключения: " . $e->getMessage());
}
?>
```

---
