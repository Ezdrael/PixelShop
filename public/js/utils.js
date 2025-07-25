// public/js/utils.js

/**
 * Перекладає англійський статус замовлення на українську мову.
 * @param {string} status Англійський статус замовлення (наприклад, 'pending', 'processing').
 * @returns {string} Перекладений статус українською мовою.
 */
function translateOrderStatusJS(status) {
    switch (status) {
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
            // Повертаємо оригінал з великої літери, якщо переклад не знайдено
            return status.charAt(0).toUpperCase() + status.slice(1);
    }
}
