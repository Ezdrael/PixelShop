<?php
// mvc/m_photo_albums.php

class M_Photoalbums {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує всі альбоми з підрахунком фото та обкладинкою.
     */
    public function getAllWithDetails() {
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

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM photo_albums WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Будує дерево альбомів з плаского масиву.
     */
    public function buildTree(array $albums, $parentId = null): array {
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
    
    public function add(array $data): bool {
        // Встановлюємо parent_id в NULL, якщо він порожній або 0
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
     *
     * @param int $albumId ID альбому, для якого шукаємо предків
     * @return array Масив предків, відсортований від найвищого до прямого батька
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

    public function getChildren(int $parentId): array {
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

    public function update(int $id, array $data): bool {
        // Встановлюємо parent_id в NULL, якщо він порожній або 0
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
    public function hasContent(int $albumId): bool {
        $stmtChildren = $this->db->prepare("SELECT id FROM photo_albums WHERE parent_id = ? LIMIT 1");
        $stmtChildren->execute([$albumId]);
        if ($stmtChildren->fetch()) return true;
        
        $stmtPhotos = $this->db->prepare("SELECT id FROM photos WHERE album_id = ? LIMIT 1");
        $stmtPhotos->execute([$albumId]);
        if ($stmtPhotos->fetch()) return true;
        
        return false;
    }

    /**
     * Виконує видалення альбому та його вмісту.
     */
    public function deleteAlbum(int $albumId, string $action, int $targetAlbumId = null): bool {
        try {
            $this->db->beginTransaction();

            if ($action === 'move_content') {
                if ($targetAlbumId === null || $targetAlbumId == $albumId) {
                    throw new \Exception('Не вказано коректний альбом для переміщення.');
                }
                $stmtMoveAlbums = $this->db->prepare("UPDATE photo_albums SET parent_id = ? WHERE parent_id = ?");
                $stmtMoveAlbums->execute([$targetAlbumId, $albumId]);
                
                $stmtMovePhotos = $this->db->prepare("UPDATE photos SET album_id = ? WHERE album_id = ?");
                $stmtMovePhotos->execute([$targetAlbumId, $albumId]);

            } elseif ($action === 'delete_content') {
                // --- ОСНОВНЕ ВИПРАВЛЕННЯ ТУТ ---
                // Створюємо екземпляр моделі фото, щоб видаляти файли
                $mPhotos = new M_Photos();
                $photos = $mPhotos->getByAlbumId($albumId);
                foreach ($photos as $photo) {
                    // Викликаємо метод, який видаляє і запис в БД, і сам файл
                    $mPhotos->deleteById($photo['id']);
                }
                // Перевірка на вкладені альбоми (забороняємо видалення, якщо вони є)
                $children = $this->getChildren($albumId);
                if (!empty($children)) {
                    throw new \Exception('Неможливо видалити альбом, оскільки він містить вкладені альбоми.');
                }
            }
            
            // Видаляємо сам альбом
            $stmtDelete = $this->db->prepare("DELETE FROM photo_albums WHERE id = ?");
            $stmtDelete->execute([$albumId]);
            
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => $e->getMessage()];
            error_log('Album Deletion Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Встановлює зображення як обкладинку для альбому.
     */
    public function setAlbumCover(int $albumId, int $photoId): bool {
        $sql = "UPDATE photo_albums SET cover_image_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$photoId, $albumId]);
    }
}