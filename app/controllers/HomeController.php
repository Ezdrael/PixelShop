<?php
// app/controllers/HomeController.php

require_once __DIR__ . '/../models/Product.php'; // Підключаємо модель Product

class HomeController {
    public function index() {
        $pageTitle = "Головна Сторінка | Мій Інтернет-магазин";
        $heroTitle = "Ласкаво просимо до нашого магазину!";
        $heroLeadText = "Знайдіть найкращі товари за чудовими цінами.";

        // Отримуємо рекомендовані товари з моделі
        // Можна додати логіку для вибору лише "рекомендованих" товарів,
        // наприклад, за спеціальним полем у БД або просто перші кілька.
        // Для простоти, візьмемо всі товари і виберемо перші 3.
        $allProducts = Product::getAllProducts();
        $featuredProducts = array_slice($allProducts, 0, 3); // Беремо перші 3 товари

        // Завантажуємо представлення
        // ob_start() і require_once ... default.php залишаються в home/index.php
        require_once __DIR__ . '/../views/home/index.php';
    }
}
