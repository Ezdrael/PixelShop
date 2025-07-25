<?php
// app/views/products/detail.php
// $pageTitle та $product доступні тут з ProductController.
ob_start(); // Починаємо буферизацію виводу
?>

<!-- Хлібні крихти -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-1"></i>Головна</a>
        <span>/</span>
        <a href="<?php echo BASE_URL; ?>/product/list"><i class="fas fa-th-large me-1"></i>Каталог Товарів</a>
        <span>/</span>
        <a href="<?php echo BASE_URL; ?>/product/list/<?php echo htmlspecialchars($product['category_name']); ?>"><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars(ucfirst($product['category_name'])); ?></a>
        <span>/</span>
        <span><i class="fas fa-mobile-alt me-1"></i><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
</section>

<!-- Основний контент сторінки товару -->
<main class="py-5 flex-grow-1">
    <div class="container">
        <div class="row product-detail-section animate__animated animate__fadeIn">
            <!-- Колонка для зображень товару -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>"
                     class="img-fluid product-main-image"
                     alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?> - Основне зображення"
                     id="mainProductImage"
                     onerror="this.onerror=null;this.src='https://placehold.co/600x400/e0e0e0/ffffff?text=No+Image';">
            </div>

            <!-- Колонка для інформації про товар -->
            <div class="col-lg-6">
                <h1 class="product-title animate__animated animate__fadeInRight"><?php echo htmlspecialchars($product['name'] ?? ''); ?> <i class="fas fa-mobile-alt ms-3 text-muted opacity-75"></i></h1>
                <p class="product-price animate__animated animate__fadeInRight animate__delay-0-5s"><?php echo htmlspecialchars($product['displayPrice'] ?? '0 грн'); ?></p>
                <p class="product-description animate__animated animate__fadeInRight animate__delay-1s">
                    <?php echo htmlspecialchars($product['description'] ?? ''); ?>
                </p>

                <div class="d-flex align-items-center mb-4 animate__animated animate__fadeInUp animate__delay-1-5s">
                    <label for="quantity" class="form-label me-3 mb-0">Кількість:</label>
                    <input type="number" id="quantity" class="form-control quantity-input flex-grow-0" value="1" min="1" max="<?php echo htmlspecialchars($product['quantity'] ?? 0); ?>">
                    <button type="button" class="btn btn-add-to-cart ms-3"
                            data-id="<?php echo htmlspecialchars($product['id'] ?? ''); ?>"
                            data-name="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                            data-price="<?php echo htmlspecialchars($product['price'] ?? 0); ?>"
                            data-display-price="<?php echo htmlspecialchars($product['displayPrice'] ?? '0 грн'); ?>"
                            data-image-url="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>"
                            data-available-quantity="<?php echo htmlspecialchars($product['quantity'] ?? 0); ?>"> <!-- Додано доступну кількість -->
                        <i class="fas fa-cart-plus me-2"></i>Додати в кошик
                    </button>
                </div>

                <ul class="list-unstyled text-muted animate__animated animate__fadeInUp animate__delay-2s">
                    <li><i class="fas fa-check-circle text-success me-2"></i> В наявності: <span id="availableQuantity"><?php echo htmlspecialchars($product['quantity'] ?? 0); ?></span> шт.</li> <!-- Відображення кількості -->
                    <li><i class="fas fa-shipping-fast text-info me-2"></i> Швидка доставка</li>
                    <li><i class="fas fa-star text-warning me-2"></i> Рейтинг: 4.8 (125 відгуків)</li>
                </ul>
            </div>
        </div>

        <!-- Вкладки з характеристиками та відгуками -->
        <div class="row mt-5 animate__animated animate__fadeInUp animate__delay-2-5s">
            <div class="col-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab" aria-controls="specs" aria-selected="true"><i class="fas fa-info-circle me-2"></i>Характеристики</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false"><i class="fas fa-comments me-2"></i>Відгуки</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                        <h4 class="mb-3">Технічні характеристики</h4>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-display me-2 text-primary"></i><strong>Дисплей:</strong> 6.7" Super Retina XDR</li>
                            <li><i class="fas fa-microchip me-2 text-primary"></i><strong>Процесор:</strong> A17 Bionic</li>
                            <li><i class="fas fa-memory me-2 text-primary"></i><strong>Пам'ять:</strong> 256 ГБ</li>
                            <li><i class="fas fa-camera me-2 text-primary"></i><strong>Камера:</strong> 48 МП основна, 12 МП ультраширококутна</li>
                            <li><i class="fas fa-battery-full me-2 text-primary"></i><strong>Акумулятор:</strong> До 24 годин відтворення відео</li>
                            <li><i class="fas fa-mobile-alt me-2 text-primary"></i><strong>Операційна система:</strong> iOS 18</li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                        <h4 class="mb-3">Відгуки покупців (3)</h4>
                        <div class="mb-3 border-bottom pb-3">
                            <strong>Іван П.</strong> <span class="text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span>
                            <p class="text-muted mb-1">"Просто неймовірний телефон! Камера робить чудові фото, а швидкість роботи вражає."</p>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>15 липня 2025</small>
                        </div>
                        <div class="mb-3 border-bottom pb-3">
                            <strong>Олена С.</strong> <span class="text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></span>
                            <p class="text-muted mb-1">"Відмінний пристрій, але батарея могла б тримати трохи довше."</p>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>10 липня 2025</small>
                        </div>
                         <div class="mb-3">
                            <strong>Дмитро В.</strong> <span class="text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span>
                            <p class="text-muted mb-1">"Перейшов з Android, дуже задоволений. Інтерфейс інтуїтивно зрозумілий."</p>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>8 липня 2025</small>
                        </div>
                        <button class="btn btn-outline-primary mt-3" data-bs-toggle="modal" data-bs-target="#reviewModal"><i class="fas fa-pen-to-square me-2"></i>Написати відгук</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Модальне вікно для написання відгуку (залишається без змін) -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel"><i class="fas fa-pen-to-square me-2"></i>Написати відгук про <?php echo htmlspecialchars($product['name'] ?? ''); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <div class="mb-3">
                        <label for="reviewerName" class="form-label">Ваше ім'я (необов'язково):</label>
                        <input type="text" class="form-control" id="reviewerName" placeholder="Ваше ім'я">
                    </div>
                    <div class="mb-3">
                        <label for="reviewerEmail" class="form-label">Ваш email (необов'язково):</label>
                        <input type="email" class="form-control" id="reviewerEmail" placeholder="name@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Ваша оцінка:</label>
                        <div class="star-rating" id="starRating">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" id="selectedRating" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="reviewText" class="form-label">Ваш відгук:</label>
                        <textarea class="form-control" id="reviewText" rows="5" placeholder="Напишіть ваш відгук тут..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Скасувати</button>
                <button type="button" class="btn btn-primary" id="submitReviewBtn"><i class="fas fa-paper-plane me-2"></i>Відправити відгук</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
