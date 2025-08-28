<?php
// app/Mvc/Controllers/Admin/SettingsController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Settings;

class SettingsController extends BaseController
{
    protected $mSettings;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('settings', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mSettings = new Settings();
    }

    public function indexAction()
    {
        $this->title = 'Налаштування сайту';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->hasPermission('settings', 'e')) {
                return $this->showAccessDenied();
            }
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('CSRF Error');
            }

            $faviconFile = $_FILES['favicon'] ?? null;
            $uploadResult = $this->mSettings->handleFaviconUpload($faviconFile);

            if ($uploadResult['success'] === false) {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => $uploadResult['message']];
            } else {
                if ($this->mSettings->update($_POST)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Налаштування успішно збережено.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка при збереженні налаштувань.'];
                }
            }
            
            header('Location: ' . BASE_URL . '/settings');
            exit();
        }

        $settings = $this->mSettings->getAll();
        
        // !! НОВИЙ КОД: Скануємо папку з темами !!
        $themesPath = BASE_PATH . '/public/themes/';
        $themes = array_filter(scandir($themesPath), function($item) use ($themesPath) {
            return is_dir($themesPath . $item) && !in_array($item, ['.', '..']);
        });
        
        $this->render('v_settings', [
            'settings' => $settings,
            'timezones' => \DateTimeZone::listIdentifiers(),
            'themes' => $themes // Передаємо список тем у шаблон
        ]);
    }
}