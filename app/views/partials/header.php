<?php
// app/views/partials/header.php

// Перевіряємо, чи змінна $requestUri вже визначена.
// Якщо ні, визначаємо її тут, щоб уникнути попереджень PHP.
// Цей блок має бути на самому початку файлу.
if (!isset($requestUri)) {
    $requestUri = trim($_SERVER['REQUEST_URI'], '/');
    $basePath = '/pixelshop/public'; // Переконайтеся, що це відповідає вашому базовому шляху
    if (strpos($requestUri, trim($basePath, '/')) === 0) {
        $requestUri = substr($requestUri, strlen(trim($basePath, '/')));
        $requestUri = trim($requestUri, '/');
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3">
    <div class="container">
        <a class="navbar-brand animate__animated animate__fadeInLeft" href="<?php echo BASE_URL; ?>/"><i class="fas fa-store me-2"></i>PixelShop</a> <!-- Змінено надпис на PixelShop -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($requestUri == '' || $requestUri == 'home/index') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-2"></i>Головна</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($requestUri == 'product/list') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/product/list"><i class="fas fa-th-large me-2"></i>Каталог</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/#"><i class="fas fa-info-circle me-2"></i>Про нас</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/#"><i class="fas fa-envelope me-2"></i>Контакти</a>
                </li>
                <li class="nav-item">
                    <!-- ОНОВЛЕНО: Тепер посилання веде на сторінку кошика, а не відкриває модальне вікно -->
                    <a class="nav-link <?php echo ($requestUri == 'cart/index') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/cart/index"><i class="fas fa-shopping-cart me-2"></i>Кошик <span class="badge bg-primary rounded-pill">0</span></a>
                </li>
                <!-- НОВИЙ ПУНКТ МЕНЮ: Мої Замовлення -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($requestUri == 'order/track') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/order/track"><i class="fas fa-box-open me-2"></i>Мої Замовлення</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/#"><i class="fas fa-user me-2"></i>Вхід</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
