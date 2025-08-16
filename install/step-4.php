<?php
/**
 * step-4.php - Виконання встановлення.
 */

render_header("Крок 4: Встановлення...", 4);
echo '<div id="progress-log">';

// --- Функції встановлення ---

/**
 * Завантажує останню версію CMS з GitHub.
 */
function download_from_github($user, $repo, $branch) {
    $zip_url = "https://api.github.com/repos/$user/$repo/zipball/$branch";
    $zip_file = '../cms_latest.zip';

    $fp = fopen($zip_file, 'w+');
    if ($fp === false) {
        throw new Exception("Не вдалося створити тимчасовий файл для архіву.");
    }

    $ch = curl_init($zip_url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CMS Installer');
    
    // Додаємо заголовок для авторизації, якщо репозиторій CMS також приватний
    if (defined('GITHUB_TOKEN')) {
         curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: token ' . GITHUB_TOKEN]);
    }
    curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("Помилка cURL при завантаженні архіву: " . curl_error($ch));
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code >= 400) {
        rewind($fp);
        $error_response = stream_get_contents($fp);
        fclose($fp);
        unlink($zip_file);
        throw new Exception("Помилка від GitHub API ($http_code). Відповідь: " . htmlspecialchars($error_response));
    }

    curl_close($ch);
    fclose($fp);
    
    return $zip_file;
}

/**
 * Розпаковує архів з файлами CMS.
 */
function extract_archive($zip_file) {
    $zip = new ZipArchive;
    if ($zip->open($zip_file) !== TRUE) {
        throw new Exception("Не вдалося відкрити zip-архів '$zip_file'.");
    }
    $extract_path = '../cms_temp_extract';
    if (!is_dir($extract_path)) {
        mkdir($extract_path, 0755, true);
    }
    $zip->extractTo($extract_path);
    $zip->close();
    return $extract_path;
}

/**
 * Переміщує файли з розпакованої папки в корінь сайту.
 */
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

    foreach (scandir($inner_dir) as $file) {
        if ($file !== '.' && $file !== '..') {
            rename($inner_dir . '/' . $file, '../' . $file);
        }
    }
    
    rmdir($inner_dir);
    rmdir($source_dir);
}

/**
 * Імпортує SQL файли в базу даних.
 * Спочатку створює структуру, потім опціонально наповнює демо-даними.
 */
function import_sql($config) {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Імпорт основної схеми (структури таблиць)
    $schema_file = '../' . SQL_FILE_PATH;
    if (!file_exists($schema_file)) {
        throw new Exception("Файл схеми бази даних '$schema_file' не знайдено.");
    }
    log_message("Імпорт структури бази даних...");
    $sql = file_get_contents($schema_file);
    $sql = str_replace('%%PREFIX%%', $config['db_prefix'], $sql);
    $pdo->exec($sql);
    log_message("Структуру успішно імпортовано.", 'success');

    // 2. Перевірка та імпорт демо-даних (якщо обрано)
    if (!empty($config['install_demo_data'])) {
        $demo_file = '../' . DEMO_SQL_FILE_PATH;
        if (!file_exists($demo_file)) {
            log_message("Файл з демо-даними '$demo_file' не знайдено, пропуск.", 'info');
        } else {
            log_message("Імпорт демонстраційних даних...");
            $sql = file_get_contents($demo_file);
            $sql = str_replace('%%PREFIX%%', $config['db_prefix'], $sql);
            $pdo->exec($sql);
            log_message("Демонстраційні дані успішно імпортовано.", 'success');
        }
    } else {
        log_message("Пропуск імпорту демонстраційних даних за вибором користувача.", 'info');
    }
}

/**
 * Створює конфігураційний файл CMS.
 */
function write_config_file($filename, $config) {
    $config_data = [
        'database' => [
            'host' => $config['db_host'],
            'name' => $config['db_name'],
            'user' => $config['db_user'],
            'password' => $config['db_pass'],
            'prefix' => $config['db_prefix'],
        ],
        'site' => [
            'title' => $config['site_title'],
            'admin_user'  => $config['admin_user'],
            'admin_pass'  => $config['admin_pass'],
            'admin_email' => $config['admin_email']
        ]
    ];

    $config_content = "<?php\n\nreturn " . var_export($config_data, true) . ";\n";
    if (file_put_contents('../' . $filename, $config_content) === false) {
        throw new Exception("Не вдалося записати конфігураційний файл '$filename'.");
    }
}

// --- Запуск процесу встановлення ---

try {
    log_message("Завантаження останньої версії CMS з GitHub...");
    $zip_file = download_from_github(GITHUB_CMS_USER, GITHUB_CMS_REPO, GITHUB_CMS_BRANCH);
    log_message("Архів успішно завантажено.", 'success');

    log_message("Розпакування файлів CMS...");
    $extracted_path = extract_archive($zip_file);
    log_message("Файли розпаковано.", 'success');

    log_message("Переміщення файлів до кореневого каталогу...");
    move_files_to_root($extracted_path);
    log_message("Файли успішно переміщено.", 'success');

    import_sql($_SESSION['install_data']);
    
    log_message("Створення конфігураційного файлу...");
    write_config_file(CONFIG_FILE_NAME, $_SESSION['install_data']);
    log_message("Файл '". CONFIG_FILE_NAME. "' створено.", 'success');

    log_message("Очищення тимчасових файлів...");
    unlink($zip_file);
    log_message("Тимчасові файли видалено.", 'success');

    log_message("Створення файлу блокування...");
    file_put_contents('../install.lock', 'Installed on '. date('Y-m-d H:i:s'));
    log_message("Файл 'install.lock' створено.", 'success');

    echo '</div>'; // Закриваємо progress-log
    echo '<h2><i class="fas fa-check-circle" style="color: var(--success-color);"></i> Встановлення завершено!</h2>';
    echo '<p>Ваша CMS успішно встановлена. Для безпеки каталог інсталятора було видалено.</p>';
    echo '<a href="../" class="button">Перейти на сайт <i class="fas fa-arrow-right"></i></a>';

    session_destroy();
    delete_directory_recursive(__DIR__);

} catch (Exception $e) {
    log_message("КРИТИЧНА ПОМИЛКА: ". $e->getMessage(), 'error');
    echo '</div>';
    echo '<h2>Помилка встановлення</h2>';
    echo '<p>Під час встановлення сталася помилка. Будь ласка, перевірте журнал вище для отримання деталей.</p>';
}

render_footer();
exit;
