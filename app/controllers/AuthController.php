<?php
// app/controllers/AuthController.php

// Починаємо сесію, якщо вона ще не розпочата
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/User.php'; // Припускаємо, що у вас є модель User

class AuthController {
    public function login() {
        $pageTitle = "Вхід | PixelShop";
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = User::authenticate($email, $password); // Припускаємо метод authenticate у моделі User

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                // Можна додати інші дані користувача до сесії
                header('Location: ' . BASE_URL . '/'); // Перенаправлення на головну сторінку після успішного входу
                exit();
            } else {
                $errorMessage = "Невірний email або пароль.";
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        $pageTitle = "Реєстрація | PixelShop";
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $errorMessage = '';
        $successMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
                $errorMessage = "Будь ласка, заповніть усі поля.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Будь ласка, введіть дійсний email.";
            } elseif ($password !== $confirmPassword) {
                $errorMessage = "Паролі не співпадають.";
            } elseif (User::findByEmail($email)) { // Припускаємо метод findByEmail у моделі User
                $errorMessage = "Користувач з таким email вже існує.";
            } else {
                // Хешуємо пароль перед збереженням
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userId = User::create($fullName, $email, $hashedPassword); // Припускаємо метод create у моделі User

                if ($userId) {
                    $successMessage = "Реєстрація успішна! Тепер ви можете увійти.";
                    // Можна автоматично входити користувача або перенаправляти на сторінку входу
                    // header('Location: ' . BASE_URL . '/auth/login');
                    // exit();
                } else {
                    $errorMessage = "Виникла помилка під час реєстрації. Будь ласка, спробуйте ще раз.";
                }
            }
        }

        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        session_unset(); // Видаляємо всі змінні сесії
        session_destroy(); // Знищуємо сесію
        header('Location: ' . BASE_URL . '/'); // Перенаправлення на головну сторінку
        exit();
    }
}
