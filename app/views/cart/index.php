<?php
// app/views/cart/index.php
ob_start(); // Починаємо буферизацію виводу

// Переконайтеся, що $pageTitle та $requestUri визначені, якщо вони не прийшли з контролера
$pageTitle = $pageTitle ?? "Ваш Кошик | PixelShop";
$requestUri = $requestUri ?? $_SERVER['REQUEST_URI'];
?>

<!-- Хлібні крихти -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-1"></i>Головна</a>
        <span>/</span>
        <span><i class="fas fa-shopping-cart me-1"></i>Ваш Кошик</span>
    </div>
</section>

<main class="py-5 flex-grow-1">
    <div class="container">
        <h1 class="text-center mb-5 animate__animated animate__fadeInDown">Ваш Кошик</h1>

        <?php if (!empty($cartItems)): ?>
            <div class="row justify-content-center">
                <div class="col-lg-10 col-md-12">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-info animate__animated animate__fadeIn" role="alert">
                            <?php echo htmlspecialchars($_SESSION['message']); ?>
                        </div>
                        <?php unset($_SESSION['message']); // Очистити повідомлення після відображення ?>
                    <?php endif; ?>

                    <div class="card shadow-lg animate__animated animate__fadeInUp">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Товар</th>
                                            <th scope="col" class="text-center">Ціна</th>
                                            <th scope="col" class="text-center">Кількість</th>
                                            <th scope="col" class="text-end">Всього</th>
                                            <th scope="col" class="text-center">Дії</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? ''); ?>"
                                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                             class="img-thumbnail me-3"
                                                             style="width: 50px; height: 50px; object-fit: cover;"
                                                             onerror="this.onerror=null;this.src='https://placehold.co/50x50/e0e0e0/ffffff?text=No+Image';">
                                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><?php echo htmlspecialchars($item['displayPrice']); ?></td>
                                                <td class="text-center">
                                                    <form action="<?php echo BASE_URL; ?>/cart/update" method="POST" class="d-inline-flex">
                                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" class="form-control form-control-sm text-center" style="width: 70px;" onchange="this.form.submit()">
                                                    </form>
                                                </td>
                                                <td class="text-end fw-bold"><?php echo htmlspecialchars($item['displayItemTotal']); ?></td>
                                                <td class="text-center">
                                                    <form action="<?php echo BASE_URL; ?>/cart/remove" method="POST" class="d-inline">
                                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Видалити"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Загальна сума:</td>
                                            <td class="text-end fw-bold text-success fs-5"><?php echo htmlspecialchars($displayTotalAmount); ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo BASE_URL; ?>/product/list" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Продовжити покупки</a>
                                <div>
                                    <form action="<?php echo BASE_URL; ?>/cart/clear" method="POST" class="d-inline me-2">
                                        <button type="submit" class="btn btn-warning"><i class="fas fa-eraser me-2"></i>Очистити кошик</button>
                                    </form>
                                    <a href="<?php echo BASE_URL; ?>/order/checkout" class="btn btn-success"><i class="fas fa-credit-card me-2"></i>Оформити замовлення</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center animate__animated animate__fadeIn" role="alert">
                <i class="fas fa-info-circle me-2"></i>Ваш кошик порожній.
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/product/list" class="btn btn-primary"><i class="fas fa-shopping-bag me-2"></i>Почати покупки</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
