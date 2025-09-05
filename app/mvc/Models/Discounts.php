<?php
// app/Mvc/Models/Discounts.php
namespace App\Mvc\Models;

use App\Core\DB;

class Discounts
{
    private $db;
    public function __construct() { $this->db = DB::getInstance(); }
    
    // Методи для роботи зі знижками будуть тут
}