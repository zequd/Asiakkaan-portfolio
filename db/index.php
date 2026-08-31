<?php
// Подключаемся к базе
$host = 'localhost';
$dbname = 'resume_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем все резюме
    $resumes = $pdo->query("SELECT * FROM resumes ORDER BY id DESC")->fetchAll();
    
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>База резюме</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 30px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .resume-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #4CAF50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .info {
            flex: 1;
        }
        
        .name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .job {
            color: #666;
            font-size: 16px;
            margin: 5px 0;
        }
        
        .age {
            color: #999;
            font-size: 14px;
        }
        
        .badge {
            background: #e8f5e9;
            color: #4CAF50;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📄 База резюме</h1>
        
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
                    </div>
                    <span class="badge">ID: <?= $resume['id'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #999;">Нет данных. Запустите seed.php</p>
        <?php endif; ?>
        
        <p style="text-align: center; color: #999; margin-top: 20px;">
            Всего резюме: <?= count($resumes) ?>
        </p>
    </div>
</body>
</html>
