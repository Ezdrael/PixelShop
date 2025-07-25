<?php
// app/views/products/list.php
// $pageTitle, $products та $categories доступні тут з ProductController.
ob_start(); // Починаємо буферизацію виводу

// Визначаємо активну категорію з URL для підсвічування в меню
// $segments визначається у public/index.php
// Перевіряємо, чи існує $segments і чи є в ньому елемент з індексом 2
$currentCategory = isset($segments[2]) && $segments[2] !== '' ? $segments[2] : null;
?>

<!-- Хлібні крихти -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-1"></i>Головна</a>
        <span>/</span>
        <span><i class="fas fa-th-large me-1"></i>Каталог Товарів</span>
        <?php if ($currentCategory): ?>
            <span>/</span>
            <span><?php echo htmlspecialchars(ucfirst($currentCategory)); ?></span>
        <?php endif; ?>
    </div>
</section>

<!-- Основний контент -->
<main class="py-5 flex-grow-1">
    <div class="container">
        <div class="row">
            <!-- Бічна панель для категорій -->
            <div class="col-lg-3 mb-4 mb-lg-0 animate__animated animate__fadeInLeft">
                <div class="card shadow-sm p-4">
                    <h4 class="h5 fw-bold mb-4"><i class="fas fa-filter me-2"></i>Категорії</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item <?php echo ($currentCategory === null) ? 'active' : ''; ?>">
                            <a href="<?php echo BASE_URL; ?>/product/list" class="text-decoration-none d-block <?php echo ($currentCategory === null) ? 'text-white' : 'text-dark'; ?>">
                                <i class="fas fa-globe me-2"></i>Всі товари
                            </a>
                        </li>
                        <?php foreach ($categories as $categoryName): ?>
                            <li class="list-group-item <?php echo ($currentCategory === $categoryName) ? 'active' : ''; ?>">
                                <a href="<?php echo BASE_URL; ?>/product/list/<?php echo htmlspecialchars($categoryName); ?>" class="text-decoration-none d-block <?php echo ($currentCategory === $categoryName) ? 'text-white' : 'text-dark'; ?>">
                                    <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars(ucfirst($categoryName)); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Основна область з товарами -->
            <div class="col-lg-9">
                <section class="text-center mb-5 animate__animated animate__fadeInDown">
                    <h1 class="display-4 fw-bold text-dark mb-3">
                        <?php echo ($currentCategory) ? 'Товари категорії: ' . htmlspecialchars(ucfirst($currentCategory)) : 'Весь Каталог Товарів'; ?>
                    </h1>
                    <p class="lead text-muted mx-auto" style="max-width: 700px;">
                        Знайдіть найкращі товари за чудовими цінами, відсортуйте їх за вашими вподобаннями.
                    </p>
                </section>

                <!-- Фільтри та сортування -->
                <section class="mb-5 p-4 bg-white rounded shadow-sm animate__animated animate__fadeInUp">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <h3 class="h5 fw-bold text-dark mb-3 mb-md-0"><i class="fas fa-sort me-2"></i>Сортування</h3>
                        <div class="d-flex align-items-center me-4">
                            <label for="sort-by" class="form-label me-2 mb-0">Сортувати за:</label>
                            <select id="sort-by" class="form-select w-auto">
                                <option value="default">За замовчуванням</option>
                                <option value="price-asc">Ціна (зростання)</option>
                                <option value="price-desc">Ціна (спадання)</option>
                                <option value="name-asc">Назва (А-Я)</option>
                                <option value="name-desc">Назва (Я-А)</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center">
                            <label for="products-per-page" class="form-label me-2 mb-0">Товарів на сторінку:</label>
                            <select id="products-per-page" class="form-select w-auto">
                                <option value="8">8</option>
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="48">48</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Сітка товарів -->
                <section class="row" id="product-list">
                    <!-- Товари будуть згенеровані JavaScript -->
                </section>

                <!-- Пагінація -->
                <section class="d-flex justify-content-center mt-4 animate__animated animate__fadeInUp">
                    <nav>
                        <ul class="pagination" id="pagination-links">
                            <!-- Посилання пагінації будуть згенеровані JavaScript -->
                        </ul>
                    </nav>
                </section>

                <!-- Секція діаграми розподілу товарів за категоріями - ВИДАЛЕНО -->
                <!-- Цей блок було видалено за вашим запитом -->

            </div>
        </div>
    </div>
</main>

<?php
// Передаємо PHP-масив товарів до JavaScript
// Важливо: використовуємо json_encode для безпечної передачі даних
echo '<script type="text/javascript">';
echo 'const allProductsData = ' . json_encode($products) . ';';
echo 'const BASE_URL = "' . BASE_URL . '";'; // Передаємо BASE_URL до JavaScript
echo '</script>';

$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
