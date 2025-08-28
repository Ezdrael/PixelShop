<?php
/**
 * Головний файл інсталятора CMS "PixelShop"
 * Вся логіка інсталяції, дизайн та функціонал знаходяться в одному файлі.
 */

// --- НАЛАШТУВАННЯ ТА КОНФІГУРАЦІЯ ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(600);

// --- КОНСТАНТИ ---
define('GITHUB_CMS_USER', 'Ezdrael');
define('GITHUB_CMS_REPO', 'PixelShop');
define('GITHUB_CMS_BRANCH', 'main');
define('REQUIRED_PHP_VERSION', '8.0.0');
define('SQL_FILE_PATH', 'database/create_tables.sql');
define('DEMO_SQL_FILE_PATH', 'database/fil_tables.sql');
define('CONFIG_FILE_NAME', 'config.php');

// --- ЗАПУСК СЕСІЇ ---
session_start([
    'use_only_cookies' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// --- ГОЛОВНИЙ КОНТРОЛЕР (РОУТЕР) ---

$step = $_SESSION['step'] ?? 1;

if (isset($_GET['ajax'])) {
    handle_ajax_request($_GET['ajax']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post_request($step);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


// --- ОСНОВНІ ФУНКЦІЇ-ОБРОБНИКИ ---

function handle_post_request($current_step) {
    if (!empty($_POST)) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'pass') === false) {
                $_SESSION['install_data'][$key] = $value;
            }
        }
    }

    switch ($current_step) {
        case 1:
            if (isset($_POST['agree_terms'])) $_SESSION['step'] = 2;
            break;
        case 2:
            clearstatcache(true);
            $permissions = check_permissions();
            $all_ok = !in_array(false, array_column($permissions, 'status'));
            if ($all_ok) $_SESSION['step'] = 3;
            break;
        case 3:
            if (isset($_SESSION['db_connected']) && $_SESSION['db_connected']) $_SESSION['step'] = 4;
            break;
        case 4:
            $_SESSION['step'] = 5;
            break;
        case 5: 
            if (isset($_POST['install_demo_data'])) {
                install_demo_data();
            }
            $_SESSION['step'] = 6;
            break;
        case 6:
            $_SESSION['step'] = 7;
            break;
        case 7:
            if (empty($_POST['admin_user']) || empty($_POST['admin_pass'])) {
                $_SESSION['form_error'] = "Логін та пароль не можуть бути порожніми.";
                break;
            }
            if ($_POST['admin_pass'] !== $_POST['admin_pass_confirm']) {
                $_SESSION['form_error'] = "Паролі не співпадають.";
            } else {
                $_SESSION['install_data']['admin_pass'] = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);
                $_SESSION['step'] = 8;
                unset($_SESSION['form_error']);
            }
            break;
        case 8:
            write_config_file();
            add_admin_user();
            file_put_contents('../install.lock', 'Installed on '. date('Y-m-d H:i:s'));
            $_SESSION['step'] = 9;
            break;
    }
}

function handle_ajax_request($action) {
    header('Content-Type: application/json');
    
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    try {
        $config = $_SESSION['install_data'] ?? [];

        switch ($action) {
            case 'reset_session':
                session_destroy();
                echo json_encode(['success' => true]);
                break;
            
            case 'recheck_permissions':
                clearstatcache(true);
                $permissions = check_permissions();
                ob_start();
                generate_permissions_html($permissions);
                $html = ob_get_clean();
                echo json_encode(['html' => $html]);
                break;

            case 'check_db':
                $db_host = $_POST['db_host'] ?? '';
                $db_name = $_POST['db_name'] ?? '';
                $db_user = $_POST['db_user'] ?? '';
                $db_pass = $_POST['db_pass'] ?? '';
                
                $_SESSION['install_data']['db_host'] = $db_host;
                $_SESSION['install_data']['db_name'] = $db_name;
                $_SESSION['install_data']['db_user'] = $db_user;
                $_SESSION['install_data']['db_pass'] = $db_pass;
                $_SESSION['install_data']['db_prefix'] = $_POST['db_prefix'] ?? 'cms_';

                $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
                new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                
                $_SESSION['db_connected'] = true;
                echo json_encode(['success' => true, 'message' => 'З\'єднання успішно встановлено!']);
                break;
            
            case 'install_schema':
                $file_path = SQL_FILE_PATH;
                $step = (int)($_POST['step'] ?? 0);
                
                $raw_url = sprintf('https://raw.githubusercontent.com/%s/%s/%s/%s', GITHUB_CMS_USER, GITHUB_CMS_REPO, GITHUB_CMS_BRANCH, $file_path);
                $sql_content = @file_get_contents($raw_url);

                if ($sql_content === false) {
                    throw new Exception("Не вдалося завантажити файл <a href='$raw_url' target='_blank'>$file_path</a> з GitHub.");
                }

                $statements = array_filter(array_map('trim', explode(";\n", $sql_content)));

                if ($step === 0) {
                    $_SESSION['sql_statements'] = $statements;
                    echo json_encode(['status' => 'ready', 'total' => count($statements)]);
                    return;
                }

                if (isset($_SESSION['sql_statements'][$step - 1])) {
                    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                    
                    $query = str_replace('%%PREFIX%%', $config['db_prefix'], $_SESSION['sql_statements'][$step - 1]);
                    if (!empty($query)) $pdo->exec($query);

                    echo json_encode(['status' => 'progress']);
                } else {
                    unset($_SESSION['sql_statements']);
                    echo json_encode(['status' => 'done']);
                }
                break;
            
            case 'download_files':
                $zip_file = download_from_github(GITHUB_CMS_USER, GITHUB_CMS_REPO, GITHUB_CMS_BRANCH);
                $extracted_path = extract_archive($zip_file);
                move_files_to_root($extracted_path);
                @unlink($zip_file);
                
                echo json_encode(['status' => 'done', 'message' => 'Файли CMS успішно встановлено!']);
                break;
                
            default:
                echo json_encode(['error' => 'Невідома дія.']);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Критична помилка: ' . $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]);
    } finally {
        restore_error_handler();
    }
}


// --- ФУНКЦІЇ-ПОМІЧНИКИ ---

function check_permissions() {
    $permissions = [];
    $php_ok = version_compare(PHP_VERSION, REQUIRED_PHP_VERSION, '>=');
    $permissions['php_version'] = [ 'title' => 'Версія PHP >= ' . REQUIRED_PHP_VERSION, 'status' => $php_ok, 'message' => $php_ok ? 'Встановлено: ' . PHP_VERSION : 'Ваша версія: ' . PHP_VERSION, 'fix' => 'Ваш сервер має застарілу версію PHP. <ol><li><strong>Для XAMPP/WAMP:</strong> Завантажте та встановіть останню версію XAMPP з офіційного сайту.</li><li><strong>Для веб-хостингу:</strong> Зайдіть у вашу панель керування (cPanel, Plesk тощо), знайдіть розділ "Вибір версії PHP" ("Select PHP Version") та оберіть ' . REQUIRED_PHP_VERSION . ' або новішу.</li></ol>'];
    $curl_ok = extension_loaded('curl');
    $permissions['curl'] = [ 'title' => 'PHP розширення: cURL', 'status' => $curl_ok, 'message' => $curl_ok ? 'Активовано' : 'Не знайдено', 'fix' => 'Це розширення необхідне для завантаження файлів. <ol><li>Знайдіть на вашому сервері файл конфігурації <code>php.ini</code>.</li><li>Знайдіть рядок <code>;extension=curl</code>.</li><li>Приберіть крапку з комою: <code>extension=curl</code>.</li><li>Збережіть файл та <strong>обов\'язково перезапустіть веб-сервер Apache</strong>.</li></ol>'];
    $fopen_ok = (bool)ini_get('allow_url_fopen');
    $permissions['allow_url_fopen'] = [ 'title' => 'PHP директива: allow_url_fopen', 'status' => $fopen_ok, 'message' => $fopen_ok ? 'Увімкнено' : 'Вимкнено', 'fix' => 'Ця опція дозволяє PHP працювати з файлами за URL-адресами, що необхідно для завантаження SQL-файлів з GitHub. <ol><li>У файлі <code>php.ini</code> знайдіть рядок <code>allow_url_fopen = Off</code>.</li><li>Змініть його на <code>allow_url_fopen = On</code>.</li><li>Збережіть файл та перезапустіть веб-сервер Apache.</li></ol>'];
    $pdo_ok = extension_loaded('pdo_mysql');
    $permissions['pdo_mysql'] = [ 'title' => 'PHP розширення: PDO MySQL', 'status' => $pdo_ok, 'message' => $pdo_ok ? 'Активовано' : 'Не знайдено', 'fix' => 'Це розширення необхідне для роботи з базою даних. <ol><li>У файлі <code>php.ini</code> знайдіть рядок <code>;extension=pdo_mysql</code>.</li><li>Приберіть крапку з комою: <code>extension=pdo_mysql</code>.</li><li>Збережіть файл та перезапустіть веб-сервер Apache.</li></ol>'];
    $zip_ok = class_exists('ZipArchive');
    $permissions['zip'] = [ 'title' => 'PHP розширення: Zip', 'status' => $zip_ok, 'message' => $zip_ok ? 'Активовано' : 'Не знайдено', 'fix' => 'Це розширення необхідне для розпакування архівів. <ol><li>У файлі <code>php.ini</code> знайдіть рядок <code>;extension=zip</code>.</li><li>Приберіть крапку з комою: <code>extension=zip</code>.</li><li>Збережіть файл та перезапустіть веб-сервер Apache.</li></ol>'];
    $target_dir = __DIR__;
    $test_file = $target_dir . '/temp_writable_test_' . uniqid() . '.tmp';
    $writable_ok = false;
    if (@file_put_contents($test_file, 'test')) {
        if (file_exists($test_file)) {
            $writable_ok = true;
            @unlink($test_file);
        }
    }
    $permissions['writable'] = [ 'title' => 'Права на запис у поточний каталог', 'status' => $writable_ok, 'message' => $writable_ok ? 'Дозволено' : 'Відмовлено у доступі', 'fix' => 'Інсталятор повинен мати можливість створювати файли та папки. <ol><li><strong>Найпростіше рішення:</strong> Перезапустіть XAMPP Control Panel **від імені адміністратора**.</li><li><strong>Альтернатива для Windows:</strong> Натисніть правою кнопкою на папку проекту (`install`), оберіть "Властивості" -> "Безпека", та надайте групі "Users" (Користувачі) "Повний доступ" ("Full control").</li></ol>'];
    return $permissions;
}

function write_config_file() {
    $config_data = [
        'database' => [ 'host' => $_SESSION['install_data']['db_host'], 'name' => $_SESSION['install_data']['db_name'], 'user' => $_SESSION['install_data']['db_user'], 'password' => $_SESSION['install_data']['db_pass'], 'prefix' => $_SESSION['install_data']['db_prefix'], ],
        'site' => [ 'title' => $_SESSION['install_data']['site_title'], ]
    ];
    $config_content = "<?php\n\nreturn " . var_export($config_data, true) . ";\n";
    file_put_contents(CONFIG_FILE_NAME, $config_content);
}

function add_admin_user() {
    $config = $_SESSION['install_data'];
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $role_table = $config['db_prefix'] . 'roles';
    $stmt = $pdo->prepare("INSERT INTO {$role_table} (id, role_name, perm_chat, perm_roles, perm_users, perm_categories, perm_goods, perm_warehouses, perm_arrivals, perm_transfers, perm_albums) VALUES (1, 'Адміністратор', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed') ON DUPLICATE KEY UPDATE role_name = 'Адміністратор'");
    $stmt->execute();
    
    $user_table = $config['db_prefix'] . 'users';
    $stmt = $pdo->prepare("INSERT INTO {$user_table} (id, name, email, password, role_id) VALUES (1, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), password=VALUES(password), email=VALUES(email)");
    $stmt->execute([$config['admin_user'], $config['admin_pass'], $config['admin_email']]);
}

function install_demo_data() {
    try {
        $config = $_SESSION['install_data'];
        $file_path = DEMO_SQL_FILE_PATH;
        $raw_url = sprintf('https://raw.githubusercontent.com/%s/%s/%s/%s', GITHUB_CMS_USER, GITHUB_CMS_REPO, GITHUB_CMS_BRANCH, $file_path);
        $sql_content = @file_get_contents($raw_url);

        if ($sql_content) {
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
            $statements = array_filter(array_map('trim', explode(";\n", $sql_content)));
            foreach ($statements as $query) {
                $query = str_replace('%%PREFIX%%', $config['db_prefix'], $query);
                if (!empty($query)) $pdo->exec($query);
            }
        }
    } catch (Exception $e) {
        // Ігноруємо помилки
    }
}

function generate_permissions_html($permissions) {
    $all_ok = !in_array(false, array_column($permissions, 'status'));
    echo '<ul class="permissions-list">';
    foreach ($permissions as $key => $p) {
        $status_class = $p['status'] ? 'success' : 'danger';
        $icon = $p['status'] ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
        echo "<li class='{$status_class}'><div>{$icon} <strong>{$p['title']}</strong></div><div class='message'>{$p['message']}</div>";
        if (!$p['status']) {
            echo '<div class="help-trigger" data-modal-id="modal-'.$key.'"><i class="fas fa-question-circle"></i></div>';
        }
        echo '</li>';
    }
    echo '</ul>';
    echo '<div class="action-footer">';
    if ($all_ok) {
        echo '<form method="POST"><button type="submit" class="button">Далі <i class="fas fa-arrow-right"></i></button></form>';
    } else {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Будь ласка, виправте проблеми та натисніть "Повторити перевірку".</div>';
        echo '<div class="button-group"><button type="button" id="recheck-btn" class="button secondary"><i class="fas fa-sync-alt"></i> Повторити перевірку</button></div>';
    }
    echo '</div>';
}

function download_from_github($user, $repo, $branch) {
    $zip_url = "https://api.github.com/repos/$user/$repo/zipball/$branch";
    $zip_file = __DIR__ . '/cms_latest.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }

    $fp = fopen($zip_file, 'w+');
    if ($fp === false) {
        throw new Exception("Не вдалося створити тимчасовий файл для архіву: " . $zip_file);
    }

    $ch = curl_init($zip_url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CMS Installer');
    curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("Помилка cURL при завантаженні архіву: " . curl_error($ch));
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code >= 400) {
        rewind($fp);
        $error_response = stream_get_contents($fp);
        fclose($fp);
        @unlink($zip_file);
        throw new Exception("Помилка від GitHub API ($http_code). Перевірте правильність GITHUB_CMS_USER/REPO/BRANCH. Відповідь: " . htmlspecialchars($error_response));
    }

    curl_close($ch);
    fclose($fp);
    
    return $zip_file;
}

function extract_archive($zip_file) {
    $zip = new ZipArchive;
    if ($zip->open($zip_file) !== TRUE) {
        throw new Exception("Не вдалося відкрити zip-архів '$zip_file'.");
    }
    $extract_path = __DIR__ . '/cms_temp_extract';
    if (is_dir($extract_path)) { delete_recursive($extract_path); }
    
    mkdir($extract_path, 0755, true);
    $zip->extractTo($extract_path);
    $zip->close();
    return $extract_path;
}

function move_files_to_root($source_dir) {
    $files = scandir($source_dir);
    if (count($files) < 3) {
        throw new Exception("Не вдалося знайти папку з файлами CMS всередині розпакованого архіву.");
    }
    $inner_dir_name = $files[2]; 
    $inner_dir = $source_dir . '/' . $inner_dir_name;

    if (!is_dir($inner_dir)) {
        throw new Exception("Внутрішній каталог '$inner_dir' не знайдено.");
    }

    $iterator = new DirectoryIterator($inner_dir);
    foreach ($iterator as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $source_path = $fileinfo->getPathname();
            $dest_path = __DIR__ . '/' . $fileinfo->getFilename();
            copy_recursive($source_path, $dest_path);
        }
    }
    
    delete_recursive($source_dir);
}

function copy_recursive($source, $dest) {
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $iterator = new DirectoryIterator($source);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDot()) {
                copy_recursive($fileinfo->getPathname(), $dest . '/' . $fileinfo->getFilename());
            }
        }
    } else {
        copy($source, $dest);
    }
}

function delete_recursive($dir) {
    if (!file_exists($dir)) return;
    $iterator = new DirectoryIterator($dir);
    foreach ($iterator as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $path = $fileinfo->getPathname();
            if ($fileinfo->isDir()) {
                delete_recursive($path);
            } else {
                unlink($path);
            }
        }
    }
    rmdir($dir);
}

function is_database_empty() {
    try {
        if (!isset($_SESSION['install_data']['db_host'])) return true;
        $config = $_SESSION['install_data'];
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?");
        $stmt->execute([$config['db_name']]);
        return $stmt->fetchColumn() == 0;
    } catch (PDOException $e) {
        return false;
    }
}


// --- ФУНКЦІЇ ВІДОБРАЖЕННЯ (HTML/CSS/JS) ---

function render_header($title, $current_step) {
    $steps = [ 1 => ['icon' => 'fas fa-file-contract', 'label' => 'Угоди'], 2 => ['icon' => 'fas fa-clipboard-check', 'label' => 'Перевірка'], 3 => ['icon' => 'fas fa-database', 'label' => 'База даних'], 4 => ['icon' => 'fas fa-table', 'label' => 'Таблиці'], 5 => ['icon' => 'fas fa-seedling', 'label' => 'Демо-дані'], 6 => ['icon' => 'fas fa-download', 'label' => 'Файли'], 7 => ['icon' => 'fas fa-user-shield', 'label' => 'Адміністратор'], 8 => ['icon' => 'fas fa-store', 'label' => 'Магазин'], 9 => ['icon' => 'fas fa-check-circle', 'label' => 'Готово'], ];
    $installer_version = filemtime(__FILE__);
    echo <<<HTML
<!DOCTYPE html><html lang="uk"><head><meta charset="UTF-8"><title>Встановлення CMS - $title</title><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Cuprum:wght@700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #3b82f6; --primary-color-hover: #2563eb; --success-color: #22c55e; --danger-color: #ef4444; --warning-color: #f59e0b; --bg-color: #f1f5f9; --card-bg-color: #ffffff; --text-color: #334155; --text-light-color: #64748b; --border-color: #e2e8f0; --border-radius: 0.75rem; --font-family-main: 'Montserrat', sans-serif; --font-family-steps: 'Cuprum', sans-serif; --font-family-code: 'Roboto Mono', monospace; --card-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.07), 0 4px 6px -4px rgb(0 0 0 / 0.1); }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: var(--font-family-main); background-color: var(--bg-color); color: var(--text-color); margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { width: 100%; max-width: 800px; background-color: var(--card-bg-color); border-radius: var(--border-radius); box-shadow: var(--card-shadow); padding: 2.5rem; border: 1px solid var(--border-color); }
        .progress-steps { display: flex; list-style: none; padding: 0; margin: 0 0 2.5rem 0; position: relative; justify-content: space-between; }
        .progress-steps::before { content: ''; position: absolute; top: 1.125rem; left: 5%; right: 5%; height: 4px; background-color: var(--border-color); z-index: 0; }
        .step { text-align: center; position: relative; z-index: 1; width: 11%; }
        .step-icon { width: 2.5rem; height: 2.5rem; border-radius: 50%; background-color: var(--card-bg-color); color: var(--text-light-color); border: 3px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 1.125rem; margin: 0 auto 0.5rem; transition: all 0.4s ease; }
        .step-label { font-family: var(--font-family-steps); font-size: 0.9rem; font-weight: 700; color: var(--text-light-color); text-transform: uppercase; }
        .step.active .step-icon { border-color: var(--primary-color); color: var(--primary-color); }
        .step.active .step-label { color: var(--primary-color); }
        .step.completed .step-icon { border-color: var(--success-color); background-color: var(--success-color); color: #fff; }
        h2 { text-align: center; font-size: 1.75rem; margin: 0 0 1rem 0; color: var(--text-color); }
        p { text-align: center; color: var(--text-light-color); margin: 0 0 2rem 0; line-height: 1.6; max-width: 60ch; margin-left: auto; margin-right: auto; }
        .content { border-top: 1px solid var(--border-color); padding-top: 2rem; }
        .step-image { display: block; max-height: 300px; margin: 0 auto 1.5rem auto; }
        code, .modal-body code { font-family: var(--font-family-code); background-color: #e2e8f0; padding: 2px 6px; border-radius: 4px; color: #334155; }
        .legal-links { display: flex; justify-content: center; gap: 2rem; margin-bottom: 1rem; } .legal-links a { color: var(--primary-color); text-decoration: none; font-weight: 500; } .legal-links a:hover { text-decoration: underline; } .input-group { position: relative; margin-bottom: 1.25rem; } .input-group .icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light-color); pointer-events: none; } .input-field { width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-size: 1rem; transition: border-color 0.2s, box-shadow 0.2s; } .input-field:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); } .button { display: block; width: 100%; padding: 0.875rem 1.5rem; border: none; border-radius: 0.5rem; background-color: var(--primary-color); color: #fff; font-size: 1rem; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: background-color 0.2s ease; } .button:hover { background-color: var(--primary-color-hover); } .button:disabled { background-color: #9ca3af; cursor: not-allowed; } .button i { margin-left: 0.5rem; } .button.secondary { background-color: var(--card-bg-color); color: var(--primary-color); border: 1px solid var(--primary-color); } .button.secondary:hover { background-color: #eff6ff; } .button-group { display: flex; gap: 1rem; } .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid transparent; text-align: center; } .alert-success { background-color: #ecfdf5; color: #059669; border-color: #a7f3d0; } .alert-danger { background-color: #fff1f2; color: #e11d48; border-color: #fecdd3; } .alert-warning { background-color: #fffbeb; color: #b45309; border-color: #fde68a; } .permissions-list { list-style: none; padding: 0; margin: 0; border: 1px solid var(--border-color); border-radius: var(--border-radius); overflow: hidden; } .permissions-list li { display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-bottom: 1px solid var(--border-color); } .permissions-list li:last-child { border-bottom: none; } .permissions-list li.success { color: #059669; } .permissions-list li.danger { color: #be123c; } .permissions-list li .fas { font-size: 1.25rem; margin-right: 1rem; } .permissions-list .message { margin-left: auto; color: var(--text-light-color); font-size: 0.9rem; } .permissions-list .help-trigger { margin-left: 1rem; color: var(--primary-color); cursor: pointer; font-size: 1.25rem; } .modal-overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; } .modal-content { background-color: #fff; width: 90%; max-width: 600px; border-radius: var(--border-radius); box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative; animation: fadeIn 0.3s ease-out; overflow: hidden; } .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; background-color: var(--light-gray-color); border-bottom: 1px solid var(--border-color); } .modal-header h3 { margin: 0; font-size: 1.25rem; color: var(--text-color); } .modal-header h3 .fas { margin-right: 0.75rem; color: var(--primary-color); } .close-button { color: var(--text-light-color); font-size: 1.5rem; font-weight: bold; border: none; background: none; cursor: pointer; padding: 0.5rem; line-height: 1; } .modal-body { padding: 1.5rem; line-height: 1.7; max-height: 60vh; overflow-y: auto; } .modal-body h4 { margin-top: 0; margin-bottom: 0.5rem; color: var(--text-color); } .modal-body ol { padding-left: 20px; } .modal-body li { margin-bottom: 0.75rem; } @keyframes fadeIn { from {opacity: 0; transform: translateY(-30px) scale(0.98);} to {opacity: 1; transform: translateY(0) scale(1);} } .progress-bar { width: 100%; height: 1.5rem; background-color: var(--border-color); border-radius: 0.5rem; overflow: hidden; margin-top: 0.5rem; } .progress-bar-inner { width: 0%; height: 100%; background-color: var(--primary-color); transition: width 0.4s ease; text-align: center; color: white; font-weight: 500; line-height: 1.5rem; font-size: 0.8rem; } .progress-label { text-align: center; margin-top: 0.5rem; color: var(--text-light-color); font-style: italic; min-height: 1.2em; } .action-footer { margin-top: 2rem; } .checkbox-group { display: flex; align-items: center; background-color: #f8f9fa; padding: 1rem; border-radius: 0.5rem; }
        .animation-container { display: flex; justify-content: space-between; align-items: center; padding: 2rem 0; }
        .animation-icon { font-size: 4rem; color: var(--text-light-color); }
        .animation-path { flex-grow: 1; height: 5px; background: linear-gradient(90deg, var(--primary-color) 50%, var(--border-color) 50%); background-size: 200% 100%; animation: path-load 2s linear infinite; }
        .animation-files { position: relative; width: 100%; height: 100%; }
        .file-icon { position: absolute; font-size: 1.5rem; color: var(--primary-color); animation: file-move 2s ease-in-out infinite; opacity: 0; }
        .file-icon:nth-child(2) { animation-delay: 0.5s; }
        .file-icon:nth-child(3) { animation-delay: 1s; }
        @keyframes path-load { from { background-position: 100% 0; } to { background-position: -100% 0; } }
        @keyframes file-move { 0% { left: 0; opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { left: calc(100% - 2rem); opacity: 0; } }
    </style>
</head><body><script>
        (function() {
            const currentVersion = '<?= $installer_version ?>';
            const storedVersion = localStorage.getItem('installerVersion');
            if (storedVersion !== currentVersion) {
                fetch('?ajax=reset_session').then(() => {
                    localStorage.setItem('installerVersion', currentVersion);
                    if (storedVersion) {
                         window.location.href = 'index.php';
                    }
                });
            }
        })();
    </script><div class="card">
HTML;
    echo '<ul class="progress-steps">';
    foreach ($steps as $num => $step_data) {
        $class = '';
        if ($num < $current_step) $class = 'completed';
        if ($num == $current_step) $class = 'active';
        echo "<li class='step $class'><div class='step-icon'><i class='{$step_data['icon']}'></i></div><div class='step-label'>{$step_data['label']}</div></li>";
    }
    echo '</ul>';
    echo "<h2>$title</h2>";
}

function render_footer() {
    echo '</div><script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector("body").addEventListener("click", async function(event) {
                if (event.target.closest(".modal-trigger")) {
                    event.preventDefault();
                    document.getElementById(event.target.closest(".modal-trigger").dataset.modalId).style.display = "flex";
                }
                if (event.target.closest(".close-button")) {
                    event.target.closest(".modal-overlay").style.display = "none";
                }
                if (event.target.classList.contains("modal-overlay")) {
                    event.target.style.display = "none";
                }
                const recheckBtn = event.target.closest("#recheck-btn");
                if (recheckBtn) {
                    recheckBtn.innerHTML = \'<i class="fas fa-spinner fa-spin"></i> Перевірка...\';
                    recheckBtn.disabled = true;
                    try {
                        const response = await fetch("?ajax=recheck_permissions");
                        const data = await response.json();
                        document.getElementById("permissions-container").innerHTML = data.html;
                    } catch (error) {
                        alert("Помилка оновлення. Спробуйте оновити сторінку.");
                    }
                }
            });
            document.addEventListener("keydown", function(event) {
                if (event.key === "Escape") {
                    document.querySelectorAll(".modal-overlay").forEach(overlay => {
                        overlay.style.display = "none";
                    });
                }
            });
        });
    </script></body></html>';
}

function render_step_1() {
    ?>
    <img src="https://i.ibb.co/xqY1p410/1.jpg" alt="Угоди" class="step-image">
    <p>Перед початком встановлення, будь ласка, ознайомтеся з нашими основними документами та підтвердіть свою згоду.</p>
    <div class="legal-links">
        <a href="#" class="modal-trigger" data-modal-id="modal-license">Ліцензійна угода</a>
        <a href="#" class="modal-trigger" data-modal-id="modal-terms">Умови використання</a>
        <a href="#" class="modal-trigger" data-modal-id="modal-privacy">Політика конфіденційності</a>
    </div>

    <form method="POST">
        <div class="checkbox-group" style="margin-top: 2rem;">
            <input type="checkbox" id="agree_terms" name="agree_terms" value="1" style="width: 1.25rem; height: 1.25rem; margin-right: 0.75rem;">
            <label for="agree_terms" style="font-weight: 500; user-select: none; cursor: pointer; margin: 0;">Я прочитав(ла) та погоджуюсь з усіма умовами</label>
        </div>
        <button type="submit" id="continue-btn" class="button" style="margin-top: 1rem;" disabled>Продовжити <i class="fas fa-arrow-right"></i></button>
    </form>

    <div id="modal-license" class="modal-overlay"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-file-contract"></i> Ліцензійна угода</h3><button class="close-button">&times;</button></div><div class="modal-body"><h4>Ліцензійна угода на використання Системи управління контентом "PixelShop"</h4><p>Ця Угода укладається між Власником прав та будь-якою особою ("Користувач"), яка встановлює Продукт. Встановлення означає повну згоду з умовами.</p><ol><li><b>Предмет Угоди:</b> Власник надає Користувачеві невиключну, безоплатну ліцензію на використання Продукту.</li><li><b>Права та Заборони:</b> Користувач може встановлювати, використовувати та розповсюджувати Продукт безоплатно. Забороняється продавати Продукт, вносити зміни у вихідний код без письмової згоди Власника та видаляти повідомлення про авторські права.</li><li><b>Відмова від гарантій:</b> Продукт надається "ЯК Є". Власник не надає жодних гарантій щодо його працездатності.</li><li><b>Обмеження відповідальності:</b> Власник не несе відповідальності за будь-які збитки, завдані внаслідок використання Продукту.</li></ol><p>Ця Угода регулюється законодавством України.</p></div></div></div>
    <div id="modal-terms" class="modal-overlay"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-gavel"></i> Умови використання</h3><button class="close-button">&times;</button></div><div class="modal-body"><h4>Умови використання Системи управління контентом "PixelShop"</h4><ol><li><b>Прийняття умов:</b> Використовуючи Продукт, ви погоджуєтесь з цими Умовами.</li><li><b>Відповідальність Користувача:</b> Ви несете повну відповідальність за весь контент на вашому сайті та за будь-яку діяльність, що здійснюється за допомогою Продукту. Заборонено використовувати Продукт для незаконної діяльності.</li><li><b>Інтелектуальна власність:</b> Усі права на Продукт належать Власнику.</li></ol></div></div></div>
    <div id="modal-privacy" class="modal-overlay"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-user-secret"></i> Політика конфіденційності</h3><button class="close-button">&times;</button></div><div class="modal-body"><h4>Політика конфіденційності при використанні CMS "PixelShop"</h4><p>Ця Політика пояснює, як Продукт обробляє дані відповідно до Закону України «Про захист персональних даних».</p><ol><li><b>Збір та Використання даних:</b> Встановлений на вашому сервері Продукт може збирати персональні дані користувачів та клієнтів (імена, email, адреси). Ви, як власник сайту, є контролером цих даних. Власник Продукту не має доступу до цих даних.</li><li><b>Передача даних третім особам:</b> Продукт може інтегруватися зі сторонніми сервісами (платіжні системи, служби доставки), яким можуть передаватися дані для виконання замовлень.</li><li><b>Права суб'єктів даних:</b> Ви, як власник сайту, зобов'язані забезпечити права ваших користувачів на доступ, виправлення та видалення їхніх персональних даних.</li></ol></div></div></div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const agreeCheckbox = document.getElementById('agree_terms');
        const continueBtn = document.getElementById('continue-btn');
        agreeCheckbox.addEventListener('change', function() {
            continueBtn.disabled = !this.checked;
        });
    });
    </script>
    <?php
}

function render_step_2() {
    $permissions = check_permissions();
    ?>
    <img src="https://i.ibb.co/Ldwy1Z7b/2.jpg" alt="Перевірка" class="step-image">
    <div id="permissions-container">
        <?php generate_permissions_html($permissions); ?>
    </div>
    <?php
    foreach ($permissions as $key => $p) {
        if (!$p['status']) {
            echo '<div id="modal-'.$key.'" class="modal-overlay"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-wrench"></i> Як виправити</h3><button class="close-button">&times;</button></div><div class="modal-body"><h4>'.$p['title'].'</h4><div>'.$p['fix'].'</div></div></div></div>';
        }
    }
}

function render_step_3() {
    $data = $_SESSION['install_data'] ?? [];
    ?>
    <img src="https://i.ibb.co/6cj2tF1B/3.jpg" alt="База даних" class="step-image">
    <p>Введіть дані для підключення до вашої бази даних MySQL.</p>
    <form method="post" id="db-form">
        <div class="input-group"><i class="icon fas fa-server"></i><input class="input-field" type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($data['db_host'] ?? 'localhost') ?>" placeholder="Хост бази даних" required></div>
        <div class="input-group"><i class="icon fas fa-database"></i><input class="input-field" type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($data['db_name'] ?? '') ?>" placeholder="Ім'я бази даних" required></div>
        <div class="input-group"><i class="icon fas fa-user"></i><input class="input-field" type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($data['db_user'] ?? '') ?>" placeholder="Ім'я користувача" required></div>
        <div class="input-group"><i class="icon fas fa-lock"></i><input class="input-field" type="password" id="db_pass" name="db_pass" value="" placeholder="Пароль"></div>
        <div class="input-group"><i class="icon fas fa-table"></i><input class="input-field" type="text" id="db_prefix" name="db_prefix" value="<?= htmlspecialchars($data['db_prefix'] ?? 'cms_') ?>" placeholder="Префікс таблиць" required></div>
        <div id="connection-status" style="margin-bottom: 1rem;"></div>
        <div class="button-group">
            <button type="button" id="check-db-btn" class="button secondary"><i class="fas fa-plug"></i> Перевірити з'єднання</button>
            <button type="submit" id="continue-btn" class="button" disabled>Далі <i class="fas fa-arrow-right"></i></button>
        </div>
    </form>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkBtn = document.getElementById('check-db-btn');
        const continueBtn = document.getElementById('continue-btn');
        const form = document.getElementById('db-form');
        const statusBox = document.getElementById('connection-status');
        const inputs = form.querySelectorAll('input');
        async function checkConnection() {
            checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Перевірка...';
            checkBtn.disabled = true;
            continueBtn.disabled = true;
            statusBox.innerHTML = '';
            const formData = new FormData(form);
            try {
                const response = await fetch('?ajax=check_db', { method: 'POST', body: formData });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();
                let alertClass = result.success ? 'alert-success' : 'alert-danger';
                statusBox.innerHTML = `<div class="alert ${alertClass}">${result.message}</div>`;
                if (result.success) continueBtn.disabled = false;
            } catch (error) {
                statusBox.innerHTML = '<div class="alert alert-danger">Сталася критична помилка. Перевірте консоль.</div>';
                console.error('Fetch error:', error);
            } finally {
                checkBtn.innerHTML = '<i class="fas fa-plug"></i> Перевірити з\'єднання';
                checkBtn.disabled = false;
            }
        }
        checkBtn.addEventListener('click', checkConnection);
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                continueBtn.disabled = true;
                statusBox.innerHTML = '';
            });
        });
    });
    </script>
    <?php
}

function render_step_4() {
    $is_db_empty = is_database_empty();
    ?>
    <img src="https://i.ibb.co/DPzg7Zhq/4.jpg" alt="Таблиці" class="step-image">
    <?php
    if ($is_db_empty) {
        ?>
        <p>Ваша база даних порожня. Зараз буде створено необхідну структуру таблиць. Процес почнеться автоматично.</p>
        <div id="install-progress" style="margin-top: 2rem;">
            <div style="margin-bottom: 1.5rem;">
                <strong><i class="fas fa-layer-group"></i> Створення структури бази даних:</strong>
                <div class="progress-bar"><div id="progress-schema" class="progress-bar-inner">0%</div></div>
            </div>
        </div>
        <div id="install-log" style="margin-top: 1.5rem;"></div>
        <div id="continue-section" style="display: none; text-align: center; margin-top: 2rem;">
            <form method="POST">
                <button type="submit" class="button">Продовжити <i class="fas fa-arrow-right"></i></button>
            </form>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const schemaBar = document.getElementById('progress-schema');
            const log = document.getElementById('install-log');
            const continueSection = document.getElementById('continue-section');
            const progressContainer = document.getElementById('install-progress');
            async function runInstallStep(step) {
                const formData = new FormData();
                formData.append('step', step);
                const response = await fetch('?ajax=install_schema', { method: 'POST', body: formData });
                if (!response.ok) throw new Error(`Помилка мережі: ${response.statusText}`);
                return await response.json();
            }
            async function processQueue() {
                let response = await runInstallStep(0);
                if (response.error) { log.innerHTML = `<div class="alert alert-danger">${response.error}</div>`; return; }
                const total = response.total;
                if (total === 0) {
                    schemaBar.style.width = '100%'; schemaBar.textContent = '100%';
                    log.innerHTML = `<div class="alert alert-success">Структуру бази даних створено!</div>`;
                    progressContainer.style.display = 'none';
                    continueSection.style.display = 'block';
                    return;
                }
                for (let i = 1; i <= total; i++) {
                    response = await runInstallStep(i);
                    if (response.error) { log.innerHTML = `<div class="alert alert-danger">${response.error}</div>`; return; }
                    const percent = Math.round((i / total) * 100);
                    schemaBar.style.width = percent + '%';
                    schemaBar.textContent = percent + '%';
                }
                log.innerHTML = `<div class="alert alert-success">Структуру бази даних успішно створено!</div>`;
                progressContainer.style.display = 'none';
                continueSection.style.display = 'block';
            }
            processQueue();
        });
        </script>
        <?php
    } else {
        ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Увага!</strong> Ваша база даних "<?= htmlspecialchars($_SESSION['install_data']['db_name']) ?>" вже містить таблиці.
        </div>
        <p>Створення нових таблиць буде пропущено. Ви можете безпечно продовжити встановлення, щоб завантажити файли та налаштувати конфігурацію.</p>
        <form method="POST" style="margin-top: 2rem;">
            <button type="submit" class="button">Продовжити з існуючими таблицями <i class="fas fa-arrow-right"></i></button>
        </form>
        <?php
    }
}

function render_step_5() {
    ?>
    <img src="https://i.ibb.co/vvVpfJn4/5.jpg" alt="Демо-дані" class="step-image">
    <p>Тепер ви можете заповнити ваш сайт <strong>демонстраційними даними</strong>. Це дуже корисно для першого знайомства з системою. Ви зможете видалити ці дані пізніше.</p>
    <form method="POST">
        <div class="button-group">
            <button type="submit" name="skip_demo_data" class="button secondary"><i class="fas fa-forward"></i> Пропустити</button>
            <button type="submit" name="install_demo_data" class="button"><i class="fas fa-cubes"></i> Встановити демо-дані</button>
        </div>
    </form>
    <?php
}

function render_step_6() {
    ?>
    <img src="https://i.ibb.co/0x3K3MD/6.jpg" alt="Файли" class="step-image">
    <p>Ще трохи і ви станете власником чудового інтернет-магазину.</p>
    <div id="install-progress">
        <div class="animation-container">
            <div class="animation-icon"><i class="fas fa-server"></i></div>
            <div class="animation-path">
                <div class="animation-files">
                    <i class="fas fa-file-alt file-icon"></i>
                    <i class="fas fa-file-archive file-icon"></i>
                    <i class="fas fa-file-code file-icon"></i>
                </div>
            </div>
            <div class="animation-icon"><i class="fas fa-desktop"></i></div>
        </div>
    </div>
    <div id="download-log" style="margin-top: 1rem;"></div>
    <div id="continue-section" style="display: none; text-align: center; margin-top: 2rem;">
        <form method="POST">
            <button type="submit" class="button">Продовжити <i class="fas fa-arrow-right"></i></button>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const log = document.getElementById('download-log');
        const progressContainer = document.getElementById('install-progress');
        const continueSection = document.getElementById('continue-section');
        
        async function startDownload() {
            try {
                const response = await fetch('?ajax=download_files');
                const result = await response.json();

                if (result.error) {
                    throw new Error(result.error);
                }
                
                log.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                progressContainer.style.display = 'none';
                continueSection.style.display = 'block';

            } catch(err) {
                 log.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
                 console.error('Download Error:', err);
            }
        }
        startDownload();
    });
    </script>
    <?php
}

function render_step_7() {
    $error = $_SESSION['form_error'] ?? null;
    $data = $_SESSION['install_data'] ?? [];
    unset($_SESSION['form_error']);
    ?>
    <img src="https://i.ibb.co/XfpvJ0jb/7.jpg" alt="Адміністратор" class="step-image">
    <p>Створіть обліковий запис головного адміністратора сайту.</p>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="input-group"><i class="icon fas fa-user-shield"></i><input class="input-field" type="text" name="admin_user" placeholder="Логін адміністратора" value="<?= htmlspecialchars($data['admin_user'] ?? '') ?>" required></div>
        <div class="input-group"><i class="icon fas fa-key"></i><input class="input-field" type="password" name="admin_pass" placeholder="Пароль" required></div>
        <div class="input-group"><i class="icon fas fa-key"></i><input class="input-field" type="password" name="admin_pass_confirm" placeholder="Підтвердіть пароль" required></div>
        <div class="input-group"><i class="icon fas fa-at"></i><input class="input-field" type="email" name="admin_email" placeholder="Email адміністратора" value="<?= htmlspecialchars($data['admin_email'] ?? '') ?>" required></div>
        <button type="submit" class="button">Створити та продовжити <i class="fas fa-arrow-right"></i></button>
    </form>
    <?php
}

function render_step_8() {
    $data = $_SESSION['install_data'] ?? [];
    ?>
    <img src="https://i.ibb.co/JRcm53Ym/8.jpg" alt="Магазин" class="step-image">
    <p>Введіть основну інформацію про ваш новий інтернет-магазин.</p>
    <form method="post">
        <div class="input-group">
            <i class="icon fas fa-store"></i>
            <input class="input-field" type="text" name="site_title" placeholder="Назва магазину" value="<?= htmlspecialchars($data['site_title'] ?? '') ?>" required>
        </div>
        <button type="submit" class="button">Завершити встановлення <i class="fas fa-check-circle"></i></button>
    </form>
    <?php
}

function render_step_9() {
    ?>
    <img src="https://i.ibb.co/jPJrFBcf/9.jpg" alt="Готово" class="step-image">
    <div style="text-align:center;">
        <h2 style="border:none; font-size: 2rem;"><i class="fas fa-check-circle" style="color: var(--success-color);"></i> Встановлення завершено!</h2>
        <p>Ваш інтернет-магазин готовий до роботи. З міркувань безпеки, файл інсталятора було видалено.</p>
        <div class="button-group" style="margin-top: 2rem;">
            <a href="./" class="button">Перейти на сайт <i class="fas fa-arrow-right"></i></a>
            <a href="./admin" class="button secondary">Перейти в адмін-панель <i class="fas fa-user-shield"></i></a>
        </div>
    </div>
    <?php
    @session_destroy();
    @unlink(__FILE__);
}


// --- ФІНАЛЬНЕ ВІДОБРАЖЕННЯ СТОРІНКИ ---
render_header("Крок {$step}", $step);
echo '<div class="content">';
switch ($step) {
    case 1: render_step_1(); break;
    case 2: render_step_2(); break;
    case 3: render_step_3(); break;
    case 4: render_step_4(); break;
    case 5: render_step_5(); break;
    case 6: render_step_6(); break;
    case 7: render_step_7(); break;
    case 8: render_step_8(); break;
    case 9: render_step_9(); break;
    default: render_step_1();
}
echo '</div>';
render_footer();

?>