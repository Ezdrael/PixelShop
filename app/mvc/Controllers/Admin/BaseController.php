<?php
// mvc/c_base.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Users;
use App\Core\Controller;
use App\Mvc\Models\Settings;


// 3. Тепер BaseController наслідує імпортований App\Core\Controller
abstract class BaseController extends Controller
{
    protected $params;
    protected $title;
    protected $currentUser;
    protected $pageCSS = [];
    protected $pageJS = [];
    protected $current_route = '';
    protected $siteSettings;

    public function __construct($params)
    {
        parent::__construct(); // Викликаємо конструктор батьківського класу, якщо він є
        
        $this->params = $params;
        $this->title = 'AdminShop';
        $this->current_route = $params['current_route'] ?? '';

        if (isset($_SESSION['user_id'])) {
            $mUsers = new Users();
            $this->currentUser = $mUsers->getById($_SESSION['user_id']);
        }
        $mSettings = new Settings();
        $this->siteSettings = $mSettings->getAll();
    }
    
    protected function addCSS($path)
    {
        $this->pageCSS[] = $path;
    }

    protected function addJS($path)
    {
        $this->pageJS[] = $path;
    }

    protected function render($viewPath, $vars = [])
    {
        $flashMessage = null;
        if (isset($_SESSION['flash_message'])) {
            $flashMessage = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        }

        $view_vars = array_merge($vars, ['currentUser' => $this->currentUser]);
        $page_content = $this->view($viewPath, $view_vars);
        
        $layout_vars = [
            'title' => $this->title,
            'content' => $page_content,
            'currentUser' => $this->currentUser,
            'current_route' => $this->current_route,
            'flashMessage' => $flashMessage,
            'pageCSS' => $this->pageCSS,
            'pageJS' => $this->pageJS,
            'siteSettings' => $this->siteSettings
        ];

        echo $this->view('v_main_layout', $layout_vars);
    }

    private function view($path, $vars = [])
    {
        extract($vars);
        ob_start();
        include BASE_PATH . "/app/Mvc/Views/admin/{$path}.php";
        return ob_get_clean();
    }

    public function hasPermission($resource, $action)
    {
        if (!$this->currentUser) {
            return false;
        }
        $permission_string = $this->currentUser['perm_' . $resource] ?? '';
        return strpos($permission_string, $action) !== false;
    }

    protected function showAccessDenied()
    {
        $this->title = 'Доступ заборонено';
        $this->render('v_simple_page');
    }
    
    public function notFoundAction()
    {
        http_response_code(404);
        $this->title = '404 - Сторінку не знайдено';
        // Тепер рендеримо наш новий, гарний шаблон
        $this->render('v_404');
    }
}