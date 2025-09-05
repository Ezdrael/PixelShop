<?php
// app/Mvc/Models/BonusPoints.php
namespace App\Mvc\Models;

use App\Core\DB;

class BonusPoints
{
    private $db;
    public function __construct() { $this->db = DB::getInstance(); }
    
    // Методи для роботи з бонусними балами будуть тут
}