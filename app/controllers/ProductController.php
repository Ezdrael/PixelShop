<?php
// app/controllers/ProductController.php

require_once __DIR__ . '/../models/Product.php'; // Підключаємо модель Product

class ProductController {
    public function list($category = null) { // $category тепер буде назвою категорії (string)
        $pageTitle = "Каталог Товарів | Мій Інтернет-магазин";

        // Отримуємо список товарів, можливо, відфільтрований за категорією
        $products = Product::getAllProducts($category);

        // Отримуємо всі унікальні категорії для відображення фільтрів
        $categories = Product::getUniqueCategories();

        // Передаємо дані у представлення
        require_once __DIR__ . '/../views/products/list.php';
    }

    public function detail($id) {
        $pageTitle = "Деталі Товару | Мій Інтернет-магазин";
        $product = Product::getProductById($id);

        if (!$product) {
            header("HTTP/1.0 404 Not Found");
            echo "404 - Товар не знайдено";
            return;
        }

        require_once __DIR__ . '/../views/products/detail.php';
    }
}
