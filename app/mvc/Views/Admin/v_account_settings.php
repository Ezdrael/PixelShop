<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2>Налаштування акаунту</h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти налаштування"><i class="fas fa-save"></i></button>
            </div>
        </div>

        <div class="tabs-container" style="margin-top: 1.5rem;">
            <div class="tab-nav">
                <a href="#" class="tab-link active" data-tab="profile"><i class="fas fa-user-circle"></i> Основні дані</a>
                <a href="#" class="tab-link" data-tab="interface"><i class="fas fa-palette"></i> Інтерфейс</a>
            </div>

            <div class="tab-content-wrapper">
                <div id="profile" class="tab-content active">
                    <div class="form-body">
                        <div class="form-group-inline">
                            <label for="user-name">Ім'я</label>
                            <input type="text" id="user-name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div class="form-group-inline">
                            <label for="user-email">Email</label>
                            <input type="email" id="user-email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <hr style="margin: 2rem 0;">
                        <div class="form-group-inline">
                            <label for="user-password">Новий пароль</label>
                            <input type="password" id="user-password" name="password" class="form-control" autocomplete="new-password" placeholder="Залиште порожнім, щоб не змінювати">
                        </div>
                        <div class="form-group-inline">
                            <label for="user-password-confirm">Підтвердження пароля</label>
                            <input type="password" id="user-password-confirm" name="password_confirm" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div id="interface" class="tab-content">
                    <div class="form-body">
                        <p>Тут буде реалізовано налаштування сортування пунктів бокового меню та кількості елементів на сторінках списків.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Простий скрипт для перемикання вкладок
    const tabContainer = document.querySelector('.tabs-container');
    if (tabContainer) {
        const tabLinks = tabContainer.querySelectorAll('.tab-link');
        const tabContents = tabContainer.querySelectorAll('.tab-content');

        tabLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const tabId = link.dataset.tab;
                
                tabLinks.forEach(item => item.classList.remove('active'));
                tabContents.forEach(item => item.classList.remove('active'));
                
                link.classList.add('active');
                document.getElementById(tabId)?.classList.add('active');
            });
        });
    }
});
</script>