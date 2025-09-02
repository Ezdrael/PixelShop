<?php
// mvc/m_photoalbums.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Photoalbums {
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує всі альбоми з підрахунком фото та обкладинкою.
     */
    public function getAllWithDetails()
    {
        $sql = "
            SELECT 
                a.*, 
                COUNT(p.id) as photo_count,
                cover_photo.filename as cover_image_filename
            FROM 
                photo_albums a 
            LEFT JOIN 
                photos p ON a.id = p.album_id
            LEFT JOIN
                photos cover_photo ON a.cover_image_id = cover_photo.id
            GROUP BY 
                a.id 
            ORDER BY 
                a.name ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM photo_albums WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Будує дерево альбомів з плаского масиву.
     */
    public function buildTree(array $albums, $parentId = null): array
    {
        $branch = [];
        foreach ($albums as $album) {
            if ($album['parent_id'] == $parentId) {
                $children = $this->buildTree($albums, $album['id']);
                if ($children) {
                    $album['children'] = $children;
                }
                $branch[] = $album;
            }
        }
        return $branch;
    }
    
    public function add(array $data): bool
    {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $sql = "INSERT INTO photo_albums (name, description, parent_id) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            trim($data['name']),
            trim($data['description']),
            $parentId
        ]);
    }

    /**
     * Отримує всіх предків для заданого альбому.
     */
    public function getAncestors(int $albumId): array
    {
        $ancestors = [];
        $currentAlbum = $this->getById($albumId);

        while (isset($currentAlbum['parent_id']) && $currentAlbum['parent_id'] != 0) {
            $parentAlbum = $this->getById($currentAlbum['parent_id']);
            if ($parentAlbum) {
                array_unshift($ancestors, $parentAlbum);
                $currentAlbum = $parentAlbum;
            } else {
                break;
            }
        }
        return $ancestors;
    }

    public function getChildren(int $parentId): array
    {
        $sql = "
            SELECT 
                a.*,
                p.filename as cover_image_filename
            FROM 
                photo_albums a
            LEFT JOIN
                photos p ON a.cover_image_id = p.id
            WHERE 
                a.parent_id = ?
            ORDER BY 
                a.name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $sql = "UPDATE photo_albums SET name = ?, description = ?, parent_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            trim($data['name']),
            trim($data['description']),
            $parentId,
            $id
        ]);
    }
    
    /**
     * Перевіряє, чи є в альбомі дочірні альбоми або фотографії.
     */
    public function hasContent(int $albumId): bool
    {
        $stmtChildren = $this->db->prepare("SELECT 1 FROM photo_albums WHERE parent_id = ? LIMIT 1");
        $stmtChildren->execute([$albumId]);
        if ($stmtChildren->fetch()) {
            return true;
        }
        
        $stmtPhotos = $this->db->prepare("SELECT 1 FROM photos WHERE album_id = ? LIMIT 1");
        $stmtPhotos->execute([$albumId]);
        if ($stmtPhotos->fetch()) {
            return true;
        }
        
        return false;
    }

    /**
     * Встановлює зображення як обкладинку для альбому.
     */
    public function setAlbumCover(int $albumId, int $photoId): bool
    {
        $sql = "UPDATE photo_albums SET cover_image_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$photoId, $albumId]);
    }

    /**
     * Отримує список всіх альбомів, які можуть бути батьківськими.
     * Виключає поточний альбом та всіх його нащадків.
     * @param int $excludeAlbumId - ID поточного альбому, який редагується.
     * @return array
     */
    public function getAvailableParentAlbums(): array
    {
        $sql = "SELECT id, name, parent_id FROM photo_albums ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Переміщує вміст одного альбому в інший, а потім видаляє вихідний альбом.
     */
    public function moveContentsAndDeleteAlbum(int $sourceAlbumId, int $targetAlbumId): bool
    {
        try {
            $this->db->beginTransaction();
            $stmtMoveAlbums = $this->db->prepare("UPDATE photo_albums SET parent_id = ? WHERE parent_id = ?");
            $stmtMoveAlbums->execute([$targetAlbumId, $sourceAlbumId]);
            // Виправлено помилку: оновлюємо таблицю photos, а не photo_albums
            $stmtMovePhotos = $this->db->prepare("UPDATE photos SET album_id = ? WHERE album_id = ?");
            $stmtMovePhotos->execute([$targetAlbumId, $sourceAlbumId]);
            $stmtDelete = $this->db->prepare("DELETE FROM photo_albums WHERE id = ?");
            $stmtDelete->execute([$sourceAlbumId]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Album Content Move Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Головний публічний метод для видалення. Керує транзакцією.
     */
    public function deleteAlbumRecursively(int $albumId): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Знаходимо всі вкладені альбоми (всю ієрархію вниз)
            $allAlbumIdsToDelete = array_merge([$albumId], $this->getDescendantIds($albumId));
            $placeholders = implode(',', array_fill(0, count($allAlbumIdsToDelete), '?'));

            // 2. Знаходимо всі фотографії у цих альбомах, щоб видалити їхні файли
            $sqlPhotos = "SELECT filename FROM photos WHERE album_id IN ($placeholders)";
            $stmtPhotos = $this->db->prepare($sqlPhotos);
            $stmtPhotos->execute($allAlbumIdsToDelete);
            $photosToDelete = $stmtPhotos->fetchAll(PDO::FETCH_COLUMN);

            // 3. Видаляємо фізичні файли фотографій
            foreach ($photosToDelete as $filename) {
                $filePath = BASE_PATH . '/public/resources/img/products/' . $filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // 4. Видаляємо ОДИН батьківський альбом.
            // База даних автоматично видалить всі дочірні альбоми та всі фотографії
            // завдяки налаштуванню зовнішніх ключів (ON DELETE CASCADE / ON DELETE SET NULL).
            $stmtDelete = $this->db->prepare("DELETE FROM photo_albums WHERE id = ?");
            $stmtDelete->execute([$albumId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Album Deletion Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Допоміжний рекурсивний метод для отримання ID всіх нащадків.
     */
    private function getDescendantIds(int $parentId): array
    {
        $stmt = $this->db->prepare("SELECT id FROM photo_albums WHERE parent_id = ?");
        $stmt->execute([$parentId]);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $descendantIds = $children;
        
        foreach ($children as $childId) {
            $descendantIds = array_merge($descendantIds, $this->getDescendantIds($childId));
        }
        
        return $descendantIds;
    }

    /**
     * Отримує список всіх альбомів для переміщення, виключаючи вказаний та його нащадків.
     */
    public function getAlbumsForMove(int $excludeAlbumId): array
    {
        $descendants = $this->getDescendantIds($excludeAlbumId);
        $descendants[] = $excludeAlbumId;

        $placeholders = implode(',', array_fill(0, count($descendants), '?'));
        $sql = "SELECT id, name FROM photo_albums WHERE id NOT IN ($placeholders) ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($descendants);
        return $stmt->fetchAll();
    }

    /**
     * Внутрішній рекурсивний метод, який виконує видалення.
     */
    private function _recursiveDeleteWorker(int $albumId): void
    {
        $stmtChildren = $this->db->prepare("SELECT id FROM photo_albums WHERE parent_id = ?");
        $stmtChildren->execute([$albumId]);
        $childrenIds = $stmtChildren->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($childrenIds as $childId) {
            $this->_recursiveDeleteWorker($childId);
        }

        // === ВИПРАВЛЕНО ТУТ: "album-id" замінено на "album_id" ===
        $stmtPhotos = $this->db->prepare("SELECT id, filename FROM photos WHERE album_id = ?");
        $stmtPhotos->execute([$albumId]);
        $photos = $stmtPhotos->fetchAll();

        if (!empty($photos)) {
            $photoIds = array_column($photos, 'id');
            foreach ($photos as $photo) {
                // Використовуємо BASE_PATH для правильного шляху
                $filePath = BASE_PATH . '/public/resources/img/products/' . $photo['filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $inQuery = implode(',', array_fill(0, count($photoIds), '?'));
            $stmtDeletePhotos = $this->db->prepare("DELETE FROM photos WHERE id IN ($inQuery)");
            $stmtDeletePhotos->execute($photoIds);
        }

        $stmtDeleteAlbum = $this->db->prepare("DELETE FROM photo_albums WHERE id = ?");
        $stmtDeleteAlbum->execute([$albumId]);
    }

    /**
     * Отримує список всіх альбомів для переміщення, виключаючи вказаний та його нащадків.
     * Повертає готову деревоподібну структуру.
     *
     * @param int $excludeAlbumId ID альбому, який потрібно виключити разом з його деревом.
     * @return array
     */
    public function getAvailableAlbumsForMove(int $excludeAlbumId): array
    {
        // 1. Знаходимо ID всіх нащадків альбому, який видаляється
        $descendantIds = $this->getDescendantIds($excludeAlbumId);
        
        // 2. Додаємо до списку виключення сам батьківський альбом
        $excludedIds = array_merge([$excludeAlbumId], $descendantIds);
        
        // 3. Формуємо плейсхолдери для безпечного SQL-запиту
        $placeholders = implode(',', array_fill(0, count($excludedIds), '?'));

        // 4. Вибираємо всі альбоми, ID яких НЕ входять до списку виключення
        $sql = "SELECT id, name, parent_id FROM photo_albums WHERE id NOT IN ($placeholders) ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($excludedIds);
        $flatAlbumList = $stmt->fetchAll();

        // 5. Будуємо з відфільтрованого плаского списку ієрархічне дерево
        return $this->buildTree($flatAlbumList);
    }

}