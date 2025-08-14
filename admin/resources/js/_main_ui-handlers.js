// resources/js/ui-handlers.js

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

    document.addEventListener('click', (e) => { 
        if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !menuBtn?.contains(e.target)) { 
            sidebar.classList.remove('open'); 
        }
        if (userDropdown?.classList.contains('show') && !userProfileToggle?.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });

    // --- Логіка для флеш-повідомлень ---
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        const flashCloseBtn = document.getElementById('flashCloseBtn');
        const flashTimerSpan = document.getElementById('flashTimer');
        let countdown = 10;

        const closeFlashMessage = () => {
            if (!flashMessage) return;
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.style.display = 'none', 300);
            clearInterval(timerInterval);
        };

        if (flashCloseBtn) {
            flashCloseBtn.addEventListener('click', closeFlashMessage);
        }

        const timerInterval = setInterval(() => {
            countdown--;
            if (flashTimerSpan) flashTimerSpan.textContent = countdown;
            if (countdown <= 0) closeFlashMessage();
        }, 1000);
    }
}