<?php
// app/Mvc/Controllers/Admin/NotesController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Notes;

class NotesController extends BaseController  
{
    protected $mNotes;

    public function __construct($params) 
    {
        parent::__construct($params);
        $this->mNotes = new Notes();
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
        if (!$this->hasPermission('notes', 'v')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $notes = $this->mNotes->getByUserId($this->currentUser['id']);
        $this->sendJson(['success' => true, 'notes' => $notes]);
    }

    public function createAction()
    {
        if (!$this->hasPermission('notes', 'a')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $content = trim($data['content'] ?? '');
        
        if (empty($content)) {
            $this->sendJson(['success' => false, 'message' => 'Нотатка не може бути порожньою.'], 400);
        }

        $newNote = $this->mNotes->create($this->currentUser['id'], $content);
        if ($newNote) {
            $this->sendJson(['success' => true, 'note' => $newNote]);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка створення нотатки.'], 500);
        }
    }
    
    public function updateAction()
    {
        if (!$this->hasPermission('notes', 'e')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $noteId = (int)($this->params['id'] ?? 0);
        $content = trim($data['content'] ?? '');

        if ($noteId <= 0 || empty($content)) {
            $this->sendJson(['success' => false, 'message' => 'Некоректні дані.'], 400);
        }

        $updatedNote = $this->mNotes->update($noteId, $this->currentUser['id'], $content);
        if ($updatedNote) {
            $this->sendJson(['success' => true, 'note' => $updatedNote]);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка оновлення або нотатку не знайдено.'], 500);
        }
    }
    
    public function deleteAction()
    {
        if (!$this->hasPermission('notes', 'd')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $noteId = (int)($this->params['id'] ?? 0);

        if ($noteId <= 0) {
            $this->sendJson(['success' => false, 'message' => 'Некоректний ID.'], 400);
        }

        if ($this->mNotes->delete($noteId, $this->currentUser['id'])) {
            $this->sendJson(['success' => true]);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка видалення або нотатку не знайдено.'], 500);
        }
    }
}