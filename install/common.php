<?php
/**
 * common.php - Спільні функції та налаштування для інсталятора.
 */

// --- Налаштування середовища ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 хвилин на виконання

session_start(['use_only_cookies' => true]);

// --- Визначення констант та конфігурації ---
define('GITHUB_CMS_USER', 'Ezdrael');       // Замініть на ваш логін GitHub
define('GITHUB_CMS_REPO', 'PixelShop');       // Замініть на назву репозиторію CMS
define('GITHUB_CMS_BRANCH', 'main');          // Гілка для завантаження CMS
define('REQUIRED_PHP_VERSION', '8.0.0');      // Мінімальна версія PHP
define('SQL_FILE_PATH', 'database/schema.sql'); // Шлях до SQL файлу всередині репозиторію CMS
define('CONFIG_FILE_NAME', 'config.php');     // Назва конфігураційного файлу

/**
 * Рендерить HTML-шапку сторінки з новим дизайном.
 * @param string $title Заголовок сторінки.
 * @param int $current_step Поточний крок (1-4).
 */
function render_header($title, $current_step = 1) {
    $steps = [
        1 => ['icon' => 'fas fa-clipboard-check', 'label' => 'Перевірка'],
        2 => ['icon' => 'fas fa-database', 'label' => 'База даних'],
        3 => ['icon' => 'fas fa-user-cog', 'label' => 'Налаштування'],
        4 => ['icon' => 'fas fa-download', 'label' => 'Встановлення']
    ];

    echo <<<HTML
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Встановлення CMS - $title</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff; --primary-color-hover: #0056b3; --success-color: #28a745;
            --danger-color: #dc3545; --light-gray-color: #f8f9fa; --gray-color: #dee2e6;
            --dark-gray-color: #6c757d; --text-color: #212529; --border-radius: 8px;
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            font-family: var(--font-family); background-color: var(--light-gray-color); color: var(--text-color);
            margin: 0; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }
        .container {
            width: 100%; max-width: 680px; background: #fff; padding: 40px; border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        /* Схема кроків */
        .progress-steps {
            display: flex; list-style: none; padding: 0; margin-bottom: 40px; position: relative; justify-content: space-between;
        }
        .progress-steps::before {
            content: ''; position: absolute; top: 18px; left: 15%; right: 15%; height: 4px;
            background-color: var(--gray-color); z-index: 0;
        }
        .step { text-align: center; position: relative; z-index: 1; width: 25%; }
        .step-icon {
            width: 40px; height: 40px; border-radius: 50%; background-color: #fff; color: var(--dark-gray-color);
            border: 3px solid var(--gray-color); display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin: 0 auto 10px; transition: all 0.3s ease;
        }
        .step-label { font-size: 14px; font-weight: 500; color: var(--dark-gray-color); }
        .step.active .step-icon { border-color: var(--primary-color); color: var(--primary-color); }
        .step.active .step-label { color: var(--primary-color); }
        .step.completed .step-icon { border-color: var(--success-color); background-color: var(--success-color); color: #fff; }
        .step.completed .step-label { color: var(--dark-gray-color); }

        h2 { font-size: 22px; text-align: center; border-bottom: 1px solid var(--gray-color); padding-bottom: 15px; margin-bottom: 25px; }
        p { line-height: 1.6; }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--gray-color); }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%; padding: 12px 12px 12px 45px; border: 1px solid var(--gray-color); border-radius: 6px;
            box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2); }
        .button {
            display: inline-block; background-color: var(--primary-color); color: #fff; padding: 12px 25px;
            border: none; border-radius: 6px; text-decoration: none; font-size: 16px; font-weight: 500;
            cursor: pointer; transition: background-color 0.2s ease;
        }
        .button:hover { background-color: var(--primary-color-hover); }
        .button:disabled { background-color: var(--gray-color); cursor: not-allowed; }
        .button i { margin-left: 8px; }
        button.button i { margin-left: 0; margin-right: 8px; }

        .alert { border: 1px solid transparent; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-danger { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        
        .status-box { margin-top: 20px; padding: 12px; border-radius: 6px; display: none; border: 1px solid transparent; text-align: center; }
        .status-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .status-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
HTML;
    echo '<ul class="progress-steps">';
    foreach ($steps as $num => $step_data) {
        $class = '';
        if ($num < $current_step) $class = 'completed';
        if ($num == $current_step) $class = 'active';
        $icon = $step_data['icon'];
        $label = $step_data['label'];
        echo "<li class='step $class'><div class='step-icon'><i class='$icon'></i></div><div class='step-label'>$label</div></li>";
    }
    echo '</ul>';
    echo "<h2>$title</h2>";
}

/**
 * Рендерить HTML-підвал сторінки.
 */
function render_footer() {
    echo <<<HTML
    </div>
</body>
</html>
HTML;
}

/**
 * Виводить повідомлення в журнал прогресу.
 * @param string $message Повідомлення.
 * @param string $type Тип ('info', 'success', 'error').
 */
function log_message($message, $type = 'info') {
    $class = 'log-info';
    if ($type === 'error') $class = 'log-error';
    if ($type === 'success') $class = 'log-success';
    echo "<p class='$class'>[". date('H:i:s'). "] $message</p>";
    flush();
    ob_flush();
}

/**
 * Рекурсивно видаляє каталог та весь його вміст.
 * @param string $dir Шлях до каталогу.
 * @return bool
 */
function delete_directory_recursive($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!delete_directory_recursive($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}
