// public/resources/js/main/ui-handlers.js

export function initUiHandlers() {
    // --- Логіка для мобільного меню та випадаючого меню профілю (без змін) ---
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

    // --- Універсальні обробники для ВСІХ віджетів (без змін) ---
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

            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const isAlreadyOpen = widget.classList.contains('open');
                document.querySelectorAll('.notes-widget, .clipboard-widget, .messages-widget').forEach(w => w.classList.remove('open'));
                if (!isAlreadyOpen) widget.classList.add('open');
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', () => widget.classList.remove('open'));
            }
        }
    });

    // === ОСНОВНЕ ВИПРАВЛЕННЯ: Оновлена логіка закриття при кліку поза елементами ===
    document.addEventListener('click', (event) => { 
        // --- Клік поза меню та сайдбаром (без змін) ---
        if (sidebar?.classList.contains('open') && !sidebar.contains(event.target) && !menuBtn?.contains(event.target)) { 
            sidebar.classList.remove('open'); 
        }
        if (userDropdown?.classList.contains('show') && !userProfileToggle?.contains(event.target)) {
            userDropdown.classList.remove('show');
        }
        
        // --- Клік поза віджетом ---
        const openWidget = document.querySelector('.notes-widget.open, .clipboard-widget.open, .messages-widget.open');
        if (openWidget) {
            const isClickInsideWidget = openWidget.contains(event.target);
            const isClickOnAnyToggleButton = event.target.closest('#messages-toggle-btn, #notes-toggle-btn, #clipboard-toggle-btn');
            
            // !! ДОДАНО ПЕРЕВІРКУ: ігноруємо кліки всередині будь-якого модального вікна !!
            const isClickInsideModal = event.target.closest('.modal-overlay');

            if (!isClickInsideWidget && !isClickOnAnyToggleButton && !isClickInsideModal) {
                openWidget.classList.remove('open');
            }
        }
    });
}