<?php
// app/Mvc/Models/Main.php
namespace App\Mvc\Models;

use App\Core\DB;

/**
 * Клас-заглушка для моделі Main.
 * Створений, оскільки на нього посилається MainController.
 */
class Main
{
    private $db;

    public function __construct() 
    {
        $this->db = DB::getInstance();
    }
}