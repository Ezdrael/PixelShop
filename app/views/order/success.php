<?php
// app/views/order/success.php
// $pageTitle доступний тут з OrderController.
ob_start(); // Починаємо буферизацію виводу
?>

<!-- Хлібні крихти -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-1"></i>Головна</a>
        <span>/</span>
        <span><i class="fas fa-check-circle me-1"></i>Замовлення Оформлено</span>
    </div>
</section>

<main class="py-5 flex-grow-1">
    <div class="container text-center">
        <h1 class="display-4 fw-bold text-success mb-4 animate__animated animate__fadeInDown">Замовлення Успішно Оформлено!</h1>
        <p class="lead text-muted mb-4 animate__animated animate__fadeInUp">
            Дякуємо за ваше замовлення! Ми отримали ваші дані та незабаром зв'яжемося з вами для підтвердження.
        </p>
        <i class="fas fa-check-circle text-success fa-5x mb-5 animate__animated animate__zoomIn"></i>
        <div class="animate__animated animate__fadeInUp animate__delay-1s">
            <a href="<?php echo BASE_URL; ?>/" class="btn btn-primary btn-lg me-3"><i class="fas fa-home me-2"></i>На головну</a>
            <a href="<?php echo BASE_URL; ?>/product/list" class="btn btn-outline-primary btn-lg"><i class="fas fa-th-large me-2"></i>Продовжити покупки</a>
        </div>
    </div>
</main>

<?php
$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
