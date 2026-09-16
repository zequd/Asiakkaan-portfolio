<?php

class AdminController
{
    public function index()
    {
        header('Location: /');
        exit;
    }

    public function login()
    {
        $this->jsonHeaders();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(array('success' => false, 'message' => 'Invalid request.'), 405);
        }

        $input = $this->readJson();
        $username = isset($input['username']) ? trim((string) $input['username']) : '';
        $password = isset($input['password']) ? (string) $input['password'] : '';
        $env = $this->env();
        $adminUser = isset($env['ADMIN_USER']) ? trim((string) $env['ADMIN_USER']) : '';
        $hash = isset($env['ADMIN_PASSWORD_HASH']) ? trim((string) $env['ADMIN_PASSWORD_HASH']) : '';

        if ($adminUser === '' || $hash === '') {
            $this->json(array('success' => false, 'message' => 'Admin credentials are not configured.'), 500);
        }

        if (!hash_equals($adminUser, $username) || !password_verify($password, $hash)) {
            $this->json(array('success' => false, 'message' => 'Invalid login or password.'), 401);
        }

        $this->startSession();
        session_regenerate_id(true);
        $_SESSION['portfolio_admin'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->json(array('success' => true, 'csrf' => $_SESSION['csrf_token']));
    }

    public function logout()
    {
        $this->jsonHeaders();
        $this->startSession();
        $_SESSION = array();

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
        $this->json(array('success' => true));
    }

    public function status()
    {
        $this->jsonHeaders();
        $logged = $this->isLoggedIn();
        $result = array('success' => true, 'loggedIn' => $logged);

        if ($logged) {
            $this->startSession();
            $result['csrf'] = $_SESSION['csrf_token'];
        }

        $this->json($result);
    }

    public function get()
    {
        $this->jsonHeaders();
        $this->requireLogin();
        $this->json(array('success' => true, 'content' => $this->loadContent()));
    }

    public function save()
    {
        $this->jsonHeaders();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(array('success' => false, 'message' => 'Invalid request.'), 405);
        }

        $raw = file_get_contents('php://input');
        if (strlen($raw) > 5 * 1024 * 1024) {
            $this->json(array('success' => false, 'message' => 'Request is too large.'), 413);
        }

        $input = json_decode($raw, true);
        $this->checkCsrf(isset($input['csrf']) ? $input['csrf'] : '');

        if (!isset($input['content']) || !is_array($input['content'])) {
            $this->json(array('success' => false, 'message' => 'Invalid content.'), 400);
        }

        $content = $this->normalizeContent($input['content']);
        $this->saveContent($content);
        $this->json(array('success' => true, 'message' => 'Changes saved.', 'content' => $content));
    }

    public function upload()
    {
        $this->jsonHeaders();
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(array('success' => false, 'message' => 'Invalid request.'), 405);
        }

        $this->checkCsrf(isset($_POST['csrf']) ? $_POST['csrf'] : '');

        if (!isset($_FILES['image'])) {
            $this->json(array('success' => false, 'message' => 'No image was uploaded.'), 400);
        }

        $file = $_FILES['image'];
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(array('success' => false, 'message' => 'Image upload failed.'), 400);
        }

        if ((int) $file['size'] > 8 * 1024 * 1024) {
            $this->json(array('success' => false, 'message' => 'Image is too large. Maximum size is 8 MB.'), 413);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->json(array('success' => false, 'message' => 'Invalid uploaded file.'), 400);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $extensions = array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        );

        if (!isset($extensions[$mime])) {
            $this->json(array('success' => false, 'message' => 'Only JPG, PNG, WEBP and GIF images are allowed.'), 415);
        }

        $directory = __DIR__ . '/../../public/assets/img/uploads';
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            $this->json(array('success' => false, 'message' => 'Could not create image directory.'), 500);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $destination = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->json(array('success' => false, 'message' => 'Could not save image.'), 500);
        }

        $this->json(array(
            'success' => true,
            'path' => '/assets/img/uploads/' . $filename,
            'filename' => $filename
        ));
    }

    public function credentialsUpdate()
    {
        $this->jsonHeaders();
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(array('success' => false, 'message' => 'Invalid request.'), 405);
        }

        $input = $this->readJson();
        $this->checkCsrf(isset($input['csrf']) ? $input['csrf'] : '');

        $currentUsername = isset($input['current_username']) ? trim((string) $input['current_username']) : '';
        $currentPassword = isset($input['current_password']) ? (string) $input['current_password'] : '';
        $newUsername = isset($input['new_username']) ? trim((string) $input['new_username']) : '';
        $newPassword = isset($input['new_password']) ? (string) $input['new_password'] : '';

        $env = $this->env();
        $storedUsername = isset($env['ADMIN_USER']) ? trim((string) $env['ADMIN_USER']) : '';
        $storedHash = isset($env['ADMIN_PASSWORD_HASH']) ? trim((string) $env['ADMIN_PASSWORD_HASH']) : '';

        if ($currentUsername === '' || $currentPassword === '') {
            $this->json(array('success' => false, 'message' => 'Enter your current login and password.'), 400);
        }

        if (!hash_equals($storedUsername, $currentUsername) || !password_verify($currentPassword, $storedHash)) {
            $this->json(array('success' => false, 'message' => 'Current login or password is incorrect.'), 401);
        }

        if ($newUsername === '' || strlen($newUsername) > 64) {
            $this->json(array('success' => false, 'message' => 'New login must contain 1–64 characters.'), 400);
        }

        if ($newPassword === '' || strlen($newPassword) < 8) {
            $this->json(array('success' => false, 'message' => 'New password must contain at least 8 characters.'), 400);
        }

        $env['ADMIN_USER'] = $newUsername;
        $env['ADMIN_PASSWORD_HASH'] = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->writeEnv($env);

        $this->startSession();
        session_regenerate_id(true);
        $_SESSION['portfolio_admin'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->json(array(
            'success' => true,
            'message' => 'Login and password changed successfully.',
            'csrf' => $_SESSION['csrf_token']
        ));
    }

    private function loadContent()
    {
        require __DIR__ . '/../../db/config.php';
        $this->ensureTable($pdo);
        $stmt = $pdo->prepare('SELECT content FROM site_content WHERE id = 1 LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row && !empty($row['content'])) {
            $content = json_decode($row['content'], true);
            if (is_array($content)) {
                return $content;
            }
        }

        $content = require __DIR__ . '/../../config/content.php';
        $this->saveContent($content);
        return $content;
    }

    private function saveContent($content)
    {
        require __DIR__ . '/../../db/config.php';
        $this->ensureTable($pdo);
        $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            $this->json(array('success' => false, 'message' => 'Could not encode content.'), 500);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO site_content (id, content, updated_at) VALUES (1, :content, NOW())
             ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = NOW()'
        );
        $stmt->execute(array(':content' => $json));
    }

    private function ensureTable($pdo)
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS site_content (
                id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
                content LONGTEXT NOT NULL,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function normalizeContent($content)
    {
        $default = require __DIR__ . '/../../config/content.php';
        $out = $default;

        if (isset($content['site']) && is_array($content['site'])) {
            foreach ($out['site'] as $key => $value) {
                if (array_key_exists($key, $content['site']) && is_scalar($content['site'][$key])) {
                    $out['site'][$key] = trim((string) $content['site'][$key]);
                }
            }
        }

        if (isset($content['experience']) && is_array($content['experience'])) {
            $out['experience'] = array();
            foreach ($content['experience'] as $x) {
                if (!is_array($x)) continue;
                $out['experience'][] = array(
                    'company' => $this->sv($x, 'company'),
                    'role' => $this->sv($x, 'role'),
                    'period' => $this->sv($x, 'period'),
                    'summary' => $this->sv($x, 'summary'),
                    'card' => $this->sv($x, 'card'),
                    'icon' => $this->sv($x, 'icon')
                );
            }
        }

        if (isset($content['skills']) && is_array($content['skills'])) {
            $out['skills'] = array();
            foreach ($content['skills'] as $x) {
                if (!is_array($x)) continue;
                $items = array();
                if (isset($x['items']) && is_array($x['items'])) {
                    foreach ($x['items'] as $i) {
                        if (is_scalar($i)) $items[] = trim((string) $i);
                    }
                }
                $out['skills'][] = array(
                    'title' => $this->sv($x, 'title'),
                    'note' => $this->sv($x, 'note'),
                    'items' => $items
                );
            }
        }

        if (isset($content['credentials']) && is_array($content['credentials'])) {
            $out['credentials'] = array();
            foreach ($content['credentials'] as $x) {
                if (!is_array($x)) continue;
                $out['credentials'][] = array(
                    'kind' => $this->sv($x, 'kind'),
                    'title' => $this->sv($x, 'title'),
                    'issuer' => $this->sv($x, 'issuer'),
                    'date' => $this->sv($x, 'date')
                );
            }
        }

        if (isset($content['sections']) && is_array($content['sections'])) {
            $out['sections'] = array();
            foreach ($content['sections'] as $x) {
                if (!is_array($x)) continue;
                $out['sections'][] = array(
                    'id' => $this->sv($x, 'id'),
                    'label' => $this->sv($x, 'label')
                );
            }
        }

        return $out;
    }

    private function sv($a, $k)
    {
        return isset($a[$k]) && is_scalar($a[$k]) ? trim((string) $a[$k]) : '';
    }

    private function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $this->json(array('success' => false, 'message' => 'Unauthorized.'), 401);
        }
    }

    private function isLoggedIn()
    {
        $this->startSession();
        if (empty($_SESSION['portfolio_admin']) || $_SESSION['portfolio_admin'] !== true) return false;
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }

    private function checkCsrf($token)
    {
        $this->startSession();
        $sessionToken = isset($_SESSION['csrf_token']) ? (string) $_SESSION['csrf_token'] : '';
        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, (string) $token)) {
            $this->json(array('success' => false, 'message' => 'Invalid security token.'), 403);
        }
    }

    private function startSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params(array('httponly' => true, 'secure' => $secure, 'samesite' => 'Lax'));
        session_start();
    }

    private function env()
    {
        $file = __DIR__ . '/../../.env';
        if (!file_exists($file)) return array();
        $env = parse_ini_file($file, false, INI_SCANNER_RAW);
        return is_array($env) ? $env : array();
    }

    private function writeEnv($env)
    {
        $file = __DIR__ . '/../../.env';
        $lines = array();
        $keys = array('DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'ADMIN_USER', 'ADMIN_PASSWORD_HASH', 'TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID');

        foreach ($keys as $key) {
            if (!array_key_exists($key, $env)) continue;
            $value = (string) $env[$key];
            if ($key === 'ADMIN_USER' || $key === 'ADMIN_PASSWORD_HASH') {
                $value = '"' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $value) . '"';
            }
            $lines[] = $key . '=' . $value;
        }

        $temp = $file . '.tmp';
        if (file_put_contents($temp, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX) === false || !rename($temp, $file)) {
            @unlink($temp);
            $this->json(array('success' => false, 'message' => 'Could not update .env. Make sure the file is writable by PHP.'), 500);
        }
    }

    private function readJson()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : array();
    }

    private function jsonHeaders()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }

    private function json($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function profile() { $this->index(); }
    public function experience() { $this->index(); }
    public function skills() { $this->index(); }
    public function credentials() { $this->index(); }
    public function sections() { $this->index(); }
    public function contact() { $this->index(); }
}


        return false;
    }
}
