<?php
// ===================================================================
// Файл: mvc/c_main.php 🕰️
// Розміщення: /mvc/c_main.php
// Призначення: Контролер для головної сторінки та сторінки "Про нас".
// ===================================================================

class C_Main extends C_Base {
    
    public function indexAction() {
        $this->title = 'Dashboard';
        $content = "<p>Вітаємо в адмінпанелі! Оберіть розділ в меню ліворуч.</p>";
        $this->render('v_simple_page', ['page_content' => $content]);
    }
    
    public function aboutAction() {
        $this->title = 'Про систему';
        $content = "<p>Це проста MVC система, створена для демонстрації.</p>";
        $this->render('v_simple_page', ['page_content' => $content]);
    }
    
    public function notFoundAction() {
        header("HTTP/1.0 404 Not Found");
        $this->title = '404 - Сторінку не знайдено';
        $content = "<p>На жаль, сторінку, яку ви шукали, не існує.</p>";
        $this->render('v_simple_page', ['page_content' => $content]);
    }
}