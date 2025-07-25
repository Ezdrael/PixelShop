<?php
// app/models/Order.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php'; // Потрібно для оновлення кількості товару

class Order {
    /**
     * Генерує унікальний буквено-цифровий ID для відстеження.
     * @return string
     */
    private static function generateTrackingId() {
        // Генеруємо випадковий рядок з 16 байт і перетворюємо його в шістнадцятковий формат (32 символи)
        // Додаємо кілька символів з uniqid для додаткової унікальності та довжини
        return uniqid(bin2hex(random_bytes(8)), true);
    }

    /**
     * Зберігає нове замовлення та його позиції в базу даних.
     *
     * @param array $orderData Дані замовлення (full_name, email, phone, city, address_line, notes, user_id, total_amount)
     * @param array $cartItems Масив товарів у кошику (id, quantity, price)
     * @return string|false Унікальний ID для відстеження замовлення або false у разі помилки
     */
    public static function saveOrder($orderData, $cartItems) {
        $conn = getDbConnection();
        $conn->begin_transaction(); // Починаємо транзакцію для забезпечення цілісності даних

        try {
            // 1. Вставка даних у таблицю `orders`
            $userId = isset($orderData['user_id']) ? $orderData['user_id'] : null;
            $fullName = $orderData['full_name'];
            $email = $orderData['email'];
            $phone = $orderData['phone'];
            $city = $orderData['city'];
            $addressLine = $orderData['address_line'];
            $notes = $orderData['notes'];
            $trackingId = self::generateTrackingId(); // Генеруємо унікальний ID для відстеження
            $totalAmount = $orderData['total_amount']; // Загальна сума вже передається з контролера
            $status = 'pending'; // Початковий статус замовлення

            $stmt = $conn->prepare("INSERT INTO `orders` (`tracking_id`, `user_id`, `full_name`, `email`, `phone`, `city`, `address_line`, `notes`, `total_amount`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                throw new Exception("Помилка підготовки запиту для orders: " . $conn->error);
            }

            // Прив'язка параметрів (додано tracking_id)
            $stmt->bind_param("sissssssds", $trackingId, $userId, $fullName, $email, $phone, $city, $addressLine, $notes, $totalAmount, $status);
            $stmt->execute();

            $orderId = $conn->insert_id; // Отримуємо внутрішній ID щойно вставленого замовлення
            $stmt->close();

            // 2. Вставка даних у таблицю `order_items` та оновлення кількості товару
            $stmt_item = $conn->prepare("INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `price`) VALUES (?, ?, ?, ?)");
            if ($stmt_item === false) {
                throw new Exception("Помилка підготовки запиту для order_items: " . $conn->error);
            }

            $stmt_product_update = $conn->prepare("UPDATE `products` SET `quantity` = `quantity` - ? WHERE `id` = ? AND `quantity` >= ?");
            if ($stmt_product_update === false) {
                throw new Exception("Помилка підготовки запиту для оновлення кількості товару: " . $conn->error);
            }

            foreach ($cartItems as $item) {
                // Вставка позиції замовлення
                $stmt_item->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
                $stmt_item->execute();

                // Оновлення кількості товару на складі
                $stmt_product_update->bind_param("iii", $item['quantity'], $item['id'], $item['quantity']);
                $stmt_product_update->execute();

                if ($stmt_product_update->affected_rows === 0) {
                    // Якщо кількість не оновилася, це означає, що товару недостатньо
                    throw new Exception("Недостатня кількість товару з ID: " . $item['id']);
                }
            }
            $stmt_item->close();
            $stmt_product_update->close();

            $conn->commit(); // Завершуємо транзакцію
            return $trackingId; // Повертаємо унікальний ID для відстеження

        } catch (Exception $e) {
            $conn->rollback(); // Відкочуємо транзакцію у разі помилки
            error_log("Помилка збереження замовлення: " . $e->getMessage());
            return false;
        } finally {
            $conn->close();
        }
    }

    /**
     * Отримує деталі замовлення за його внутрішнім ID.
     * Не використовується для відстеження користувачем, лише для внутрішніх потреб.
     *
     * @param int $orderId Внутрішній ID замовлення.
     * @return array|null Масив з деталями замовлення та його позиціями, або null, якщо замовлення не знайдено.
     */
    public static function getOrderById($orderId) {
        $conn = getDbConnection();
        $order = null;

        // Отримати основну інформацію про замовлення
        $stmt_order = $conn->prepare("SELECT id, tracking_id, user_id, full_name, email, phone, city, address_line, notes, total_amount, status, created_at FROM `orders` WHERE id = ?");
        if ($stmt_order === false) {
            error_log("Помилка підготовки запиту для отримання замовлення: " . $conn->error);
            $conn->close();
            return null;
        }
        $stmt_order->bind_param("i", $orderId);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();

        if ($result_order && $result_order->num_rows > 0) {
            $order = $result_order->fetch_assoc();
            $order['displayTotalAmount'] = number_format($order['total_amount'], 0, ',', ' ') . ' грн';
            $order['items'] = [];

            // Отримати позиції замовлення
            $stmt_items = $conn->prepare("
                SELECT oi.product_id, oi.quantity, oi.price, p.name AS product_name, p.image_url
                FROM `order_items` oi
                JOIN `products` p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            if ($stmt_items === false) {
                error_log("Помилка підготовки запиту для отримання позицій замовлення: " . $conn->error);
                $conn->close();
                return null;
            }
            $stmt_items->bind_param("i", $orderId);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();

            if ($result_items && $result_items->num_rows > 0) {
                while ($row_item = $result_items->fetch_assoc()) {
                    $row_item['displayPrice'] = number_format($row_item['price'], 0, ',', ' ') . ' грн';
                    $order['items'][] = $row_item;
                }
            }
            $stmt_items->close();
        }

        $stmt_order->close();
        $conn->close();
        return $order;
    }

    /**
     * Отримує деталі замовлення за його унікальним ID відстеження.
     *
     * @param string $trackingId Унікальний ID для відстеження замовлення.
     * @return array|null Масив з деталями замовлення та його позиціями, або null, якщо замовлення не знайдено.
     */
    public static function getOrderByTrackingId($trackingId) {
        $conn = getDbConnection();
        $order = null;

        // Отримати основну інформацію про замовлення
        $stmt_order = $conn->prepare("SELECT id, tracking_id, user_id, full_name, email, phone, city, address_line, notes, total_amount, status, created_at FROM `orders` WHERE tracking_id = ?");
        if ($stmt_order === false) {
            error_log("Помилка підготовки запиту для отримання замовлення за tracking_id: " . $conn->error);
            $conn->close();
            return null;
        }
        $stmt_order->bind_param("s", $trackingId);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();

        if ($result_order && $result_order->num_rows > 0) {
            $order = $result_order->fetch_assoc();
            $order['displayTotalAmount'] = number_format($order['total_amount'], 0, ',', ' ') . ' грн';
            $order['items'] = [];

            // Отримати позиції замовлення за внутрішнім order_id
            $stmt_items = $conn->prepare("
                SELECT oi.product_id, oi.quantity, oi.price, p.name AS product_name, p.image_url
                FROM `order_items` oi
                JOIN `products` p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            if ($stmt_items === false) {
                error_log("Помилка підготовки запиту для отримання позицій замовлення: " . $conn->error);
                $conn->close();
                return null;
            }
            $stmt_items->bind_param("i", $order['id']); // Використовуємо внутрішній ID замовлення
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();

            if ($result_items && $result_items->num_rows > 0) {
                while ($row_item = $result_items->fetch_assoc()) {
                    $row_item['displayPrice'] = number_format($row_item['price'], 0, ',', ' ') . ' грн';
                    $order['items'][] = $row_item;
                }
            }
            $stmt_items->close();
        }

        $stmt_order->close();
        $conn->close();
        return $order;
    }

    /**
     * Отримує всі замовлення з бази даних.
     * У реальному додатку тут може бути фільтрація за користувачем, пагінація тощо.
     *
     * @return array Масив з усіма замовленнями.
     */
    public static function getAllOrders() {
        $conn = getDbConnection();
        $orders = [];

        $sql = "SELECT id, tracking_id, user_id, full_name, email, phone, city, address_line, notes, total_amount, status, created_at FROM `orders` ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($order = $result->fetch_assoc()) {
                $order['displayTotalAmount'] = number_format($order['total_amount'], 0, ',', ' ') . ' грн';
                // Можна також отримати позиції для кожного замовлення тут, якщо потрібно відображати їх у списку
                // Або залишити це для детального перегляду (getOrderByTrackingId)
                $orders[] = $order;
            }
        }

        $conn->close();
        return $orders;
    }
}
