<?php
// mvc/m_photos.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Photos {
    private $db;
    public $uploadDir = BASE_PATH . '/public/resources/img/products/';

    public function __construct() {
        $this->db = DB::getInstance();
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function getByAlbumId(int $albumId): array {
        $stmt = $this->db->prepare("SELECT * FROM photos WHERE album_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$albumId]);
        return $stmt->fetchAll();
    }

    /**
     * Обробити завантаження одного фото.
     * @param array $file - Один файл з масиву $_FILES.
     * @param int $albumId - ID альбому, куди завантажується фото.
     * @param string $note - Примітка до фото.
     * @return bool|string - Повертає true у разі успіху або рядок з помилкою.
     */
    public function addPhoto(array $file, int $albumId, string $note = '') {
        // 1. Перевірка на помилки завантаження
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Помилка завантаження файлу.';
        }

        // 2. Перевірка типу файлу (дозволяємо тільки зображення)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return 'Недозволений тип файлу. Дозволено тільки jpeg, png, gif, webp.';
        }

        $this->db->beginTransaction();
        try {
            // 3. Створюємо запис в БД, щоб отримати унікальний ID
            $stmt = $this->db->prepare("INSERT INTO photos (album_id, filename, note) VALUES (?, '', ?)");
            $stmt->execute([$albumId, $note]);
            $photoId = $this->db->lastInsertId();

            // 4. Формуємо нове, унікальне ім'я файлу
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = $photoId . '.' . strtolower($extension);
            $uploadPath = $this->uploadDir . $newFilename;

            // 5. Переміщуємо файл
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new \Exception('Не вдалося перемістити завантажений файл.');
            }

            // 6. Оновлюємо запис в БД з фінальним ім'ям файлу
            $stmtUpdate = $this->db->prepare("UPDATE photos SET filename = ? WHERE id = ?");
            $stmtUpdate->execute([$newFilename, $photoId]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Photo Upload Error: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM photos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Видаляє фотографію за її ID (із запису в БД, і з диска).
     */
    public function deleteById(int $id): bool {
        // Спочатку знаходимо запис, щоб отримати ім'я файлу
        $photo = $this->getById($id);
        if (!$photo) {
            return false; // Фото не знайдено
        }

        try {
            $this->db->beginTransaction();

            // Видаляємо запис з бази даних
            $stmt = $this->db->prepare("DELETE FROM photos WHERE id = ?");
            $stmt->execute([$id]);

            // Видаляємо файл з сервера
            $filePath = $this->uploadDir . $photo['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Photo Deletion Error: ' . $e->getMessage());
            return false;
        }
    }
}