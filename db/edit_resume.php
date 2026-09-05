<?php
require __DIR__ . '/config.php';

// Проверяем, что пользователь вошёл
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

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

if (!$is_admin && !$is_author) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $job_title = trim($_POST['job_title'] ?? '');
    
    if (empty($name) || empty($age) || empty($job_title)) {
        $error = 'Все поля обязательны!';
    } elseif (!is_numeric($age) || $age < 16 || $age > 100) {
        $error = 'Возраст должен быть от 16 до 100 лет!';
    } else {
        $stmt = $pdo->prepare("UPDATE resumes SET name = :name, age = :age, job_title = :job_title WHERE id = :id");
        $stmt->execute(['name' => $name, 'age' => $age, 'job_title' => $job_title, 'id' => $id]);
        $success = 'Резюме обновлено!';
        $resume = ['name' => $name, 'age' => $age, 'job_title' => $job_title];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать резюме</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 30px; }
        .container {
            max-width: 500px; margin: 0 auto; background: white;
            padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #333; margin-bottom: 25px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input {
            width: 100%; padding: 12px; margin-bottom: 20px;
            border: 1px solid #ddd; border-radius: 8px;
            box-sizing: border-box; font-size: 16px;
        }
        button {
            width: 100%; padding: 14px; background: #2196F3;
            color: white; border: none; border-radius: 8px;
            font-size: 16px; cursor: pointer;
        }
        button:hover { background: #0b7dda; }
        .error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .back-link { text-align: center; margin-top: 15px; }
        .back-link a { color: #999; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Редактировать резюме</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label>Имя:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($resume['name']) ?>" required>
            
            <label>Возраст:</label>
            <input type="number" name="age" min="16" max="100" value="<?= $resume['age'] ?>" required>
            
            <label>Должность:</label>
            <input type="text" name="job_title" value="<?= htmlspecialchars($resume['job_title']) ?>" required>
            
            <button type="submit">Сохранить изменения</button>
        </form>
        
        <div class="back-link">
            <a href="<?= BASE_URL ?>/index.php">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html>