// resources/js/main.js

import { initModalHandlers } from './_main_modal-handlers.js';
import { initUiHandlers } from './_main_ui-handlers.js';
import { initBreadcrumbs } from './_main_breadcrumbs.js';

// Запускаємо ініціалізацію всіх обробників після завантаження сторінки
document.addEventListener('DOMContentLoaded', () => {
    initUiHandlers();
    initModalHandlers();
    initBreadcrumbs();
});