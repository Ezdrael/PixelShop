<?php
// app/views/order/tracking.php
// $pageTitle, $order та $errorMessage доступні тут з OrderController.
ob_start(); // Починаємо буферизацію виводу

// Функція для перекладу статусу на українську
function translateOrderStatus($status) {
    switch ($status) {
        case 'pending':
            return 'В очікуванні';
        case 'processing':
            return 'В обробці';
        case 'completed':
            return 'Виконано';
        case 'cancelled':
            return 'Скасовано';
        case 'shipped':
            return 'Відправлено';
        case 'delivered':
            return 'Доставлено';
        default:
            return ucfirst($status); // Повертаємо оригінал з великої літери, якщо переклад не знайдено
    }
}
?>

<!-- Хлібні крихти -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-1"></i>Головна</a>
        <span>/</span>
        <span><i class="fas fa-truck-fast me-1"></i>Відстеження Замовлення</span>
    </div>
</section>

<main class="py-5 flex-grow-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($errorMessage): ?>
                    <div class="alert alert-warning text-center animate__animated animate__fadeIn" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $errorMessage; ?>
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>/order/track" class="btn btn-outline-primary"><i class="fas fa-list me-2"></i>Перейти до списку моїх замовлень</a>
                        </div>
                    </div>
                <?php elseif ($order): ?>
                    <div class="card shadow-sm p-4 animate__animated animate__fadeIn">
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle fa-5x text-success mb-3 animate__animated animate__bounceIn"></i>
                            <h1 class="display-5 fw-bold text-success mb-3 animate__animated animate__fadeInDown">Замовлення успішно оформлено!</h1>
                            <p class="lead text-muted animate__animated animate__fadeInUp animate__delay-0-5s">Дякуємо за ваше замовлення, <?php echo htmlspecialchars($order['full_name']); ?>!</p>
                        </div>

                        <div class="mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                            <h2 class="h4 fw-bold mb-3"><i class="fas fa-receipt me-2"></i>Деталі замовлення #<?php echo htmlspecialchars($order['tracking_id']); ?></h2> <!-- Використовуємо tracking_id -->
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Статус:
                                    <span class="badge bg-info text-dark fw-bold"><?php echo htmlspecialchars(translateOrderStatus($order['status'])); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Загальна сума:
                                    <span class="fw-bold text-primary"><?php echo htmlspecialchars($order['displayTotalAmount']); ?></span>
                                </li>
                                <li class="list-group-item">
                                    Дата замовлення:
                                    <span class="float-end"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($order['created_at']))); ?></span>
                                </li>
                                <li class="list-group-item">
                                    Email:
                                    <span class="float-end"><?php echo htmlspecialchars($order['email']); ?></span>
                                </li>
                                <li class="list-group-item">
                                    Телефон:
                                    <span class="float-end"><?php echo htmlspecialchars($order['phone']); ?></span>
                                </li>
                                <li class="list-group-item">
                                    Адреса доставки:
                                    <span class="float-end"><?php echo htmlspecialchars($order['city'] . ', ' . $order['address_line']); ?></span>
                                </li>
                                <?php if (!empty($order['notes'])): ?>
                                    <li class="list-group-item">
                                        Коментарі:
                                        <span class="float-end text-muted"><?php echo htmlspecialchars($order['notes']); ?></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="mb-4 animate__animated animate__fadeInUp animate__delay-1-5s">
                            <h3 class="h5 fw-bold mb-3"><i class="fas fa-box-open me-2"></i>Товари в замовленні:</h3>
                            <ul class="list-group">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? ''); ?>"
                                                 alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>"
                                                 class="cart-item-image me-2"
                                                 style="width: 50px; height: 50px; object-fit: contain;"
                                                 onerror="this.onerror=null;this.src='https://placehold.co/50x50/e0e0e0/ffffff?text=No+Image';">
                                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        </div>
                                        <span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($item['quantity']); ?> шт. x <?php echo htmlspecialchars($item['displayPrice']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="text-center mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                            <p class="fw-bold">Ви можете відстежувати статус вашого замовлення за посиланням:</p>
                            <a href="<?php echo BASE_URL; ?>/order/track/<?php echo htmlspecialchars($order['tracking_id']); ?>" class="btn btn-outline-primary btn-lg"><i class="fas fa-link me-2"></i>Відстежити Замовлення</a> <!-- Використовуємо tracking_id -->
                            <p class="mt-3 text-muted">Збережіть це посилання, щоб перевірити статус пізніше.</p>
                        </div>

                        <div class="text-center mt-4 animate__animated animate__fadeInUp animate__delay-2-5s">
                            <a href="<?php echo BASE_URL; ?>/product/list" class="btn btn-secondary"><i class="fas fa-shopping-bag me-2"></i>Продовжити покупки</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
