<?php
// app/views/order/checkout.php
// $pageTitle, $isAuthenticated, $savedAddresses доступні тут з OrderController.
ob_start(); // Починаємо буферизацію виводу
?>

<!-- Хлібні крихти -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-1"></i>Головна</a>
        <span>/</span>
        <span><i class="fas fa-shopping-cart me-1"></i>Кошик</span>
        <span>/</span>
        <span><i class="fas fa-cash-register me-1"></i>Оформлення Замовлення</span>
    </div>
</section>

<main class="py-5 flex-grow-1">
    <div class="container">
        <h1 class="text-center mb-5 animate__animated animate__fadeInDown">Оформлення Замовлення</h1>

        <div class="row">
            <!-- Інформація про замовлення (товари в кошику) -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm p-4 animate__animated animate__fadeInLeft">
                    <h2 class="h4 fw-bold mb-4"><i class="fas fa-shopping-basket me-2"></i>Ваше замовлення</h2>
                    <div id="checkoutCartItems" class="table-responsive">
                        <!-- Сюди буде завантажено вміст кошика JavaScript'ом -->
                        <p class="text-muted text-center">Завантаження товарів...</p>
                    </div>
                    <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">
                        <h4 class="h5 fw-bold mb-0">Загальна сума: <span id="checkoutCartTotal" class="text-primary">0 грн</span></h4>
                    </div>
                </div>
            </div>

            <!-- Форма оформлення замовлення -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm p-4 animate__animated animate__fadeInRight">
                    <h2 class="h4 fw-bold mb-4"><i class="fas fa-user-check me-2"></i>Ваші дані та доставка</h2>
                    <form id="checkoutForm">
                        <?php if ($isAuthenticated): ?>
                            <!-- Форма для авторизованого користувача -->
                            <div class="mb-3">
                                <label for="savedAddress" class="form-label">Виберіть збережену адресу:</label>
                                <select class="form-select" id="savedAddress">
                                    <option value="">Вибрати нову адресу</option>
                                    <?php foreach ($savedAddresses as $address): ?>
                                        <option value="<?php echo htmlspecialchars($address['id']); ?>">
                                            <?php echo htmlspecialchars($address['address_line'] . ', ' . $address['city']); ?>
                                            <?php echo !empty($address['notes']) ? ' (' . htmlspecialchars($address['notes']) . ')' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="newAddressFields" style="display: none;">
                                <hr class="my-4">
                                <h5 class="mb-3">Нова адреса доставки</h5>
                                <div class="mb-3">
                                    <label for="fullName" class="form-label">Повне ім'я <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Іван Петренко" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="example@gmail.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Телефон <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="+380XXXXXXXXX" required>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Вулиця, будинок, квартира" required>
                                </div>
                                <div class="mb-3">
                                    <label for="city" class="form-label">Місто <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="city" name="city" placeholder="Київ" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="saveAddress" name="saveAddress">
                                    <label class="form-check-label" for="saveAddress">Зберегти цю адресу для майбутніх замовлень</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Додаткові коментарі</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Наприклад, зручний час доставки..."></textarea>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const savedAddressSelect = document.getElementById('savedAddress');
                                    const newAddressFields = document.getElementById('newAddressFields');
                                    const fullNameInput = document.getElementById('fullName');
                                    const emailInput = document.getElementById('email');
                                    const phoneInput = document.getElementById('phone');
                                    const addressInput = document.getElementById('address');
                                    const cityInput = document.getElementById('city');

                                    function toggleNewAddressFields() {
                                        if (savedAddressSelect.value === '') {
                                            newAddressFields.style.display = 'block';
                                            // Зробити поля обов'язковими
                                            fullNameInput.setAttribute('required', 'required');
                                            emailInput.setAttribute('required', 'required');
                                            phoneInput.setAttribute('required', 'required');
                                            addressInput.setAttribute('required', 'required');
                                            cityInput.setAttribute('required', 'required');
                                        } else {
                                            newAddressFields.style.display = 'none';
                                            // Зробити поля необов'язковими
                                            fullNameInput.removeAttribute('required');
                                            emailInput.removeAttribute('required');
                                            phoneInput.removeAttribute('required');
                                            addressInput.removeAttribute('required');
                                            cityInput.removeAttribute('required');
                                        }
                                    }

                                    savedAddressSelect.addEventListener('change', toggleNewAddressFields);

                                    // Ініціалізувати стан полів при завантаженні сторінки
                                    toggleNewAddressFields();

                                    // Заповнити поля, якщо вибрано збережену адресу (імітація)
                                    savedAddressSelect.addEventListener('change', function() {
                                        const selectedId = this.value;
                                        if (selectedId !== '') {
                                            // У реальному додатку тут був би AJAX-запит для отримання деталей адреси за ID
                                            // Для імітації:
                                            const addresses = <?php echo json_encode($savedAddresses); ?>;
                                            const selectedAddress = addresses.find(addr => addr.id == selectedId);
                                            if (selectedAddress) {
                                                // Заповнення полів (якщо потрібно відображати їх для редагування)
                                                // Або просто переконатися, що вони порожні, якщо вибрано збережену адресу
                                                fullNameInput.value = 'Ім\'я Авторизованого Користувача'; // Приклад
                                                emailInput.value = 'auth@example.com'; // Приклад
                                                phoneInput.value = '+380123456789'; // Приклад
                                                addressInput.value = selectedAddress.address_line;
                                                cityInput.value = selectedAddress.city;
                                            }
                                        } else {
                                            // Очистити поля, якщо вибрано "Вибрати нову адресу"
                                            fullNameInput.value = '';
                                            emailInput.value = '';
                                            phoneInput.value = '';
                                            addressInput.value = '';
                                            cityInput.value = '';
                                        }
                                    });
                                });
                            </script>

                        <?php else: ?>
                            <!-- Звичайна форма для неавторизованого користувача -->
                            <div class="mb-3">
                                <label for="fullName" class="form-label">Повне ім'я <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Іван Петренко" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="example@gmail.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+380XXXXXXXXX" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Вулиця, будинок, квартира" required>
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">Місто <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="Київ" required>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Додаткові коментарі</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Наприклад, зручний час доставки..."></textarea>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-3"><i class="fa-solid fa-clipboard-check me-2"></i>Підтвердити Замовлення</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
