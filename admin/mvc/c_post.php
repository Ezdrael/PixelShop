<?php
// ===================================================================
// Файл: mvc/c_post.php 🕰️
// Розміщення: /mvc/c_post.php
// Призначення: Контролер для демонстраційної сторінки постів.
// ===================================================================

class C_Post extends C_Base {
    
    public function showAction() {
        $this->title = 'Перегляд поста';
        $content = "<p>Це C_Post, метод showAction.</p>" . 
                   "<p>ID поста: " . htmlspecialchars($this->params['id']) . "</p>";
        $this->render('v_simple_page', ['page_content' => $content]);
    }
}