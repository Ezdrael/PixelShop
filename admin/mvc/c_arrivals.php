<?php
// mvc/c_arrivals.php

class C_Arrivals extends C_Base {
    protected $mArrivals;

    public function __construct($params) {
        parent::__construct($params);
        $this->mArrivals = new M_Arrivals();
    }

    // Тепер це єдиний метод контролера
    public function addAction() {
        if (!$this->hasPermission('arrivals', 'a')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Нове надходження';

        // Обробка POST-запиту
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            $data = [
                'arrival_datetime' => $_POST['arrival_datetime'],
                'user_id' => $this->currentUser['id'], // ID поточного користувача
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
            
            if ($this->mArrivals->processNewArrival($data)) {
                 $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Нове надходження успішно проведено. Залишки оновлено.'];
                header('Location: ' . BASE_URL . '/arrivals/add'); // Перезавантажуємо сторінку для нової операції
                exit();
            } else {
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка при проведенні надходження.'];
            }
        }
        
        // Підготовка даних для GET-запиту (для випадаючих списків у формі)
        $mWarehouses = new M_Warehouses();
        $mGoods = new M_Goods();
        
        $this->render('v_arrival_add', [
            'warehouses' => $mWarehouses->getAll(),
            'goods' => $mGoods->getAll()
        ]);
    }

    public function indexAction() {
        if (!$this->hasPermission('arrivals', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Історія надходжень';
        
        $arrivalsList = $this->mArrivals->getArrivalsList();
        
        $this->render('v_arrivals_list', ['arrivalsList' => $arrivalsList]);
    }

    public function watchAction() {
        if (!$this->hasPermission('arrivals', 'v')) {
            return $this->showAccessDenied();
        }

        // Отримуємо параметри з URL
        $datetime = $this->params['datetime'] ?? '';
        $userId = (int)($this->params['userid'] ?? 0);

        $arrivalData = $this->mArrivals->getArrivalDetails($datetime, $userId);

        $this->title = $arrivalData 
            ? 'Перегляд надходження від ' . date('d.m.Y H:i', strtotime($datetime)) 
            : 'Надходження не знайдено';
        
        $this->render('v_arrival_single', ['arrivalData' => $arrivalData]);
    }
}