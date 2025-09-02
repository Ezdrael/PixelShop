<?php
// app/Mvc/Controllers/Admin/AccountController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Account; // Майбутня модель

class AccountController extends BaseController
{
    protected $mAccount;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->mAccount = new Account();
    }

    /**
     * Відображає сторінку налаштувань акаунту.
     */
    public function settingsAction()
    {
        $this->title = 'Налаштування акаунту';

        // Логіка для збереження форми буде тут у майбутньому
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ...
            // Наразі просто показуємо повідомлення
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Налаштування будуть збережені тут.'];
            header('Location: ' . BASE_URL . '/account/settings');
            exit();
        }

        // Передаємо дані поточного користувача у вигляд
        $this->render('v_account_settings', [
            'user' => $this->currentUser
        ]);
    }
}