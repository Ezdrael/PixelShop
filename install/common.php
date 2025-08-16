<?php
/**
 * common.php - Спільні функції та налаштування для інсталятора.
 */

// --- Налаштування середовища ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 хвилин на виконання

session_start([
    'use_only_cookies' => true
]);

// --- Визначення констант та конфігурації ---
define('GITHUB_CMS_USER', 'your-github-username'); // Замініть на ваш логін GitHub
define('GITHUB_CMS_REPO', 'your-cms-repo');      // Замініть на назву репозиторію CMS
define('REQUIRED_PHP_VERSION', '8.0.0');
define('SQL_FILE_PATH', 'database/schema.sql'); // Шлях до SQL файлу всередині репозиторію CMS
define('CONFIG_FILE_NAME', 'config.php');

/**
 * Рендерить HTML-шапку сторінки.
 * @param string $title Заголовок сторінки.
 */
function render_header($title) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Встановлення CMS - $title</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 20px; }
       .container { max-width: 600px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #1d2129; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
       .button { display: inline-block; background-color: #1877f2; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; text-decoration: none; font-size: 16px; cursor: pointer; }
       .button:hover { background-color: #166fe5; }
       .error-box { background: #ffebe8; border: 1px solid #dd3c10; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        #progress-log { border: 1px solid #ccc; padding: 10px; height: 200px; overflow-y: scroll; background: #fafafa; margin-bottom: 20px; }
       .log-info { color: #333; }
       .log-error { color: #d00; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Встановлення CMS</h1>
HTML;
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
 * @param string $type Тип ('info' або 'error').
 */
function log_message($message, $type = 'info') {
    $class = $type === 'error'? 'log-error' : 'log-info';
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
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' |

| $item == '..') continue;
        if (!delete_directory_recursive($dir. DIRECTORY_SEPARATOR. $item)) return false;
    }
    return rmdir($dir);
}
