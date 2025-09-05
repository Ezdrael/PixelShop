<?php
// app/Mvc/Models/Sales.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Sales
{
    private $db;
    public function __construct() { $this->db = DB::getInstance(); }
    
    // Методи для роботи з акціями будуть тут
}