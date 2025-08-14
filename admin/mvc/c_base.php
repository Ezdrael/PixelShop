<?php
// ===================================================================
// Файл: mvc/c_base.php 🕰️
// Розміщення: /mvc/c_base.php
// ===================================================================

abstract class C_Base {
    protected $params;
    protected $title;
    protected $currentUser;

    public function __construct($params) {
        $this->params = $params;
        $this->title = 'AdminShop';

        if (isset($_SESSION['user_id'])) {
            $mUsers = new M_Users();
            $this->currentUser = $mUsers->getById($_SESSION['user_id']);
        }
    }

    protected function render($viewPath, $vars = []) {
        // Логіка флеш-повідомлень
        $flashMessage = null;
        if (isset($_SESSION['flash_message'])) {
            $flashMessage = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']); // Видаляємо після отримання
        }

        $view_vars = array_merge($vars, ['currentUser' => $this->currentUser]);
        $page_content = $this->view($viewPath, $view_vars);
        
        $layout_vars = [
            'title' => $this->title,
            'content' => $page_content,
            'currentUser' => $this->currentUser,
            'current_route' => $_GET['route'] ?? '',
            'flashMessage' => $flashMessage // Передаємо повідомлення в головний шаблон
        ];

        echo $this->view('v_main_layout', $layout_vars);
    }

    private function view($path, $vars = []) {
        extract($vars);
        ob_start();
        include ROOT . "/mvc/{$path}.php";
        return ob_get_clean();
    }

    public  function hasPermission($resource, $action) {
        if (!$this->currentUser) {
            return false;
        }
        $permission_string = $this->currentUser['perm_' . $resource] ?? '';
        return strpos($permission_string, $action) !== false;
    }

    protected function showAccessDenied() {
        $this->title = 'Доступ заборонено';
        $content = "<h2>Помилка доступу</h2><p>У вас немає прав для виконання цієї дії.</p>";
        $this->render('v_simple_page', ['page_content' => $content]);
    }
}