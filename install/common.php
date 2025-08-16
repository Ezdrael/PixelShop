<?php
/**
 * common.php - Спільні функції та налаштування для інсталятора.
 */

// ... (Налаштування середовища та константи залишаються без змін) ...
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);
session_start(['use_only_cookies' => true]);

define('GITHUB_CMS_USER', 'Ezdrael');
define('GITHUB_CMS_REPO', 'PixelShop');
define('GITHUB_CMS_BRANCH', 'main');
define('REQUIRED_PHP_VERSION', '8.0.0');
define('SQL_FILE_PATH', 'database/schema.sql');
define('CONFIG_FILE_NAME', 'config.php');

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
            --primary-color: #007bff; --success-color: #28a745; --danger-color: #dc3545;
            --light-gray-color: #f8f9fa; --gray-color: #dee2e6; --dark-gray-color: #6c757d;
            --text-color: #212529; --border-radius: 8px; --font-family: 'Inter', sans-serif;
        }
        body { font-family: var(--font-family); background-color: var(--light-gray-color); color: var(--text-color); margin: 0; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { width: 100%; max-width: 680px; background: #fff; padding: 40px; border-radius: var(--border-radius); box-shadow: 0 4px
        /* ... (решта стилів залишається схожою, але з доповненнями) ... */
        /* Схема кроків */
        .progress-steps { display: flex; list-style: none; padding: 0; margin-bottom: 40px; position: relative; justify-content: space-between; }
        .progress-steps::before { content: ''; position: absolute; top: 18px; left: 15%; right: 15%; height: 4px; background-color: var(--gray-color); z-index: 0; }
        .step { text-align: center; position: relative; z-index: 1; width: 25%; }
        .step-icon { width: 40px; height: 40px; border-radius: 50%; background-color: #fff; color: var(--dark-gray-color); border: 3px solid var(--gray-color); display: flex; align-items: center; justify-content: center; font-size: 18px; margin: 0 auto 10px; transition: all 0.3s ease; }
        .step-label { font-size: 14px; font-weight: 500; color: var(--dark-gray-color); }
        .step.active .step-icon { border-color: var(--primary-color); color: var(--primary-color); }
        .step.active .step-label { color: var(--primary-color); }
        .step.completed .step-icon { border-color: var(--success-color); background-color: var(--success-color); color: #fff; }
        .step.completed .step-label { color: var(--dark-gray-color); }
        h1, h2 { text-align: center; }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--gray-color); }
        .input-group input { padding-left: 45px; width: 100%; box-sizing: border-box; }
        /* ... (решта стилів з попереднього кроку) ... */
    </style>
</head>
<body>
    <div class="container">
HTML;
    // Логіка для відображення схеми кроків
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
    echo "<h2>$title</h2>"; // Додаємо заголовок після схеми
}

// ... (render_footer та інші функції залишаються без змін) ...
function render_footer() { /* ... */ }
function log_message($message, $type = 'info') { /* ... */ }
function delete_directory_recursive($dir) { /* ... */ }
