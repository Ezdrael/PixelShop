// public/resources/js/main/ui-handlers.js

export function initUiHandlers() {
    // --- Логіка для мобільного меню та випадаючого меню профілю ---
    const menuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const userProfileToggle = document.getElementById('user-profile-toggle');
    const userDropdown = document.getElementById('user-dropdown');

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    }
    
    if (userProfileToggle && userDropdown) {
        userProfileToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            userDropdown.classList.toggle('show');
        });
    }

    // --- УНІВЕРСАЛЬНІ ОБРОБНИКИ ДЛЯ ВІДЖЕТІВ (ВИПРАВЛЕНО) ---
    const widgets = [
        { btnId: 'messages-toggle-btn', widgetId: 'messages-widget' },
        { btnId: 'notes-toggle-btn', widgetId: 'notes-widget' },
        { btnId: 'clipboard-toggle-btn', widgetId: 'clipboard-widget' }
    ];

    widgets.forEach(({ btnId, widgetId }) => {
        const toggleBtn = document.getElementById(btnId);
        const widget = document.getElementById(widgetId);
        
        if (toggleBtn && widget) {
            const closeBtn = widget.querySelector('.notes-close-btn, .clipboard-close-btn, .messages-close-btn');

            // Клік по кнопці в хедері
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const isAlreadyOpen = widget.classList.contains('open');

                // Спочатку закриваємо всі віджети
                document.querySelectorAll('.notes-widget, .clipboard-widget, .messages-widget').forEach(w => {
                    w.classList.remove('open');
                });
                
                // Якщо віджет не був відкритий, відкриваємо його
                if (!isAlreadyOpen) {
                    widget.classList.add('open');
                }
            });

            // Клік по кнопці "хрестик" всередині віджета
            if (closeBtn) {
                closeBtn.addEventListener('click', () => widget.classList.remove('open'));
            }
        }
    });

    // --- Логіка закриття меню та віджетів при кліку поза ними ---
    document.addEventListener('click', (e) => { 
        if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !menuBtn?.contains(e.target)) { 
            sidebar.classList.remove('open'); 
        }
        
        if (userDropdown?.classList.contains('show') && !userProfileToggle?.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
        
        const openWidget = document.querySelector('.notes-widget.open, .clipboard-widget.open, .messages-widget.open');
        if (openWidget) {
            const isClickInsideWidget = openWidget.contains(e.target);
            const isClickOnAnyToggleButton = e.target.closest('#messages-toggle-btn, #notes-toggle-btn, #clipboard-toggle-btn');

            if (!isClickInsideWidget && !isClickOnAnyToggleButton) {
                openWidget.classList.remove('open');
            }
        }
    });
}