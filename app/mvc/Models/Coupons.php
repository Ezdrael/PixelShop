<?php
// app/Mvc/Models/Coupons.php
namespace App\Mvc\Models;

use App\Core\DB;

class Coupons
{
    private $db;
    public function __construct() { $this->db = DB::getInstance(); }
    
    // Методи для роботи з промокодами будуть тут
}