<?php
// app/views/order/track_list.php
// $pageTitle доступний тут з OrderController.
ob_start(); // Починаємо буферизацію виводу

// PHP-функція translateOrderStatus видалена з цього файлу, оскільки вона не використовується для PHP-рендерингу тут.
// JavaScript-функція translateOrderStatusJS тепер завантажується з public/js/utils.js.
?>

<!-- Хлібні крихти -->
<section class="breadcrumbs">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home me-1"></i>Головна</a>
        <span>/</span>
        <span><i class="fa-solid fa-clipboard-check me-1"></i>Мої Замовлення</span>
    </div>
</section>

<main class="py-5 flex-grow-1">
    <div class="container">
        <h1 class="text-center mb-5 animate__animated animate__fadeInDown">Мої Замовлення</h1>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div id="ordersListContainer">
                    <!-- Список замовлень буде завантажено JavaScript'ом -->
                    <div class="text-center text-muted">Завантаження...</div>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript">
    // Функція translateOrderStatusJS тепер знаходиться у public/js/utils.js
    // і буде доступна глобально після її підключення в default.php

    document.addEventListener('DOMContentLoaded', function() {
        const ordersListContainer = document.getElementById('ordersListContainer');
        const BASE_URL = "<?php echo BASE_URL; ?>"; // Отримуємо BASE_URL з PHP

        async function loadAndDisplayOrdersList() {
            // Отримуємо список tracking_id з localStorage
            const myOrders = JSON.parse(localStorage.getItem('myOrders') || '[]');
            ordersListContainer.innerHTML = ''; // Очищаємо контейнер

            if (myOrders.length === 0) {
                ordersListContainer.innerHTML = `
                    <div class="alert alert-info text-center" role="alert">
                        У вас ще немає замовлень. Оформіть перше!
                    </div>
                    <div class="text-center mt-4">
                        <a href="${BASE_URL}/product/list" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Почати покупки
                        </a>
                    </div>
                `;
                return;
            }

            ordersListContainer.innerHTML = '<div class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Завантаження ваших замовлень...</div>';

            const ordersHtml = [];
            for (const trackingId of myOrders) {
                try {
                    const response = await fetch(`${BASE_URL}/order/getJsonOrderDetails`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ tracking_id: trackingId })
                    });
                    const result = await response.json();

                    if (result.success && result.order) {
                        const order = result.order;
                        ordersHtml.push(`
                            <div class="card shadow-sm mb-3 order-list-item animate__animated animate__fadeInUp" data-tracking-id="${order.tracking_id}">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">Замовлення #${order.tracking_id}</h5>
                                    <p class="card-text mb-1">Статус: <span class="badge bg-info text-dark">${translateOrderStatusJS(order.status)}</span></p>
                                    <p class="card-text mb-1">Дата: ${new Date(order.created_at).toLocaleString('uk-UA')}</p>
                                    <p class="card-text mb-1">Сума: <span class="fw-bold text-primary">${order.displayTotalAmount}</span></p>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 view-order-details" data-tracking-id="${order.tracking_id}">
                                        <i class="fas fa-eye me-1"></i>Деталі замовлення
                                    </button>
                                </div>
                            </div>
                        `);
                    } else {
                        console.error(`Помилка завантаження замовлення ${trackingId}:`, result.message);
                        ordersHtml.push(`
                            <div class="alert alert-warning" role="alert">
                                Не вдалося завантажити деталі замовлення з ID: ${trackingId}.
                            </div>
                        `);
                    }
                } catch (error) {
                    console.error(`Помилка мережі при завантаженні замовлення ${trackingId}:`, error);
                    ordersHtml.push(`
                        <div class="alert alert-danger" role="alert">
                            Помилка з'єднання для замовлення з ID: ${trackingId}.
                        </div>
                    `);
                }
            }
            ordersListContainer.innerHTML = ordersHtml.join('');

            // Додаємо обробники подій для кнопок "Деталі замовлення"
            ordersListContainer.querySelectorAll('.view-order-details').forEach(button => {
                button.addEventListener('click', (e) => {
                    const trackingId = e.currentTarget.dataset.trackingId;
                    window.location.href = `${BASE_URL}/order/track/${trackingId}`;
                });
            });
        }

        loadAndDisplayOrdersList(); // Викликаємо функцію при завантаженні сторінки
    });
</script>

<?php
$content = ob_get_clean(); // Зберігаємо вивід у змінну $content
require_once __DIR__ . '/../layouts/default.php'; // Підключаємо основний шаблон
?>
