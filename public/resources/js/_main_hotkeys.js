// public/resources/js/_main_hotkeys.js

export function initHotkeys() {
    document.addEventListener('keydown', (event) => {

        // --- Відкриваємо віджети комбінацією Alt + Клавіша (без змін) ---
        if (event.altKey) {
            switch (event.code) {
                case 'KeyM':
                    event.preventDefault();
                    document.getElementById('messages-toggle-btn')?.click();
                    break;
                case 'KeyN':
                    event.preventDefault();
                    document.getElementById('notes-toggle-btn')?.click();
                    break;
                case 'KeyC':
                    event.preventDefault();
                    document.getElementById('clipboard-toggle-btn')?.click();
                    break;
            }
        }

        // --- ОНОВЛЕНА ЛОГІКА ДЛЯ КЛАВІШІ ESCAPE ---
        if (event.code === 'Escape') {
            event.preventDefault(); // Запобігаємо стандартній поведінці браузера

            // 1. Спочатку перевіряємо, чи є відкритий віджет
            const openWidget = document.querySelector('.notes-widget.open, .clipboard-widget.open, .messages-widget.open');
            if (openWidget) {
                // Якщо віджет є, закриваємо його і більше нічого не робимо
                openWidget.querySelector('.notes-close-btn, .clipboard-close-btn, .messages-close-btn')?.click();
                return; // Зупиняємо виконання подальшого коду
            }

            // 2. Якщо віджетів немає, перевіряємо, чи активний режим вибору фото
            const galleryContainer = document.getElementById('lightgallery-container');
            if (galleryContainer && galleryContainer.classList.contains('selection-mode-active')) {
                // Якщо режим активний, "натискаємо" на кнопку скасування
                document.getElementById('toggle-selection-mode')?.click();
                return; // Зупиняємо виконання
            }
            
            // Тут можна додати закриття інших елементів, наприклад, модальних вікон
            const openModal = document.querySelector('.modal-overlay[style*="display: flex"]');
            if(openModal) {
                 openModal.querySelector('.modal-close, .cancel')?.click();
            }
        }
    });
}