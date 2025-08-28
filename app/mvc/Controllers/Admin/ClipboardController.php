<?php
// app/Mvc/Controllers/Admin/ClipboardController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Clipboard;

class ClipboardController extends BaseController  
{
    protected $mClipboard;

    public function __construct($params) 
    {
        parent::__construct($params);
        $this->mClipboard = new Clipboard();
    }

    private function sendJson($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    public function getAction()
    {
        if (!$this->hasPermission('clipboard', 'v')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $items = $this->mClipboard->getByUserId($this->currentUser['id']);
        $this->sendJson(['success' => true, 'items' => $items]);
    }

    public function addAction()
    {
        if (!$this->hasPermission('clipboard', 'a')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $content = trim($data['content'] ?? '');
        
        if (empty($content)) {
            $this->sendJson(['success' => false, 'message' => 'Пустий вміст не може бути додано.'], 400);
        }

        if ($this->mClipboard->add($this->currentUser['id'], $content)) {
            $this->sendJson(['success' => true]);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка збереження в буфер обміну.'], 500);
        }
    }

    public function clearAction()
    {
        // Для очищення потрібні права на видалення
        if (!$this->hasPermission('clipboard', 'd')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }

        if ($this->mClipboard->clearByUserId($this->currentUser['id'])) {
            $this->sendJson(['success' => true]);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка очищення буфера обміну.'], 500);
        }
    }
}