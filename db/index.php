<?php
require __DIR__ . '/config.php';

// Проверяем статус пользователя
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$current_user_id = $_SESSION['user_id'] ?? null;

// Получаем все резюме с информацией о создателе
$resumes = $pdo->query("
    SELECT r.*, u.username as author_name 
    FROM resumes r 
    LEFT JOIN users u ON r.user_id = u.id 
    ORDER BY r.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>База резюме</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 30px; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .container { max-width: 800px; margin: 0 auto; }
        .resume-card {
            background: white; border-radius: 12px; padding: 25px;
            margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex; align-items: center; gap: 20px; position: relative;
        }
        .avatar {
            width: 60px; height: 60px; border-radius: 50%;
            background: #4CAF50; color: white; display: flex;
            align-items: center; justify-content: center;
            font-size: 24px; font-weight: bold; flex-shrink: 0;
        }
        .info { flex: 1; }
        .name { font-size: 20px; font-weight: bold; color: #333; }
        .job { color: #666; font-size: 16px; margin: 5px 0; }
        .age { color: #999; font-size: 14px; }
        .author { color: #bbb; font-size: 12px; margin-top: 3px; }
        .admin-actions { display: flex; gap: 8px; }
        .btn {
            padding: 8px 14px; border: none; border-radius: 6px;
            cursor: pointer; font-size: 14px; text-decoration: none;
            display: inline-block; transition: background 0.2s;
        }
        .btn-edit { background: #2196F3; color: white; }
        .btn-edit:hover { background: #0b7dda; }
        .btn-delete { background: #f44336; color: white; }
        .btn-delete:hover { background: #da190b; }
        .btn-add {
            background: #4CAF50; color: white; padding: 12px 24px;
            font-size: 16px; margin-bottom: 20px; display: inline-block;
        }
        .btn-add:hover { background: #45a049; }
        .admin-bar {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 20px;
            flex-wrap: wrap; gap: 10px;
        }
        .admin-bar-right { display: flex; align-items: center; gap: 10px; }
        .auth-links {
            text-align: center; margin-top: 20px;
            display: flex; gap: 15px; justify-content: center;
        }
        .auth-links a {
            color: #999; text-decoration: none; font-size: 14px;
        }
        .auth-links a:hover { text-decoration: underline; color: #666; }
        .admin-badge {
            background: #ff9800; color: white; padding: 5px 12px;
            border-radius: 20px; font-size: 12px;
        }
        .user-badge {
            background: #4CAF50; color: white; padding: 5px 12px;
            border-radius: 20px; font-size: 12px;
        }
        .empty-message {
            text-align: center; color: #999; font-size: 18px; padding: 50px 0;
        }
        .count-info {
            text-align: center; color: #999; margin-top: 20px; font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📄 База резюме</h1>
        
        <?php if ($is_logged_in): ?>
            <div class="admin-bar">
                <a href="<?= BASE_URL ?>/add_resume.php" class="btn btn-add">➕ Добавить резюме</a>
                <div class="admin-bar-right">
                    <?php if ($is_admin): ?>
                        <span class="admin-badge">Администратор: <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <?php else: ?>
                        <span class="user-badge">Пользователь: <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/logout.php" class="btn btn-delete">Выйти</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (count($resumes) > 0): ?>
            <?php foreach ($resumes as $resume): ?>
                <div class="resume-card">
                    <div class="avatar">
                        <?= mb_substr(htmlspecialchars($resume['name']), 0, 1) ?>
                    </div>
                    <div class="info">
                        <div class="name"><?= htmlspecialchars($resume['name']) ?></div>
                        <div class="job"><?= htmlspecialchars($resume['job_title']) ?></div>
                        <div class="age">Возраст: <?= $resume['age'] ?> лет</div>
                        <?php if ($resume['author_name']): ?>
                            <div class="author">Создал: <?= htmlspecialchars($resume['author_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_admin || ($is_logged_in && $resume['user_id'] === $current_user_id)): ?>
                        <div class="admin-actions">
                            <a href="<?= BASE_URL ?>/edit_resume.php?id=<?= $resume['id'] ?>" class="btn btn-edit">✏️ Изменить</a>
                            <a href="<?= BASE_URL ?>/delete_resume.php?id=<?= $resume['id'] ?>" 
                               class="btn btn-delete"
                               onclick="return confirm('Удалить это резюме?');">🗑️ Удалить</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-message">
                Нет данных. Зарегистрируйтесь и добавьте первое резюме!
            </div>
        <?php endif; ?>
        
        <?php if (!$is_logged_in): ?>
            <div class="auth-links">
                <a href="<?= BASE_URL ?>/login.php">🔑 Войти</a>
                <a href="<?= BASE_URL ?>/register.php">📝 Зарегистрироваться</a>
            </div>
        <?php endif; ?>
        
        <div class="count-info">
            Всего резюме: <?= count($resumes) ?>
        </div>
    </div>
</body>
</html>
