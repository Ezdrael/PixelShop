<?php
// app/Mvc/Controllers/Admin/CurrenciesController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Currencies;
use App\Core\CurrencyRateService; 

class CurrenciesController extends BaseController
{
    protected $mCurrencies;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->mCurrencies = new Currencies();
    }

    public function indexAction()
    {
        if (!$this->hasPermission('currencies', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Керування валютами';
        $currencies = $this->mCurrencies->getAll();
        $this->render('v_currencies_list', ['currencies' => $currencies]);
    }


    /**
     * Новий метод для оновлення курсів.
     */
    public function updateRatesAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('currencies', 'e')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав доступу.']);
            exit();
        }

        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? $headers['x-csrf-token'] ?? '';
        if (empty($tokenFromHeader) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            echo json_encode(['success' => false, 'message' => 'Помилка валідації CSRF-токена.']);
            exit();
        }

        $rateService = new CurrencyRateService();
        $rates = $rateService->fetchRates();

        if ($rates === null) {
            echo json_encode(['success' => false, 'message' => 'Не вдалося отримати курси з API. Перевірте лог-файли сервера.']);
            exit();
        }

        if ($this->mCurrencies->updateRates($rates)) {
            echo json_encode(['success' => true, 'message' => 'Курси валют успішно оновлено.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка при збереженні курсів в БД.']);
        }
        exit();
    }

    // Метод для додавання нової валюти (для AJAX)
    public function addAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('currencies', 'a')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав доступу.']);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->mCurrencies->add($data);

        if ($newId) {
            echo json_encode(['success' => true, 'id' => $newId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка додавання.']);
        }
        exit();
    }

    // Метод для видалення валюти (для AJAX)
    public function deleteAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('currencies', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав доступу.']);
            exit();
        }
        
        $id = (int)($this->params['id'] ?? 0);
        if ($this->mCurrencies->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка видалення.']);
        }
        exit();
    }
}