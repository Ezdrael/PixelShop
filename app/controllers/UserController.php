<?php
// app/controllers/UserController.php

require_once __DIR__ . '/../models/User.php'; // Підключаємо модель User

class UserController {
    /**
     * Обробляє запити на реєстрацію користувача.
     * Очікує POST-запит з даними JSON.
     */
    public function register() {
        header('Content-Type: application/json'); // Встановлюємо Content-Type для JSON-відповіді

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Отримуємо JSON-дані з тіла запиту
            $input = json_decode(file_get_contents('php://input'), true);

            // --- ТИМЧАСОВИЙ КОД ДЛЯ НАЛАГОДЖЕННЯ ---
            error_log("Отримані дані для реєстрації: " . print_r($input, true));
            // --- КІНЕЦЬ ТИМЧАСОВОГО КОДУ ---

            $name = $input['name'] ?? '';
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            $confirmPassword = $input['confirmPassword'] ?? '';

            // Проста валідація
            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                echo json_encode(['success' => false, 'message' => 'Будь ласка, заповніть усі обов\'язкові поля.']);
                return;
            }

            if ($password !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'Паролі не співпадають!']);
                return;
            }

            // Викликаємо метод реєстрації з моделі User
            $result = User::registerUser($name, $email, $password);

            // --- ТИМЧАСОВИЙ КОД ДЛЯ НАЛАГОДЖЕННЯ ---
            error_log("Результат реєстрації: " . print_r($result, true));
            // --- КІНЕЦЬ ТИМЧАСОВОГО КОДУ ---

            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Реєстрація успішна! Тепер ви можете увійти.']);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
        } else {
            // Якщо це не POST-запит, повертаємо помилку
            header("HTTP/1.0 405 Method Not Allowed");
            echo json_encode(['success' => false, 'message' => 'Метод не дозволено.']);
        }
    }

    /**
     * Обробляє запити на вхід користувача.
     * Очікує POST-запит з даними JSON.
     */
    public function login() {
        header('Content-Type: application/json'); // Встановлюємо Content-Type для JSON-відповіді

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Отримуємо JSON-дані з тіла запиту
            $input = json_decode(file_get_contents('php://input'), true);

            // --- ТИМЧАСОВИЙ КОД ДЛЯ НАЛАГОДЖЕННЯ ---
            error_log("Отримані дані для входу: " . print_r($input, true));
            // --- КІНЕЦЬ ТИМЧАСОВОГО КОДУ ---

            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';

            // Проста валідація
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Будь ласка, введіть email та пароль.']);
                return;
            }

            // Викликаємо метод автентифікації з моделі User
            $user = User::authenticateUser($email, $password);

            // --- ТИМЧАСОВИЙ КОД ДЛЯ НАЛАГОДЖЕННЯ ---
            error_log("Результат входу: " . print_r($user, true));
            // --- КІНЕЦЬ ТИМЧАСОВОГО КОДУ ---

            if ($user) {
                // Успішний вхід. У реальному додатку тут зазвичай створюється сесія або JWT-токен.
                // Для демонстрації, ми просто повертаємо ім'я користувача.
                echo json_encode(['success' => true, 'message' => 'Вхід успішний!', 'userName' => $user['username']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Невірний email або пароль.']);
            }
        } else {
            // Якщо це не POST-запит, повертаємо помилку
            header("HTTP/1.0 405 Method Not Allowed");
            echo json_encode(['success' => false, 'message' => 'Метод не дозволено.']);
        }
    }
}
