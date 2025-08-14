<?php
// mvc/c_photo_albums.php

class C_Photoalbums extends C_Base {
    protected $mAlbums;
    protected $mPhotos;

    public function __construct($params) {
        parent::__construct($params);
        if (!$this->hasPermission('albums', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mAlbums = new M_PhotoAlbums();
        $this->mPhotos = new M_Photos();
    }

    /**
     * Показує головну сторінку з деревом альбомів.
     */
    public function indexAction() {
        $this->title = 'Фотоальбоми';
        $allAlbums = $this->mAlbums->getAllWithDetails();
        $albumsTree = $this->mAlbums->buildTree($allAlbums);
        $this->render('v_photo_albums_list', [
            'albumsTree' => $albumsTree,
            'controller' => $this
        ]);
    }

    /**
     * Показує вміст одного альбому.
     */
    public function viewAlbumAction() {
        $id = (int)($this->params['id'] ?? 0);
        $album = $this->mAlbums->getById($id);
        
        if (!$album) {
            header('Location: ' . BASE_URL . '/albums');
            exit();
        }
        
        $this->title = 'Альбом: ' . htmlspecialchars($album['name']);
        
        $ancestors = $this->mAlbums->getAncestors($id);
        $children = $this->mAlbums->getChildren($id);
        $photos = $this->mPhotos->getByAlbumId($id);

        // --- ОСНОВНЕ ВИПРАВЛЕННЯ ТУТ ---
        // Формуємо масив для хлібних крихт безпосередньо в контролері
        $breadcrumbs = [
            ['name' => 'Фотоальбоми', 'url' => BASE_URL . '/albums']
        ];
        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'name' => $ancestor['name'],
                'url' => BASE_URL . '/albums/view/' . $ancestor['id']
            ];
        }
        // Додаємо поточний альбом, АЛЕ ТАКОЖ З ЙОГО URL
        $breadcrumbs[] = [
            'name' => $album['name'],
            'url' => BASE_URL . '/albums/view/' . $album['id']
        ];

        $this->render('v_photo_album_single', [
            'album' => $album,
            'breadcrumbs' => $breadcrumbs, // Передаємо готовий масив
            'children' => $children,
            'photos' => $photos
        ]);
    }

    public function addAction() {
        if (!$this->hasPermission('albums', 'a')) {
            return $this->showAccessDenied();
        }
        
        $this->title = 'Створення нового альбому';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            if (!empty(trim($_POST['name']))) {
                if ($this->mAlbums->add($_POST)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Новий альбом успішно створено.'];
                    header('Location: ' . BASE_URL . '/albums');
                    exit();
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося створити альбом.'];
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва альбому не може бути порожньою.'];
            }
            // У разі помилки, перезавантажуємо сторінку, щоб не втратити введені дані
            header('Location: ' . BASE_URL . '/albums/add');
            exit();
        }

        // Отримуємо список всіх альбомів для випадаючого списку "Батьківський альбом"
        $allAlbums = $this->mAlbums->getAllWithDetails();
        
        $this->render('v_photo_album_add', ['albums' => $allAlbums]);
    }

    public function editAction() {
        if (!$this->hasPermission('albums', 'e')) {
            return $this.showAccessDenied();
        }
        
        $id = (int)($this->params['id'] ?? 0);
        $album = $this->mAlbums->getById($id);

        if (!$album) {
            header('Location: ' . BASE_URL . '/albums');
            exit();
        }

        $this->title = 'Редагування альбому: ' . htmlspecialchars($album['name']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            if (!empty(trim($_POST['name']))) {
                if ($this->mAlbums->update($id, $_POST)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Альбом успішно оновлено.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося оновити альбом.'];
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва альбому не може бути порожньою.'];
            }
            header('Location: ' . BASE_URL . '/albums/edit/' . $id);
            exit();
        }

        $allAlbums = $this->mAlbums->getAllWithDetails();
        
        $this->render('v_photo_album_edit', [
            'album' => $album,
            'albums' => $allAlbums
        ]);
    }

    public function uploadAction() {
        if (!$this->hasPermission('albums', 'a')) {
            return $this->showAccessDenied();
        }
        
        $albumId = (int)($this->params['id'] ?? 0);
        $album = $this->mAlbums->getById($albumId);

        if (!$album) {
            header('Location: ' . BASE_URL . '/albums');
            exit();
        }

        $this->title = 'Завантаження фото в альбом: ' . htmlspecialchars($album['name']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                $files = $_FILES['photos'];
                $notes = $_POST['notes'] ?? [];
                $errorCount = 0;
                
                // Перебираємо завантажені файли
                for ($i = 0; $i < count($files['name']); $i++) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    $note = $notes[$i] ?? '';
                    
                    $result = $this->mPhotos->addPhoto($file, $albumId, $note);
                    if ($result !== true) {
                        $errorCount++;
                        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка: ' . $result];
                    }
                }

                if ($errorCount == 0) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Фото успішно завантажено.'];
                }
                header('Location: ' . BASE_URL . '/albums/view/' . $albumId);
                exit();
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Ви не обрали жодного файлу.'];
            }
        }

        $this->render('v_photo_upload', ['album' => $album]);
    }

    public function deletePhotoAction() {
        header('Content-Type: application/json');

        if (!$this->hasPermission('albums', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав для видалення.']);
            exit();
        }
        
        // Перевірка CSRF-токена
        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? '';
        if (empty($tokenFromHeader) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            echo json_encode(['success' => false, 'message' => 'CSRF-помилка.']);
            exit();
        }

        $id = (int)($this->params['id'] ?? 0);
        if ($this->mPhotos->deleteById($id)) {
            // Флеш-повідомлення тут не потрібне, оскільки сторінка не перезавантажується
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не вдалося видалити фотографію.']);
        }
        exit();
    }

    public function deleteAction() {
        header('Content-Type: application/json');
        if (!$this->hasPermission('albums', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав для видалення.']); exit();
        }

        $id = (int)($this->params['id'] ?? 0);
        $action = $_POST['action'] ?? 'check'; // 'check', 'delete_content', 'move_content'
        $targetAlbumId = isset($_POST['target_album_id']) ? (int)$_POST['target_album_id'] : null;

        // Перший запит - перевірка на вміст
        if ($action === 'check') {
            if ($this->mAlbums->hasContent($id)) {
                // Альбом не порожній, відправляємо сигнал відкрити складне модальне вікно
                echo json_encode(['success' => false, 'reason' => 'has_content']);
            } else {
                // Альбом порожній, можна видаляти
                if ($this->mAlbums->deleteAlbum($id, 'delete_empty', null)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Порожній альбом успішно видалено.'];
                    echo json_encode(['success' => true]);
                }
            }
        } else {
            // Другий запит - виконання дії з модального вікна
            if ($this->mAlbums->deleteAlbum($id, $action, $targetAlbumId)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Альбом та його вміст успішно оброблено.'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Сталася помилка під час видалення.']);
            }
        }
        exit();
    }

    public function getAlbumsForMoveAction() {
        header('Content-Type: application/json');
        $excludeId = (int)($_GET['exclude_id'] ?? 0);
        
        $allAlbums = $this->mAlbums->getAllWithDetails();
        // Фільтруємо масив, щоб виключити альбом, який видаляється
        $filteredAlbums = array_filter($allAlbums, function($album) use ($excludeId) {
            return $album['id'] != $excludeId;
        });

        // Повертаємо відфільтрований список
        echo json_encode(['success' => true, 'albums' => array_values($filteredAlbums)]);
        exit();
    }

    public function setCoverAction() {
        header('Content-Type: application/json');

        if (!$this->hasPermission('albums', 'e')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав для редагування.']);
            exit();
        }
        
        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? '';
        if (empty($tokenFromHeader) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            echo json_encode(['success' => false, 'message' => 'CSRF-помилка.']);
            exit();
        }

        $albumId = (int)($this->params['albumId'] ?? 0);
        $photoId = (int)($this->params['photoId'] ?? 0);

        if ($this->mAlbums->setAlbumCover($albumId, $photoId)) {
            echo json_encode(['success' => true, 'message' => 'Обкладинку альбому оновлено.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не вдалося оновити обкладинку.']);
        }
        exit();
    }
}