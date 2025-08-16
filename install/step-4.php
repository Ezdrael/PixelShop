<?php
/**
 * step-4.php - Виконання встановлення.
 */

render_header("Крок 4: Встановлення...");
echo '<div id="progress-log">';

// --- Функції встановлення ---

function download_from_github($user, $repo) {
    $api_url = "https://api.github.com/repos/$user/$repo/releases/latest";
    $zip_file = '../cms_latest.zip';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CMS Installer');
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!isset($data['zipball_url'])) {
        throw new Exception("Не вдалося отримати URL для завантаження з GitHub API. Відповідь: ". $response);
    }
    $zip_url = $data['zipball_url'];

    $fp = fopen($zip_file, 'w+');
    $ch = curl_init($zip_url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CMS Installer');
    curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception("Помилка cURL при завантаженні архіву: ". curl_error($ch));
    }
    curl_close($ch);
    fclose($fp);
    
    return $zip_file;
}

function extract_archive($zip_file) {
    $zip = new ZipArchive;
    if ($zip->open($zip_file)!== TRUE) {
        throw new Exception("Не вдалося відкрити zip-архів '$zip_file'.");
    }
    $extract_path = '../cms_temp_extract';
    $zip->extractTo($extract_path);
    $zip->close();
    return $extract_path;
}

function move_files_to_root($source_dir) {
    $files = scandir($source_dir);
    $inner_dir_name = $files[span_0](start_span)[span_0](end_span); // Пропускаємо '.' та '..'
    $inner_dir = $source_dir. '/'. $inner_dir_name;

    foreach (scandir($inner_dir) as $file) {
        if ($file!== '.' && $file!== '..') {
            rename($inner_dir. '/'. $file, '../'. $file);
        }
    }
    rmdir($inner_dir);
    rmdir($source_dir);
}

function import_sql($sql_file_path, $config) {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']}";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $full_sql_path = '../'. $sql_file_path;
    if (!file_exists($full_sql_path)) {
        throw new Exception("SQL-файл '$full_sql_path' не знайдено.");
    }

    $sql = file_get_contents($full_sql_path);
    $sql = str_replace('%%PREFIX%%', $config['db_prefix'], $sql);
    
    $pdo->exec($sql);
}

function write_config_file($filename, $config) {
    $config_data = [
        'database' => [
            'host' => $config['db_host'],
            'name' => $config['db_name'],
            'user' => $config['db_user'],
            'password' => $config['db_pass'],
            'prefix' => $config['db_prefix'],
        'site' => [
            'title' => $config['site_title']
    ];

    $config_content = "<?php\n\nreturn ". var_export($config_data, true). ";\n";
    if (file_put_contents('../'. $filename, $config_content) === false) {
        throw new Exception("Не вдалося записати конфігураційний файл '$filename'.");
    }
}

// --- Запуск процесу встановлення ---

try {
    log_message("Завантаження останньої версії CMS з GitHub...");
    $zip_file = download_from_github(GITHUB_CMS_USER, GITHUB_CMS_REPO);
    log_message("Архів '$zip_file' успішно завантажено.");

    log_message("Розпакування файлів CMS...");
    $extracted_path = extract_archive($zip_file);
    log_message("Файли розпаковано до '$extracted_path'.");

    log_message("Переміщення файлів до кореневого каталогу...");
    move_files_to_root($extracted_path);
    log_message("Файли успішно переміщено.");

    log_message("Імпорт схеми бази даних...");
    import_sql(SQL_FILE_PATH, $_SESSION['install_data']);
    log_message("Базу даних успішно імпортовано.");

    log_message("Створення конфігураційного файлу...");
    write_config_file(CONFIG_FILE_NAME, $_SESSION['install_data']);
    log_message("Файл '". CONFIG_FILE_NAME. "' створено.");

    log_message("Очищення тимчасових файлів...");
    unlink($zip_file);
    log_message("Тимчасові файли видалено.");

    log_message("Створення файлу блокування...");
    file_put_contents('../install.lock', 'Installed on '. date('Y-m-d H:i:s'));
    log_message("Файл 'install.lock' створено.");

    echo '</div>';
    echo '<h2>Встановлення завершено!</h2>';
    echo '<p>Ваша CMS успішно встановлена. З міркувань безпеки файли інсталятора буде видалено.</p>';
    echo '<a href="../" class="button">Перейти на сайт</a>';

    // Повне самознищення
    session_destroy();
    delete_directory_recursive(__DIR__);
    if (file_exists('../install-main.php')) {
        unlink('../install-main.php');
    }

} catch (Exception $e) {
    log_message("КРИТИЧНА ПОМИЛКА: ". $e->getMessage(), 'error');
    echo '</div>';
    echo '<h2>Помилка встановлення</h2>';
    echo '<p>Під час встановлення сталася помилка. Будь ласка, перевірте журнал вище для отримання деталей.</p>';
}

render_footer();
exit;
