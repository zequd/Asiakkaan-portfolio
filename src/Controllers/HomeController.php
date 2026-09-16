<?php

class HomeController
{
    public function index()
    {
        require __DIR__ . '/../../db/config.php';

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS site_content (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                content LONGTEXT NOT NULL,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $stmt = $pdo->prepare('SELECT content FROM site_content WHERE id = 1 LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row && !empty($row['content'])) {
            $content = json_decode($row['content'], true);
        }

        if (!isset($content) || !is_array($content)) {
            $content = require __DIR__ . '/../../config/content.php';
            $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            $save = $pdo->prepare(
                'INSERT INTO site_content (id, content, updated_at)
                 VALUES (1, :content, NOW())
                 ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = NOW()'
            );
            $save->execute(array(':content' => $json));
        }

        View::page('home', $content);
    }
}
