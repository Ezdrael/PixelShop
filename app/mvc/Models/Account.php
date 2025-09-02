<?php
// app/Mvc/Models/Account.php
namespace App\Mvc\Models;

use App\Core\DB;

class Account
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    // В майбутньому тут будуть методи для оновлення профілю,
    // зміни пароля та збереження налаштувань інтерфейсу.
    // public function updateProfile($userId, $data) { ... }
    // public function changePassword($userId, $newPassword) { ... }
}