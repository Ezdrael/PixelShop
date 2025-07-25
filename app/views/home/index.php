<?php
// app/views/home/index.php
// $pageTitle, $heroTitle, $heroLeadText та $featuredProducts доступні тут з HomeController.
ob_start(); // Починаємо буферизацію виводу
?>

<!-- Hero Section -->
<section class="hero-section animate__animated animate__fadeIn">
    <div class="container">
        <h1 class="animate__animated animate__fadeInDown"><?php echo $heroTitle; ?></h1>
        <p class="lead animate__animated animate__fadeInUp animate__delay-1s"><?php echo $heroLeadText; ?></p>
        <a href="<?php echo BASE_URL; ?>/product/list" class="btn btn-primary btn-lg mt-3 animate__animated animate__zoomIn animate__delay-2s">
            Переглянути товари <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<!-- Featured Products Section -->
<section class="container my-5">
    <h2 class="text-center mb-4 animate__animated animate__fadeInUp">Рекомендовані товари</h2>
    <div class="row">
        <?php if (!empty($featuredProducts)): ?>
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-4 col-sm-6 animate__animated animate__fadeInLeft animate__delay-1s">
                    <div class="card product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             loading="lazy"
                             onerror="this.onerror=null;this.src='https://placehold.co/400x250/e0e0e0/ffffff?text=No+Image';">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="lead fw-bold text-success"><?php echo htmlspecialchars($product['displayPrice']); ?></p>
                            <a href="<?php echo BASE_URL; ?>/product/detail/<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-success w-100"><i class="fas fa-info-circle me-2"></i>Детальніше</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted">
                <p>Наразі немає рекомендованих товарів.</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="text-center mt-4 animate__animated animate__fadeInUp animate__delay-2-5s">
        <a href="<?php echo BASE_URL; ?>/product/list" class="btn btn-outline-primary btn-lg">Переглянути весь каталог <i class="fas fa-chevron-right ms-2"></i></a>
    </div>
</section>

<!-- Services Section (залишається без змін) -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5 animate__animated animate__fadeInUp">Наші переваги</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                <div class="p-4 bg-white rounded shadow-sm h-100">
                    <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                    <h4 class="fw-bold">Швидка Доставка</h4>
                    <p class="text-muted">Доставляємо ваші замовлення в найкоротші терміни по всій країні.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4 animate__animated animate__fadeInUp animate__delay-1-5s">
                <div class="p-4 bg-white rounded shadow-sm h-100">
                    <i class="fas fa-undo-alt fa-3x text-success mb-3"></i>
                    <h4 class="fw-bold">Легке Повернення</h4>
                    <p class="text-muted">Можливість повернути товар протягом 14 днів без зайвих питань.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4 animate__animated animate__fadeInUp animate__delay-2s">
                <div class="p-4 bg-white rounded shadow-sm h-100">
                    <i class="fas fa-headset fa-3x text-warning mb-3"></i>
                    <h4 class="fw-bold">Підтримка 24/7</h4>
                    <p class="text-muted">Наша служба підтримки завжди готова допомогти вам.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
