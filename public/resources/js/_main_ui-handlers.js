// public/resources/js/_main_ui-handlers.js

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

    // Закриваємо меню, якщо клікнути поза ним
    document.addEventListener('click', (e) => { 
        if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !menuBtn?.contains(e.target)) { 
            sidebar.classList.remove('open'); 
        }
        if (userDropdown?.classList.contains('show') && !userProfileToggle?.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });

}