<?php
require __DIR__ . '/config.php';

// Если уже вошёл — на главную
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Проверки
    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля!';
    } elseif (strlen($username) < 3) {
        $error = 'Имя должно быть не короче 3 символов!';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не короче 6 символов!';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают!';
    } else {
        // Проверяем, нет ли уже такого пользователя
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким именем уже существует!';
        } else {
            // Создаём пользователя
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
            $stmt->execute(['username' => $username, 'password' => $hashed_password]);
            
            $success = 'Регистрация успешна! Теперь войдите.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .register-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>📝 Регистрация</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
            <div class="back-link">
                <a href="<?= BASE_URL ?>/login.php">→ Перейти ко входу</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <label>Имя пользователя:</label>
                <input type="text" name="username" required>
                
                <label>Пароль:</label>
                <input type="password" name="password" required>
                
                <label>Повторите пароль:</label>
                <input type="password" name="password_confirm" required>
                
                <button type="submit">Зарегистрироваться</button>
            </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="<?= BASE_URL ?>/index.php">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html>