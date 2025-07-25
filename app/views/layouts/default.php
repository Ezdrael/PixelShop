<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Мій Інтернет-магазин'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Montserrat (Google Fonts) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Lobster (Google Fonts) -->
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- style.css -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css"> <!-- Коректний шлях до style.css -->

</head>
<body>

    <?php require_once __DIR__ . '/../partials/header.php'; ?>

    <main>
        <?php echo $content; ?>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

    <!-- Модальне вікно кошика -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen"> <!-- modal-fullscreen для повного екрану -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Ваш Кошик</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="cartModalBody" class="space-y-4">
                        <!-- Вміст кошика буде завантажено JavaScript'ом -->
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="text-2xl font-bold text-slate-800">
                        Загальна сума: <span id="cartTotal" class="text-primary cart-total-display">0 грн</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Продовжити покупки</button>
                        <button type="button" class="btn btn-primary" id="checkoutButton">Оформити замовлення</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальне вікно входу/реєстрації -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Вхід або Реєстрація</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="loginRegisterTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Вхід</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Реєстрація</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="loginRegisterTabContent">
                        <!-- Вкладка Вхід -->
                        <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                            <form id="loginForm">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="loginEmail" placeholder="ваша@пошта.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="loginPassword" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-3"><i class="fas fa-sign-in-alt me-2"></i>Увійти</button>
                                <div class="text-center">
                                    <p class="text-muted">або</p>
                                    <button type="button" class="btn btn-outline-dark w-100 google-btn" id="googleLoginBtn">
                                        <i class="fab fa-google me-2"></i>Увійти через Google
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- Вкладка Реєстрація -->
                        <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                            <form id="registerForm">
                                <div class="mb-3">
                                    <label for="registerName" class="form-label">Ім'я</label>
                                    <input type="text" class="form-control" id="registerName" placeholder="Ваше ім'я" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="registerEmail" placeholder="ваша@пошта.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerPassword" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="registerPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Підтвердіть пароль</label>
                                    <input type="password" class="form-control" id="confirmPassword" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100"><i class="fas fa-user-plus me-2"></i>Зареєструватися</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Утиліти JavaScript (містить translateOrderStatusJS) -->
    <script src="<?php echo BASE_URL; ?>/js/utils.js"></script>
    <!-- Основний JavaScript-файл для логіки сайту, включаючи кошик -->
    <script src="<?php echo BASE_URL; ?>/js/main.js"></script>
    <!-- JavaScript для сторінки каталогу (сортування, пагінація, діаграма) -->
    <script src="<?php echo BASE_URL; ?>/js/product_list.js"></script>
</body>
</html>
