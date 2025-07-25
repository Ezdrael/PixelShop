<?php
// app/models/Product.php

require_once __DIR__ . '/../config/database.php'; // Підключаємо конфігурацію БД

class Product {
    // Метод для отримання всіх товарів з бази даних, з можливістю фільтрації за назвою категорії
    public static function getAllProducts($categoryName = null) {
        $conn = getDbConnection();
        $products = [];

        // Перевірка на успішне підключення до БД
        if (!$conn) {
            error_log("Помилка: Не вдалося підключитися до бази даних у Product::getAllProducts().");
            return [];
        }

        // Використовуємо JOIN для отримання назви категорії з таблиці `categories`
        // Зверніть увагу: ми вибираємо c.name AS category_name, а не p.category
        $sql = "SELECT p.id, p.name, p.description, p.price, p.quantity, c.name AS category_name, p.image_url
                FROM products p
                JOIN categories c ON p.category_id = c.id";

        if ($categoryName) {
            $sql .= " WHERE c.name = ?"; // Фільтруємо за назвою категорії
        }
        $sql .= " ORDER BY p.id ASC"; // Додаємо сортування за замовчуванням

        $stmt = $conn->prepare($sql);
        
        // Перевірка на помилку підготовки запиту
        if ($stmt === false) {
            error_log("Помилка підготовки запиту getAllProducts: " . $conn->error);
            $conn->close();
            return [];
        }

        if ($categoryName) {
            $stmt->bind_param("s", $categoryName);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Перевірка на помилку виконання запиту або порожній результат
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Додано оператор нульового об'єднання для quantity
                    $row['quantity'] = $row['quantity'] ?? 0;
                    $row['displayPrice'] = number_format($row['price'], 0, ',', ' ') . ' грн';
                    $products[] = $row;
                }
                error_log("Product::getAllProducts() успішно отримано " . count($products) . " товарів.");
            } else {
                error_log("Product::getAllProducts() не знайдено товарів у базі даних.");
            }
        } else {
            error_log("Помилка виконання запиту getAllProducts: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
        return $products;
    }

    // Метод для отримання товару за ID
    public static function getProductById($id) {
        $conn = getDbConnection();
        $product = null;

        if (!$conn) {
            error_log("Помилка: Не вдалося підключитися до бази даних у Product::getProductById().");
            return null;
        }

        // Використовуємо JOIN для отримання назви категорії
        // Додано p.quantity до SELECT
        $sql = "SELECT p.id, p.name, p.description, p.price, p.quantity, c.name AS category_name, p.image_url
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            error_log("Помилка підготовки запиту getProductById: " . $conn->error);
            $conn->close();
            return null;
        }

        $stmt->bind_param("i", $id); // 'i' для цілочисельного параметра
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            // Додано оператор нульового об'єднання для quantity
            $product['quantity'] = $product['quantity'] ?? 0;
            $product['displayPrice'] = number_format($product['price'], 0, ',', ' ') . ' грн';
        } else {
            error_log("Product::getProductById() не знайдено товар з ID: " . $id);
        }

        $stmt->close();
        $conn->close();
        return $product;
    }

    // Метод для отримання всіх унікальних категорій (тепер з таблиці `categories`)
    public static function getUniqueCategories() {
        $conn = getDbConnection();
        $categories = [];

        if (!$conn) {
            error_log("Помилка: Не вдалося підключитися до бази даних у Product::getUniqueCategories().");
            return [];
        }

        $sql = "SELECT name FROM categories ORDER BY name ASC";
        $result = $conn->query($sql);

        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $categories[] = $row['name'];
                }
            } else {
                error_log("Product::getUniqueCategories() не знайдено категорій у базі даних.");
            }
        } else {
            error_log("Помилка виконання запиту getUniqueCategories: " . $conn->error);
        }

        $conn->close();
        return $categories;
    }

    // НОВИЙ МЕТОД: Пошук товарів за запитом
    public static function searchProducts($query) {
        $conn = getDbConnection();
        $products = [];

        if (!$conn) {
            error_log("Помилка: Не вдалося підключитися до бази даних у Product::searchProducts().");
            return [];
        }

        $searchQuery = "%" . $query . "%"; // Додаємо символи % для пошуку за частиною рядка

        // Пошук за назвою товару або описом
        // Додано p.quantity до SELECT
        $sql = "SELECT p.id, p.name, p.description, p.price, p.quantity, c.name AS category_name, p.image_url
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.name LIKE ? OR p.description LIKE ?
                LIMIT 10"; // Обмежуємо кількість результатів для автодоповнення

        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            error_log("Помилка підготовки запиту searchProducts: " . $conn->error);
            $conn->close();
            return [];
        }

        $stmt->bind_param("ss", $searchQuery, $searchQuery); // Прив'язуємо два параметри
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Додано оператор нульового об'єднання для quantity
                    $row['quantity'] = $row['quantity'] ?? 0;
                    $row['displayPrice'] = number_format($row['price'], 0, ',', ' ') . ' грн';
                    $products[] = $row;
                }
            } else {
                error_log("Product::searchProducts() не знайдено товарів за запитом: " . $query);
            }
        } else {
            error_log("Помилка виконання запиту searchProducts: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
        return $products;
    }
}
