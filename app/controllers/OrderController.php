<?php
// app/controllers/OrderController.php

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php'; // Потрібно для отримання даних кошика
require_once __DIR__ . '/../models/Product.php'; // Потрібно для деталей товарів у замовленні

// Починаємо сесію, якщо вона ще не розпочата
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class OrderController {
    public function checkout() {
        $pageTitle = "Оформлення Замовлення | PixelShop";

        // --- Імітація перевірки авторизації користувача ---
        $isAuthenticated = false; // Змініть на true, щоб імітувати авторизованого користувача

        $savedAddresses = [];
        if ($isAuthenticated) {
            $savedAddresses = [
                ['id' => 1, 'address_line' => 'вул. Свободи, 10, кв. 5', 'city' => 'Київ', 'notes' => 'Під’їзд 2, код 1234'],
                ['id' => 2, 'address_line' => 'пр. Перемоги, 25, офіс 7', 'city' => 'Львів', 'notes' => 'Залишити на рецепції'],
            ];
        }
        // --- Кінець імітації ---

        require_once __DIR__ . '/../views/order/checkout.php';
    }

    // НОВИЙ МЕТОД: Обробка оформлення замовлення
    public function process() {
        // Цей метод буде викликатися через POST-запит з форми оформлення замовлення
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Отримуємо дані з POST-запиту
            $fullName = $_POST['fullName'] ?? null;
            $email = $_POST['email'] ?? null;
            $phone = $_POST['phone'] ?? null;
            $city = $_POST['city'] ?? null;
            $addressLine = $_POST['address'] ?? null;
            $notes = $_POST['notes'] ?? null;
            // $userId = $_SESSION['user_id'] ?? null; // Якщо у вас є авторизація

            // Отримуємо товари з кошика (з сесії)
            $cartItems = Cart::getCart();
            $totalAmount = Cart::getTotalAmount();

            if (empty($cartItems)) {
                $_SESSION['message'] = "Ваш кошик порожній. Будь ласка, додайте товари перед оформленням замовлення.";
                header('Location: ' . BASE_URL . '/cart/index');
                exit;
            }

            if ($fullName && $email && $phone && $city && $addressLine) {
                $orderData = [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => $city,
                    'address_line' => $addressLine,
                    'notes' => $notes,
                    'user_id' => null, // Замініть на реальний user_id, якщо користувач авторизований
                    'total_amount' => $totalAmount
                ];

                $trackingId = Order::saveOrder($orderData, $cartItems);

                if ($trackingId) {
                    Cart::clearCart(); // Очищаємо кошик після успішного оформлення
                    $_SESSION['message'] = "Ваше замовлення успішно оформлено! ID відстеження: <strong>" . htmlspecialchars($trackingId) . "</strong>";
                    header('Location: ' . BASE_URL . '/order/track/' . $trackingId); // Перенаправляємо на сторінку відстеження
                    exit;
                } else {
                    $_SESSION['message'] = "Виникла помилка при оформленні замовлення. Будь ласка, спробуйте ще раз.";
                    header('Location: ' . BASE_URL . '/order/checkout');
                    exit;
                }
            } else {
                $_SESSION['message'] = "Будь ласка, заповніть всі обов'язкові поля.";
                header('Location: ' . BASE_URL . '/order/checkout');
                exit;
            }
        } else {
            $_SESSION['message'] = "Невірний метод запиту для оформлення замовлення.";
            header('Location: ' . BASE_URL . '/order/checkout');
            exit;
        }
    }

    // НОВИЙ МЕТОД: Для відображення списку оформлених замовлень
    public function list() {
        $pageTitle = "Мої Замовлення | PixelShop";

        // Отримуємо замовлення з моделі Order
        // У реальному проекті тут буде фільтрація за user_id
        $orders = Order::getAllOrders();

        require_once __DIR__ . '/../views/order/list.php';
    }

    // НОВИЙ МЕТОД: Для відстеження конкретного замовлення
    public function track($trackingId = null) {
        $pageTitle = "Відстеження Замовлення | PixelShop";
        $order = null;
        $errorMessage = null;

        if ($trackingId) {
            $order = Order::getOrderByTrackingId($trackingId);
            if (!$order) {
                $errorMessage = "Замовлення з ID відстеження '<strong>" . htmlspecialchars($trackingId) . "</strong>' не знайдено. Будь ласка, перевірте правильність ID.";
            }
        } else {
            // Якщо trackingId не надано, можливо, відобразити форму для введення ID
            // Або перенаправити на сторінку зі списком замовлень, якщо користувач авторизований
            // Для простоти, якщо немає ID, відображаємо порожню сторінку відстеження
        }
        require_once __DIR__ . '/../views/order/tracking.php';
    }
}
