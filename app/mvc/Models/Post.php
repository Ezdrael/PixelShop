<?php
// app/Mvc/Models/Post.php
namespace App\Mvc\Models;

use App\Core\DB;

/**
 * Клас-заглушка для моделі Post.
 * Створений, оскільки на нього посилається PostController.
 */
class Post 
{
    private $db;

    public function __construct() 
    {
        $this->db = DB::getInstance();
    }
}