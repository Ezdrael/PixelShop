<?php
// ===================================================================
// Файл: mvc/c_post.php 🕰️
// Розміщення: /mvc/c_post.php
// Призначення: Контролер для демонстраційної сторінки постів.
// ===================================================================
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Roles; 
use App\Mvc\Models\Post;

class PostController extends BaseController  {

    public function showAction() {
        $this->title = 'Перегляд поста';
        $content = "<p>Це PostController, метод showAction.</p>" . 
                   "<p>ID поста: " . htmlspecialchars($this->params['id']) . "</p>";
        $this->render('v_simple_page', ['page_content' => $content]);
    }
}