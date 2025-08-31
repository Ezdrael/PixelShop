<?php
// mvc/c_photoalbums.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Photoalbums;
use App\Mvc\Models\Photos;

class PhotoalbumsController extends BaseController  {
    protected $mAlbums;
    protected $mPhotos;

    public function __construct($params) {
        parent::__construct($params);
        if (!$this->hasPermission('albums', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mAlbums = new Photoalbums();
        $this->mPhotos = new Photos();
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

        $breadcrumbs = [
            ['name' => 'Фотоальбоми', 'url' => BASE_URL . '/albums']
        ];
        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'name' => $ancestor['name'],
                'url' => BASE_URL . '/albums/view/' . $ancestor['id']
            ];
        }
        $breadcrumbs[] = [
            'name' => $album['name'],
            'url' => BASE_URL . '/albums/view/' . $album['id']
        ];

        $this->render('v_photo_album_single', [
            'album' => $album,
            'ancestors' => $ancestors,
            'breadcrumbs' => $breadcrumbs, 
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
            header('Location: ' . BASE_URL . '/albums/add');
            exit();
        }

        $allAlbums = $this->mAlbums->getAllWithDetails();
        
        $this->render('v_photo_album_add', ['albums' => $allAlbums]);
    }

    public function editAction() {
    if (!$this->hasPermission('albums', 'e')) {
        return $this->showAccessDenied();
    }
    
    $id = (int)($this->params['id'] ?? 0);
    $album = $this->mAlbums->getById($id);

    if (!$album) {
        header('Location: ' . BASE_URL . '/albums');
        exit();
    }

    $this->title = 'Редагування альбому: ' . htmlspecialchars($album['name']);

    // === ДОДАНО ВІДСУТНІЙ БЛОК КОДУ ===
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('CSRF Error');
        }
        
        if (!empty(trim($_POST['name']))) {
            if ($this->mAlbums->update($id, $_POST)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Дані альбому успішно оновлено.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося оновити дані альбому.'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва альбому не може бути порожньою.'];
        }
        
        // Перенаправляємо назад на сторінку редагування, щоб показати повідомлення
        header('Location: ' . BASE_URL . '/albums/edit/' . $id);
        exit();
    }
    // === КІНЕЦЬ НОВОГО БЛОКУ ===

    $availableParents = $this->mAlbums->getAvailableParentAlbums($id);

    $this->render('v_photo_album_edit', [
        'album' => $album,
        'albums' => $availableParents
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
        // Встановлюємо заголовок, що відповідь буде у форматі JSON
        header('Content-Type: application/json');

        // 1. Перевірка прав доступу на видалення
        if (!$this->hasPermission('albums', 'd')) {
            http_response_code(403); // Forbidden
            echo json_encode(['success' => false, 'message' => 'У вас немає прав для видалення.']);
            exit();
        }

        // 2. Дія має приймати лише POST-запити для безпеки
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Неприпустимий метод запиту.']);
            exit();
        }

        // 3. Перевірка CSRF-токену, що надходить у заголовку
        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? $headers['x-csrf-token'] ?? '';
        if (empty($tokenFromHeader) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Помилка валідації CSRF-токена.']);
            exit();
        }

        // 4. Отримання та валідація ID фотографії з URL
        $photoId = (int)($this->params['id'] ?? 0);
        if ($photoId <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Некоректний ID фотографії.']);
            exit();
        }

        // 5. Виклик методу моделі для видалення фото
        // Модель mPhotos вже обробляє видалення файлу з диска та запису з БД
        $success = $this->mPhotos->deleteById($photoId);

        // 6. Повернення результату у форматі JSON
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Фото успішно видалено.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Не вдалося видалити фотографію.']);
        }
        exit();
    }

    public function deleteAction()
    {
        header('Content-Type: application/json');

        if (!$this->hasPermission('albums', 'd')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Немає прав для видалення.']);
            exit();
        }

        // --- ДОДАНО: Перевірка CSRF-токену для безпеки ---
        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? $headers['x-csrf-token'] ?? '';
        if (empty($tokenFromHeader) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Помилка валідації CSRF-токена.']);
            exit();
        }

        $albumId = (int)($this->params['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? 'delete_content';
        $targetAlbumId = (int)($data['target_album_id'] ?? 0);

        // Перевіряємо, чи існує альбом перед видаленням
        if (!$this->mAlbums->getById($albumId)) {
             echo json_encode(['success' => false, 'message' => 'Альбом не знайдено.']);
             exit();
        }

        $success = false;
        if ($action === 'delete_content' || $action === 'delete_empty') {
            $success = $this->mAlbums->deleteAlbumRecursively($albumId);
        } elseif ($action === 'move_content') {
            if ($targetAlbumId > 0) {
                $success = $this->mAlbums->moveContentsAndDeleteAlbum($albumId, $targetAlbumId);
            } else {
                echo json_encode(['success' => false, 'message' => 'Не вказано альбом для переміщення.']);
                exit();
            }
        }

        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Альбом успішно видалено.'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не вдалося виконати операцію. Можливо, альбом містить під-альбоми.']);
        }
        exit();
    }

    public function getAlbumsForMoveAction()
    {
        header('Content-Type: application/json');
        $excludeId = (int)($_GET['exclude'] ?? 0);
        $albums = $this->mAlbums->getAlbumsForMove($excludeId);
        echo json_encode(['success' => true, 'albums' => $albums]);
        exit();
    }

    public function setCoverAction() {
        // Встановлюємо заголовок, що відповідь буде у форматі JSON
        header('Content-Type: application/json');

        // 1. Перевірка прав доступу на редагування альбомів
        if (!$this->hasPermission('albums', 'e')) {
            http_response_code(403); // Forbidden
            echo json_encode(['success' => false, 'message' => 'Доступ заборонено.']);
            exit();
        }

        // 2. Дія має приймати лише POST-запити
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Неприпустимий метод запиту.']);
            exit();
        }

        // 3. Перевірка CSRF-токену
        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? $headers['x-csrf-token'] ?? '';
        if (empty($tokenFromHeader) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Помилка валідації CSRF-токена.']);
            exit();
        }

        // 4. Отримання параметрів з URL (згідно з роутером)
        $albumId = (int)($this->params['albumId'] ?? 0);
        $photoId = (int)($this->params['photoId'] ?? 0);

        if ($albumId <= 0 || $photoId <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Некоректні ID альбому або фото.']);
            exit();
        }

        // 5. Виклик методу моделі для оновлення обкладинки
        $success = $this->mAlbums->setAlbumCover($albumId, $photoId);

        // 6. Повернення результату у форматі JSON
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Обкладинку альбому успішно оновлено.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Не вдалося оновити обкладинку в базі даних.']);
        }
        exit();
    }

    public function getMAlbums() {
        return $this->mAlbums;
    }
}