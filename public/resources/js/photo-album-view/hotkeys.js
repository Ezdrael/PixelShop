// public/resources/js/photo-album-view/hotkeys.js

/**
 * Ініціалізує локальні гарячі клавіші для сторінки перегляду альбому.
 */
export function initHotKeys() {
    const galleryContainer = document.getElementById('lightgallery-container');
    if (!galleryContainer) return; // Виконуємо код тільки на сторінці альбому

    document.addEventListener('keydown', (event) => {
        // Ігноруємо, якщо фокус на полі вводу (крім Esc)
        const isInputFocused = document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA';
        if (isInputFocused && event.key !== 'Escape') {
            return;
        }

        // --- Обробка Alt + Клавіша ---
        if (event.altKey) {
            event.preventDefault();
            switch (event.code) {
                case 'Delete':
                    document.getElementById('delete-album-btn')?.click();
                    break;
                case 'KeyE':
                    document.getElementById('edit-album-btn')?.click();
                    break;
                case 'KeyU':
                    document.getElementById('upload-photo-btn')?.click();
                    break;
                case 'KeyS':
                    document.getElementById('toggle-selection-mode')?.click();
                    break;
            }
        }

        // --- ОБРОБКА ESCAPE З ПРІОРИТЕТАМИ ---
        if (event.key === 'Escape') {
            const isAnyModalOpen = document.querySelector('.modal-overlay[style*="display: flex"]');
            const isInSelectionMode = galleryContainer.classList.contains('selection-mode-active');

            // Пріоритет 1: Якщо відкрито модальне вікно, нічого не робимо.
            // Глобальний обробник в _main_hotkeys.js сам його закриє.
            if (isAnyModalOpen) {
                return; 
            }
            
            // Пріоритет 2: Якщо активний режим вибору, вимикаємо його.
            if (isInSelectionMode) {
                event.preventDefault(); // Запобігаємо іншим діям
                document.getElementById('toggle-selection-mode')?.click();
                return; // Зупиняємо подальшу обробку
            }
            
            // Пріоритет 3: Якщо нічого не відкрито, повертаємось до списку.
            document.getElementById('back-to-list-btn')?.click();
        }
    });
}