<?php
// public/app/Mvc/Views/admin/v_404.php
?>
<div class="content-card" style="text-align: center; padding: 3rem 1rem;">

    <img src="<?php echo PROJECT_URL; ?>/resources/img/layout/404.png" alt="Помилка 404" style="max-width: 300px; margin-bottom: 2rem;">

    <h2 style="font-size: 2.5rem; margin: 0 0 1rem 0; color: var(--primary-text);">Сторінку не знайдено</h2>
    <p style="font-size: 1.1rem; color: var(--secondary-text); margin-bottom: 2.5rem;">
        На жаль, ми не змогли знайти сторінку, яку ви шукали.<br>
        Можливо, її було видалено, або адреса була введена неправильно.
    </p>

    <button id="go-back-btn" class="btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
        <i class="fas fa-arrow-left"></i> Повернутися назад
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const goBackButton = document.getElementById('go-back-btn');
    
    // Перевіряємо, чи є куди повертатися в історії браузера
    if (goBackButton && window.history.length > 1) {
        goBackButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.history.back(); // Повертаємо користувача на попередню сторінку
        });
    } else if (goBackButton) {
        // Якщо історії немає, кнопка буде вести на головну сторінку адмін-панелі
        goBackButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = '<?php echo BASE_URL; ?>/';
        });
    }
});
</script>