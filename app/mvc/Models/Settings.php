<?php
// app/Mvc/Models/Settings.php
namespace App\Mvc\Models;

use App\Core\DB;

class Settings
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує всі налаштування у вигляді асоціативного масиву [ключ => значення].
     */
    public function getAll(): array
    {
        $settingsRaw = $this->db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        $settings = [];
        foreach ($settingsRaw as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Оновлює налаштування в базі даних.
     * @param array $data - Асоціативний масив [ключ => нове значення].
     */
    public function update(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // Універсальний запит, що створює або оновлює запис
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            
            $stmt = $this->db->prepare($sql);

            foreach ($data as $key => $value) {
                // Ігноруємо CSRF-токен та інші службові поля
                if ($key !== 'csrf_token') {
                    $stmt->execute([':key' => $key, ':value' => $value]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Settings Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Обробити завантаження файлу favicon.ico.
     * @param array|null $file - Дані з масиву $_FILES.
     * @return array Масив з результатом ['success' => bool, 'message' => string].
     */
    public function handleFaviconUpload(?array $file): array
    {
        // Якщо файл не було завантажено, це не помилка, просто пропускаємо
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => true]; 
        }

        // Перевірка типу файлу
        if ($file['type'] !== 'image/vnd.microsoft.icon' && $file['type'] !== 'image/x-icon') {
            return ['success' => false, 'message' => 'Помилка: Favicon має бути файлом типу .ico'];
        }

        // Перевірка розміру (наприклад, не більше 100 KB)
        if ($file['size'] > 102400) {
            return ['success' => false, 'message' => 'Помилка: Розмір файлу favicon не повинен перевищувати 100 KB.'];
        }

        // Шлях для збереження (корінь сайту)
        $destination = BASE_PATH . '/public/favicon.ico';
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Не вдалося перемістити завантажений файл.'];
        }
    }
}