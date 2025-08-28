<?php
// mvc/c_arrivals.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Arrivals;
use App\Mvc\Models\Warehouses;
use App\Mvc\Models\Goods;
use App\Mvc\Models\Users;

class ArrivalsController extends BaseController  {
    protected $mArrivals;

    public function __construct($params) {
        parent::__construct($params);
        $this->mArrivals = new Arrivals();
    }

    public function indexAction()
    {
        if (!$this->hasPermission('arrivals', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Історія надходжень';
        
        $filters = [];
        // !! КЛЮЧОВА ЗМІНА: Розбираємо параметри з URL !!
        if (isset($this->params['params'])) {
            $pairs = explode('/', $this->params['params']);
            foreach ($pairs as $pair) {
                if (strpos($pair, ':') !== false) {
                    list($key, $value) = explode(':', $pair, 2);
                    $filters[urldecode($key)] = urldecode($value);
                }
            }
        }

        $arrivalsList = $this->mArrivals->getArrivalsList($filters);
        
        $mUsers = new Users();
        $usersList = $mUsers->getAll();
        
        $this->render('v_arrivals_list', [
            'arrivalsList' => $arrivalsList,
            'usersList' => $usersList,
            'filters' => $filters
        ]);
    }

    // Тепер це єдиний метод контролера
    public function addAction()
    {
        if (!$this->hasPermission('arrivals', 'a')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Нове надходження';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('CSRF Error');
            }

            $data = [
                // !! ЗМІНА: Дату генеруємо на сервері, а не беремо з POST !!
                'arrival_datetime' => date('Y-m-d H:i:s'),
                'comment' => trim($_POST['comment'] ?? ''), // Додано обробку коментаря
                'items' => []
            ];

            if (isset($_POST['good_id']) && is_array($_POST['good_id'])) {
                for ($i = 0; $i < count($_POST['good_id']); $i++) {
                    $data['items'][] = [
                        'good_id' => $_POST['good_id'][$i],
                        'warehouse_id' => $_POST['warehouse_id'][$i],
                        'quantity' => $_POST['quantity'][$i]
                    ];
                }
            }
            
            if ($this->mArrivals->processNewArrival($data, $this->currentUser['id'])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Нове надходження успішно опрацьовано.'];
                header('Location: ' . BASE_URL . '/arrivals');
                exit();
            } else {
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка при проведенні надходження.'];
            }
        }
        
        $mWarehouses = new Warehouses();
        $mGoods = new Goods();
        
        $this->render('v_arrival_add', [
            'warehouses' => $mWarehouses->getAll(),
            'goods' => $mGoods->getAll()
        ]);
    }

    public function watchAction()
    {
        if (!$this->hasPermission('arrivals', 'v')) {
            return $this->showAccessDenied();
        }
        
        $documentId = $this->params['id'] ?? '';
        
        $arrivalDetails = $this->mArrivals->getArrivalDetails($documentId);
        
        if (!$arrivalDetails) {
            return $this->notFoundAction();
        }
        
        $arrivalVersions = $this->mArrivals->getArrivalVersions($documentId);
        
        $correctiveDetails = null;
        $comparisonData = null; // Змінна для даних порівняння
        $status = $arrivalDetails['details']['status'];

        if ($status === 'edited') {
            // Знаходимо нову версію, яка замінила поточну
            $correctiveDetails = $this->mArrivals->findCorrectiveTransactions($documentId, $status);
            
            if ($correctiveDetails) {
                $newVersionDocId = $correctiveDetails['details']['details']['document_id'];
                // Отримуємо дані для порівняння
                $comparisonData = $this->mArrivals->getArrivalComparison($documentId, $newVersionDocId);
            }
        } elseif ($status === 'canceled') {
            // Для скасованих документів просто показуємо деталі сторнування
            $correctiveDetails = $this->mArrivals->findCorrectiveTransactions($documentId, $status);
        }
        
        $this->title = 'Перегляд надходження ' . $documentId;

        $this->render('v_arrival_single', [
            'arrivalData' => $arrivalDetails,
            'arrivalVersions' => $arrivalVersions,
            'correctiveDetails' => $correctiveDetails,
            'comparisonData' => $comparisonData // Передаємо дані у шаблон
        ]);
    }

    public function editAction()
    {
        if (!$this->hasPermission('arrivals', 'e')) {
            return $this->showAccessDenied();
        }
        
        $documentId = $this->params['id'] ?? '';
        $this->title = 'Редагування надходження ' . $documentId;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');

            // !! КЛЮЧОВА ЗМІНА: Генеруємо єдиний час для всієї операції !!
            $editTimestamp = date('Y-m-d H:i:s');

            $data = [
                'arrival_datetime' => $editTimestamp, // Передаємо його сюди
                'comment' => trim($_POST['comment'] ?? ''),
                'items' => []
            ];

            if (isset($_POST['good_id']) && is_array($_POST['good_id'])) {
                for ($i = 0; $i < count($_POST['good_id']); $i++) {
                    $data['items'][] = [
                        'good_id' => $_POST['good_id'][$i],
                        'warehouse_id' => $_POST['warehouse_id'][$i],
                        'quantity' => $_POST['quantity'][$i]
                    ];
                }
            }
            
            if ($this->mArrivals->updateArrival($documentId, $data, $this->currentUser['id'])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Надходження успішно оновлено.'];
                header('Location: ' . BASE_URL . '/arrivals');
                exit();
            } else {
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка при оновленні надходження.'];
            }
        }
        
        $arrival = $this->mArrivals->getArrivalByDocumentId($documentId);
        if (!$arrival) {
            return $this->notFoundAction();
        }
        
        $mWarehouses = new Warehouses();
        $mGoods = new Goods();
        
        $this->render('v_arrival_edit', [
            'arrival' => $arrival,
            'warehouses' => $mWarehouses->getAll(),
            'goods' => $mGoods->getAll()
        ]);
    }

    public function deleteAction()
    {
        header('Content-Type: application/json');

        if (!$this->hasPermission('arrivals', 'd')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Немає прав для видалення.']);
            exit();
        }
        
        // Тут можна додати перевірку CSRF-токену з заголовка, якщо потрібно
        
        $documentId = $this->params['id'] ?? '';
        
        if ($this->mArrivals->cancelArrival($documentId, $this->currentUser['id'])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Надходження успішно скасовано.'];
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Не вдалося скасувати надходження.']);
        }
        exit();
    }
}