<?php
// app/controllers/CartController.php

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php'; // Потрібно для отримання деталей товару

// Починаємо сесію, якщо вона ще не розпочата
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class CartController {
    public function index() {
        $pageTitle = "Ваш Кошик | PixelShop";
        $cartItems = Cart::getCart();
        $displayTotalAmount = Cart::getDisplayTotalAmount();

        // Передаємо дані у представлення
        require_once __DIR__ . '/../views/cart/index.php';
    }

    public function add() {
        // Цей метод буде викликатися через AJAX POST-запит з main.js
        header('Content-Type: application/json');

        $response = ['success' => false, 'message' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $productId = $data['id'] ?? null;
            $quantity = $data['quantity'] ?? 1;

            if ($productId) {
                $product = Product::getProductById($productId); // Отримуємо повні дані товару з БД
                if ($product) {
                    // Додаємо доступну кількість до даних товару, які передаємо в модель кошика
                    $product['availableQuantity'] = $product['quantity']; // 'quantity' з БД - це доступна кількість
                    $added = Cart::addItem($product, $quantity);
                    if ($added) {
                        $response['success'] = true;
                        $response['message'] = "Товар успішно додано до кошика.";
                    } else {
                        $response['success'] = false;
                        $response['message'] = $_SESSION['message'] ?? "Не вдалося додати товар до кошика.";
                        unset($_SESSION['message']); // Очистити повідомлення після використання
                    }
                } else {
                    $response['message'] = "Товар не знайдено.";
                }
            } else {
                $response['message'] = "ID товару не надано.";
            }
        } else {
            $response['message'] = "Невірний метод запиту.";
        }

        echo json_encode($response);
    }

    public function update() {
        // Цей метод буде викликатися через POST-запит з форми на сторінці кошика
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? null;

            if ($productId && $quantity !== null) {
                Cart::updateItemQuantity($productId, $quantity);
                // Повідомлення про оновлення буде встановлено в моделі Cart
            }
        }
        header('Location: ' . BASE_URL . '/cart/index'); // Перенаправляємо назад на сторінку кошика
        exit;
    }

    public function remove() {
        // Цей метод буде викликатися через POST-запит з форми на сторінці кошика
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            if ($productId) {
                Cart::removeItem($productId);
                $_SESSION['message'] = "Товар видалено з кошика.";
            }
        }
        header('Location: ' . BASE_URL . '/cart/index');
        exit;
    }

    public function clear() {
        // Цей метод буде викликатися через POST-запит з форми на сторінці кошика
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Cart::clearCart();
            $_SESSION['message'] = "Кошик очищено.";
        }
        header('Location: ' . BASE_URL . '/cart/index');
        exit;
    }

    // Метод для отримання вмісту кошика у форматі JSON (для AJAX)
    public function getJsonCart() {
        header('Content-Type: application/json');
        $cartItems = Cart::getCart();
        $totalAmount = Cart::getTotalAmount();

        // Додаємо відформатовану ціну для кожного товару та загальну суму
        $formattedCartItems = [];
        foreach ($cartItems as $item) {
            $item['displayPrice'] = Cart::formatPrice($item['price']);
            $item['displayItemTotal'] = Cart::formatPrice($item['price'] * $item['quantity']);
            $formattedCartItems[] = $item;
        }

        echo json_encode([
            'items' => $formattedCartItems,
            'totalAmount' => $totalAmount,
            'displayTotalAmount' => Cart::getDisplayTotalAmount()
        ]);
    }
}
