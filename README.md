# PixelShop
PixelShop – це простий, але функціональний інтернет-магазин, розроблений на PHP, який демонструє базові можливості електронної комерції, такі як перегляд товарів, кошик, оформлення замовлення та відстеження.

## Зміст
- [Про проєкт](#про-проєкт)
- [Функціональні можливості](#функціональні-можливості)
- [Технології](#технології)
- [Встановлення](#встановлення)
- [Використання](#використання)
- [Структура проєкту](#структура-проєкту)
- [Ліцензія](#ліцензія)
- [Контакти](#контакти)

## Про проєкт
Цей проєкт є демонстрацією створення веб-застосунку для електронної комерції з використанням PHP для бекенду та Bootstrap для фронтенду. Він включає основні функції, необхідні для роботи інтернет-магазину, такі як управління товарами, кошиком та замовленнями.

## Функціональні можливості
- Головна сторінка: Відображення рекомендованих товарів та загальної інформації.

- Каталог товарів: Перегляд усіх товарів з можливістю фільтрації за категоріями, сортування та пагінації.

- Деталі товару: Детальний опис кожного товару, зображення та можливість додавання до кошика.

- Кошик: Додавання, оновлення кількості та видалення товарів з кошика.

- Оформлення замовлення: Форма для введення даних доставки та підтвердження замовлення.

- Відстеження замовлень: Можливість перегляду статусу замовлення за унікальним ID.

- Система авторизації/реєстрації: Вхід та реєстрація користувачів (імітація для демонстрації).

- Адаптивний дизайн: Оптимізовано для перегляду на різних пристроях (десктоп, планшет, мобільний).

- Анімації: Використання Animate.css для покращення користувацького досвіду.

## Технології
- Бекенд:

- - PHP 8.x

- - MySQL (або сумісна БД)

- Фронтенд:

- - HTML5

- - CSS3 (з використанням style.css та Bootstrap)

- - JavaScript (з використанням main.js, product_list.js, utils.js)

- - Bootstrap 5.3

- - Font Awesome 6.5.2

- - Google Fonts (Montserrat, Lobster)

- - Animate.css

## Встановлення
Для запуску проєкту вам знадобиться веб-сервер (наприклад, Apache або Nginx) з підтримкою PHP та база даних MySQL. Рекомендується використовувати XAMPP, WAMP або Docker для локальної розробки.

### 1. Клонування репозиторію
```
git clone https://github.com/Ezdrzael/PixelShop.git
cd PixelShop
```

### 2. Налаштування бази даних
- Створіть базу даних MySQL (наприклад, `pixelshop_db`).

- Імпортуйте структуру таблиць та початкові дані з файлу `database.sql`, який, ймовірно, знаходиться у вашому проєкті (або створіть його, якщо його немає, на основі ваших моделей).

Приклад `database.sql` (якщо його немає, вам потрібно буде створити його на основі ваших моделей):
```
-- Таблиця для категорій товарів
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE
);

-- Таблиця для товарів
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `quantity` INT DEFAULT 0,
    `category_id` INT,
    `image_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
);

-- Таблиця для користувачів
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблиця для замовлень
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tracking_id` VARCHAR(255) NOT NULL UNIQUE,
    `user_id` INT,
    `full_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255),
    `phone` VARCHAR(20),
    `city` VARCHAR(255) NOT NULL,
    `address_line` VARCHAR(255) NOT NULL,
    `notes` TEXT,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Таблиця для позицій замовлення
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
);

-- Додавання початкових даних (приклад)
INSERT INTO `categories` (`name`) VALUES
('Смартфони'),
('Ноутбуки'),
('Аксесуари');

INSERT INTO `products` (`name`, `description`, `price`, `quantity`, `category_id`, `image_url`) VALUES
('Смартфон X1', 'Потужний смартфон з чудовою камерою.', 15000.00, 50, 1, 'https://placehold.co/400x400/007bff/ffffff?text=Smartphone+X1'),
('Ноутбук ProBook', 'Високопродуктивний ноутбук для роботи та ігор.', 25000.00, 30, 2, 'https://placehold.co/400x400/28a745/ffffff?text=Laptop+ProBook'),
('Бездротові навушники', 'Комфортні навушники з чудовим звуком.', 2500.00, 100, 3, 'https://placehold.co/400x400/ffc107/000000?text=Headphones'),
('Смартфон Z Pro', 'Флагманський смартфон з передовими технологіями.', 22000.00, 40, 1, 'https://placehold.co/400x400/dc3545/ffffff?text=Smartphone+Z+Pro'),
('Ігровий ноутбук Legion', 'Ноутбук для справжніх геймерів.', 35000.00, 20, 2, 'https://placehold.co/400x400/6f42c1/ffffff?text=Gaming+Laptop'),
('Портативний зарядний пристрій', 'Потужний павербанк для ваших пристроїв.', 800.00, 150, 3, 'https://placehold.co/400x400/fd7e14/ffffff?text=Power+Bank');
```
### 3. Налаштування конфігурації
Відкрийте файл `app/config/database.php`.

Оновіть дані для підключення до вашої бази даних (ім'я користувача, пароль, назва БД).
```
<?php
// app/config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ваше ім'я користувача БД
define('DB_PASS', '');     // Ваш пароль БД
define('DB_NAME', 'pixelshop_db'); // Назва вашої БД

function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Помилка підключення до бази даних: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4"); // Встановлюємо кодування UTF-8
    return $conn;
}
?>
```
Відкрийте файл `public/index.php` та переконайтеся, що BASE_URL налаштовано правильно відповідно до вашого середовища.
```
<?php
// public/index.php

// ... (інший код) ...

// Визначення базового URL для коректної роботи посилань
// Встановіть це значення відповідно до вашої конфігурації сервера.
// Наприклад, якщо ваш проєкт доступний за адресою http://localhost/pixelshop/public,
// то BASE_URL має бути '/pixelshop/public'.
// Якщо ваш проєкт доступний за адресою http://localhost/, то BASE_URL має бути ''.
define('BASE_URL', '/pixelshop/public');

// ... (інший код) ...
?>
```
### 4. Розміщення файлів на веб-сервері
Розмістіть вміст папки PixelShop на вашому веб-сервері. Переконайтеся, що коренева папка веб-сервера вказує на папку public всередині PixelShop.

Для Apache (XAMPP): Скопіюйте вміст PixelShop до htdocs (наприклад, `C:\xampp\htdocs\pixelshop`). Тоді ваш проєкт буде доступний за адресою `http://localhost/pixelshop/public`.

## Використання
Після успішного встановлення ви можете відкрити проєкт у вашому браузері за налаштованим `BASE_URL`.

- Перегляд товарів: Перейдіть до `/product/list`

- Кошик: Перейдіть до `/cart/index`

- Відстеження замовлень: Перейдіть до `/order/track`

## Структура проєкту
```
PixelShop/
├── app/
│   ├── config/
│   │   └── database.php        # Конфігурація підключення до бази даних
│   ├── controllers/
│   │   ├── AuthController.php  # Контролер для автентифікації користувачів (вхід, реєстрація)
│   │   ├── CartController.php  # Контролер для управління кошиком
│   │   ├── HomeController.php  # Контролер для головної сторінки
│   │   ├── OrderController.php # Контролер для управління замовленнями
│   │   ├── ProductController.php # Контролер для управління товарами
│   │   └── UserController.php  # Контролер для управління користувачами (AJAX-методи)
│   ├── models/
│   │   ├── Order.php           # Модель для роботи з замовленнями
│   │   ├── Product.php         # Модель для роботи з товарами
│   │   └── User.php            # Модель для роботи з користувачами
│   └── views/
│       ├── auth/
│       │   ├── login.php       # Представлення сторінки входу
│       │   └── register.php    # Представлення сторінки реєстрації
│       ├── cart/
│       │   └── index.php       # Представлення сторінки кошика
│       ├── home/
│       │   └── index.php       # Представлення головної сторінки
│       ├── layouts/
│       │   └── default.php     # Основний шаблон макету сторінок
│       ├── order/
│       │   ├── checkout.php    # Представлення сторінки оформлення замовлення
│       │   ├── success.php     # Представлення сторінки успішного оформлення замовлення
│       │   ├── track_detail.php # Представлення деталей відстеження замовлення
│       │   └── track_list.php  # Представлення списку відстеження замовлень
│       ├── partials/
│       │   ├── footer.php      # Частина шаблону: футер
│       │   └── header.php      # Частина шаблону: хедер
│       └── products/
│           ├── detail.php      # Представлення сторінки деталей товару
│           └── list.php        # Представлення сторінки списку товарів
├── public/
│   ├── css/
│   │   └── style.css           # Основні CSS-стилі
│   ├── js/
│   │   ├── main.js             # Основний JavaScript для інтерактивності сайту
│   │   ├── product_list.js     # JavaScript для функціоналу каталогу товарів (сортування, пагінація)
│   │   └── utils.js            # Допоміжні JavaScript-утиліти (наприклад, переклад статусів)
│   ├── index.php               # Головний файл-маршрутизатор, точка входу
│   └── .htaccess               # Правила перезапису URL для "красивих" URL
├── database.sql                # SQL-файл для створення таблиць бази даних та початкових даних
└── README.md                   # Цей файл README
```
## Ліцензія
Цей проєкт поширюється під ліцензією MIT. Дивіться файл LICENSE для отримання додаткової інформації.

## Контакти
Якщо у вас є запитання або пропозиції, будь ласка, зв'яжіться з нами:

- Ezdrzael

- Email: ezdrael@gmail.com

- GitHub: https://github.com/Ezdrzael
