<?php
// Подключаемся к базе
$host = 'localhost';
$dbname = 'resume_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔄 Начинаю наполнение...\n\n";
    
    // Очищаем таблицу (чтобы не было дублей)
    $pdo->exec("TRUNCATE TABLE resumes");
    
    // Болванки (тестовые данные)
    $resumes = [
        [
            'name' => 'Иван Петров',
            'age' => 28,
            'job_title' => 'Веб-разработчик'
        ],
        [
            'name' => 'Мария Смирнова',
            'age' => 25,
            'job_title' => 'Дизайнер интерфейсов'
        ],
        [
            'name' => 'Алексей Иванов',
            'age' => 32,
            'job_title' => 'Менеджер проектов'
        ],
        [
            'name' => 'Елена Козлова',
            'age' => 27,
            'job_title' => 'Аналитик данных'
        ],
        [
            'name' => 'Дмитрий Соколов',
            'age' => 30,
            'job_title' => 'DevOps инженер'
        ],
        [
            'name' => 'Анна Волкова',
            'age' => 24,
            'job_title' => 'HR-специалист'
        ],
        [
            'name' => 'Сергей Николаев',
            'age' => 35,
            'job_title' => 'Архитектор ПО'
        ],
    ];
    
    // Подготавливаем запрос
    $stmt = $pdo->prepare("INSERT INTO resumes (name, age, job_title) VALUES (:name, :age, :job_title)");
    
    // Добавляем каждое резюме
    foreach ($resumes as $resume) {
        $stmt->execute($resume);
        echo "✅ Добавлен: {$resume['name']} — {$resume['job_title']}\n";
    }
    
    echo "\n==========================\n";
    echo "Всего добавлено: " . count($resumes) . " резюме\n";
    echo "==========================\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>
