<?php
// app/Mvc/Controllers/Admin/CalendarController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Calendar;
use App\Mvc\Models\Users;

class CalendarController extends BaseController
{
    protected $mCalendar;
    protected $mUsers;

    public function __construct($params)
    {
        parent::__construct($params);
        
        // Перевіряємо базове право на перегляд
        if (!$this->hasPermission('calendar', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        
        $this->mCalendar = new Calendar();
        $this->mUsers = new Users();
    }

    public function indexAction()
    {
        $this->title = 'Календар';
        
        // Підключаємо стилі для FullCalendar
        $this->addCSS('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css');
        
        // 1. Спочатку бібліотеку
        $this->addJS('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js');
        // 2. Потім локалізацію
        $this->addJS('https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/uk.js');
        // 3. І лише в кінці - наш скрипт для ініціалізації
        $this->addJS(PROJECT_URL . '/resources/js/calendar-init.js');

        $this->render('v_calendar_list');
    }

    // --- AJAX ACTIONS ---
    public function getEventsAction()
    {
        header('Content-Type: application/json');
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $events = $this->mCalendar->getEvents($start, $end);
        echo json_encode($events);
        exit();
    }
    
    public function createEventAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('calendar', 'a')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав']); exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $newEventId = $this->mCalendar->addEvent($data, $this->currentUser['id']);
        if ($newEventId) {
            echo json_encode(['success' => true, 'id' => $newEventId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка створення події']);
        }
        exit();
    }
    
    public function updateEventAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('calendar', 'e')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав']); exit();
        }
        $id = (int)($this->params['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->mCalendar->updateEvent($id, $data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка оновлення події']);
        }
        exit();
    }
    
    public function deleteEventAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('calendar', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав']); exit();
        }
        $id = (int)($this->params['id'] ?? 0);
        if ($this->mCalendar->deleteEvent($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка видалення події']);
        }
        exit();
    }

    // --- PAGE ACTIONS ---
    public function addAction()
    {
        if (!$this->hasPermission('calendar', 'a')) return $this->showAccessDenied();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->mCalendar->addEvent($_POST, $this->currentUser['id'])) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Подію успішно створено.'];
                header('Location: ' . BASE_URL . '/calendar'); exit();
            }
        }
        $this->title = 'Нова подія';
        
        // ✅ ДОДАНО: Логіка для передачі користувачів
        $this->addJS(PROJECT_URL . '/resources/js/tinymce-init.js'); // Підключаємо скрипт ініціалізації
        $users = $this->mUsers->getAll();
        $usersForMentions = array_map(function($user) {
            return ['id' => $user['id'], 'name' => $user['name']];
        }, $users);

        $this->render('v_calendar_add', [
            'usersJson' => json_encode($usersForMentions)
        ]);
    }

    public function editAction()
    {
        if (!$this->hasPermission('calendar', 'e')) return $this->showAccessDenied();
        $eventId = (int)($this->params['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->mCalendar->updateEvent($eventId, $_POST)) {
                 $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Подію успішно оновлено.'];
                 header('Location: ' . BASE_URL . '/calendar'); exit();
            }
        }
        $this->title = 'Редагування події';
        
        // ✅ ДОДАНО: Логіка для передачі користувачів
        $this->addJS(PROJECT_URL . '/resources/js/tinymce-init.js');
        $users = $this->mUsers->getAll();
        $usersForMentions = array_map(function($user) {
            return ['id' => $user['id'], 'name' => $user['name']];
        }, $users);
        $event = $this->mCalendar->getEventById($eventId);

        $this->render('v_calendar_edit', [
            'event' => $event,
            'usersJson' => json_encode($usersForMentions)
        ]);
    }

    public function watchAction()
    {
        $eventId = (int)($this->params['id'] ?? 0);
        $event = $this->mCalendar->getEventById($eventId);
        $this->title = $event ? $event['title'] : 'Подію не знайдено';
        $this->render('v_calendar_single', ['event' => $event]);
    }
}