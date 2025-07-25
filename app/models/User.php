<?php
// app/models/User.php

require_once __DIR__ . '/../config/database.php'; // Підключаємо конфігурацію БД

class User {
    /**
     * Реєструє нового користувача в системі.
     *
     * @param string $username Ім'я користувача.
     * @param string $email Електронна пошта користувача.
     * @param string $password Пароль користувача (буде хешований).
     * @return array Масив з результатом: ['success' => bool, 'message' => string]
     */
    public static function registerUser($username, $email, $password) {
        $conn = getDbConnection();

        // Перевірка, чи користувач з таким email або ім'ям вже існує
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        if ($stmt === false) {
            $error = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => 'Помилка підготовки запиту перевірки користувача: ' . $error];
        }
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result(); // Зберігаємо результат для перевірки num_rows
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Користувач з таким email або ім\'ям вже існує.'];
        }
        $stmt->close();

        // Хешування пароля для безпечного зберігання
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Вставка нового користувача в базу даних
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        if ($stmt === false) {
            $error = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => 'Помилка підготовки запиту реєстрації: ' . $error];
        }
        $stmt->bind_param("sss", $username, $email, $passwordHash);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true];
        } else {
            $error = $conn->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Помилка реєстрації: ' . $error];
        }
    }

    /**
     * Автентифікує користувача за email та паролем.
     *
     * @param string $email Електронна пошта користувача.
     * @param string $password Пароль користувача.
     * @return array|null Масив з даними користувача (без хешу пароля) у разі успіху, або null у разі невдачі.
     */
    public static function authenticateUser($email, $password) {
        $conn = getDbConnection();
        $user = null;

        $stmt = $conn->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ?");
        if ($stmt === false) {
            $error = $conn->error;
            $conn->close();
            error_log("Помилка підготовки запиту автентифікації: " . $error);
            return null;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Перевірка хешованого пароля
            if (password_verify($password, $row['password_hash'])) {
                // Видаляємо хеш пароля перед поверненням даних користувача
                unset($row['password_hash']);
                $user = $row;
            }
        }

        $stmt->close();
        $conn->close();
        return $user;
    }
}
