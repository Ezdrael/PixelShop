<?php
// app/models/Cart.php

// Починаємо сесію, якщо вона ще не розпочата
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Cart {
    /**
     * Ініціалізує кошик у сесії, якщо він ще не існує.
     */
    private static function initCart() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Додає товар до кошика або оновлює його кількість.
     *
     * @param array $product Масив з даними товару (повинен містити 'id', 'name', 'price', 'image_url', 'availableQuantity').
     * @param int $quantity Кількість товару для додавання.
     * @return bool True, якщо товар успішно додано/оновлено, false у разі помилки (наприклад, недостатньо на складі).
     */
    public static function addItem($product, $quantity) {
        self::initCart();

        $productId = $product['id'];
        $currentQuantityInCart = $_SESSION['cart'][$productId]['quantity'] ?? 0;
        $newQuantity = $currentQuantityInCart + $quantity;

        // Перевіряємо, чи доступна кількість на складі
        if ($newQuantity > ($product['availableQuantity'] ?? 0)) {
            $_SESSION['message'] = "На складі доступно лише " . ($product['availableQuantity'] ?? 0) . " одиниць товару '" . htmlspecialchars($product['name']) . "'.";
            return false;
        }

        $_SESSION['cart'][$productId] = [
            'id' => $productId,
            'name' => $product['name'],
            'image_url' => $product['image_url'],
            'price' => $product['price'],
            'quantity' => $newQuantity,
            'availableQuantity' => $product['availableQuantity'] // Зберігаємо доступну кількість
        ];
        return true;
    }

    /**
     * Оновлює кількість конкретного товару в кошику.
     *
     * @param int $productId ID товару.
     * @param int $newQuantity Нова кількість товару.
     */
    public static function updateItemQuantity($productId, $newQuantity) {
        self::initCart();

        if ($newQuantity <= 0) {
            self::removeItem($productId);
            return;
        }

        // Отримуємо актуальну інформацію про товар, щоб перевірити доступну кількість
        require_once __DIR__ . '/Product.php';
        $product = Product::getProductById($productId);

        if ($product) {
            if ($newQuantity > ($product['quantity'] ?? 0)) {
                // Якщо запитувана кількість більша за доступну, встановлюємо максимальну
                $_SESSION['cart'][$productId]['quantity'] = ($product['quantity'] ?? 0);
                $_SESSION['message'] = "На складі доступно лише " . ($product['quantity'] ?? 0) . " одиниць товару '" . htmlspecialchars($product['name']) . "'.";
            } else {
                $_SESSION['cart'][$productId]['quantity'] = $newQuantity;
                $_SESSION['message'] = "Кількість товару '" . htmlspecialchars($product['name']) . "' оновлено.";
            }
        } else {
            // Якщо товар не знайдено, видаляємо його з кошика
            self::removeItem($productId);
            $_SESSION['message'] = "Товар не знайдено, видалено з кошика.";
        }
    }

    /**
     * Видаляє товар з кошика.
     *
     * @param int $productId ID товару.
     */
    public static function removeItem($productId) {
        self::initCart();
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['message'] = "Товар видалено з кошика.";
        }
    }

    /**
     * Очищає весь кошик.
     */
    public static function clearCart() {
        unset($_SESSION['cart']);
        $_SESSION['cart'] = []; // Переініціалізуємо для чистоти
    }

    /**
     * Повертає вміст кошика.
     *
     * @return array Масив товарів у кошику.
     */
    public static function getCart() {
        self::initCart();
        return $_SESSION['cart'];
    }

    /**
     * Розраховує загальну суму товарів у кошику.
     *
     * @return float Загальна сума.
     */
    public static function getTotalAmount() {
        self::initCart();
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Повертає відформатовану загальну суму товарів у кошику.
     *
     * @return string Відформатована загальна сума.
     */
    public static function getDisplayTotalAmount() {
        return self::formatPrice(self::getTotalAmount());
    }

    /**
     * Форматує ціну для відображення.
     *
     * @param float $price Ціна.
     * @return string Відформатована ціна.
     */
    public static function formatPrice($price) {
        return number_format($price, 0, ',', ' ') . ' грн';
    }
}
