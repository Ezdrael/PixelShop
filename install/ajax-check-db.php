<?php
/**
 * ajax-check-db.php - Обробник AJAX-запиту для перевірки з'єднання з БД.
 */

// Встановлюємо заголовок, щоб браузер розумів, що це відповідь у форматі JSON
header('Content-Type: application/json');

// Перевіряємо, чи дані надіслано методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неправильний тип запиту.']);
    exit;
}

// Отримуємо дані з запиту
$db_host = $_POST['db_host'] ?? '';
$db_name = $_POST['db_name'] ?? '';
$db_user = $_POST['db_user'] ?? '';
$db_pass = $_POST['db_pass'] ?? '';

// Спроба підключення до БД
try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    new PDO($dsn, $db_user, $db_pass, $options);

    // Якщо з'єднання успішне
    echo json_encode(['success' => true, 'message' => 'З\'єднання з базою даних успішно встановлено!']);

} catch (PDOException $e) {
    // Якщо сталася помилка
    echo json_encode(['success' => false, 'message' => 'Помилка: ' . $e->getMessage()]);
}
