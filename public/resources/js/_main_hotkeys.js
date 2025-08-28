// public/resources/js/_main_hotkeys.js

export function initHotkeys() {
    document.addEventListener('keydown', (event) => {
        // --- Відкриваємо віджети комбінацією Alt + Клавіша ---
        if (event.altKey) {
            switch (event.key.toLowerCase()) {
                case 'm': // M for Messages (Повідомлення)
                    event.preventDefault();
                    document.getElementById('messages-toggle-btn')?.click();
                    break;
                case 'n': // N for Notes (Нотатки)
                    event.preventDefault();
                    document.getElementById('notes-toggle-btn')?.click();
                    break;
                case 'c': // C for Clipboard (Буфер обміну)
                    event.preventDefault();
                    document.getElementById('clipboard-toggle-btn')?.click();
                    break;
            }
        }

        // --- Закриваємо будь-який активний віджет по клавіші Escape ---
        if (event.key === 'Escape') {
            // Знаходимо будь-який віджет, що має клас .open
            const openWidget = document.querySelector('.notes-widget.open, .clipboard-widget.open, .messages-widget.open');
            
            if (openWidget) {
                event.preventDefault();
                // Знаходимо всередині нього кнопку закриття і натискаємо на неї
                openWidget.querySelector('.notes-close-btn, .clipboard-close-btn, .messages-close-btn')?.click();
            }
        }
    });
}