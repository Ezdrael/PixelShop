<?php
// app/config/database.php

function getDbConnection() {
    // Налаштування підключення до бази даних
    $servername = "localhost"; // Зазвичай "localhost" для XAMPP
    $username = "root";        // Ім'я користувача бази даних (за замовчуванням "root" для XAMPP)
    $password = "";            // Пароль бази даних (за замовчуванням порожній для XAMPP)
    $dbname = "pixelshop_db";  // Назва вашої бази даних

    // Створення нового підключення
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Перевірка підключення
    if ($conn->connect_error) {
        // У випадку помилки підключення, виводимо повідомлення і зупиняємо виконання
        die("Помилка підключення до бази даних: " . $conn->connect_error);
    }

    // Встановлення кодування символів для коректної роботи з українською мовою
    $conn->set_charset("utf8mb4");

    return $conn;
}
?>
