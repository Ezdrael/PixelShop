<?php
// ===================================================================
// Файл: mvc/v_role_single.php 🆕
// Призначення: Вигляд для сторінки перегляду однієї ролі.
// ===================================================================
?>

<div class="content-card">
    <?php if ($role): ?>
        <div class="form-header">
            <div>
                <h2><?php echo htmlspecialchars($role['role_name']); ?></h2>
                <p class="user-id-text"><strong>ID:</strong> #<?php echo htmlspecialchars($role['id']); ?></p>
            </div>
            <div class="actions-cell">
                <?php if ($this->hasPermission('roles', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/roles/edit/<?php echo htmlspecialchars($role['id']); ?>" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/roles" class="action-btn" title="До списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        
        <div class="tabs-container">
            <div class="tab-nav">
                <a href="#" class="tab-link active" data-tab="permissions"><i class="fas fa-user-shield"></i> Дозволи ролі</a>
                <a href="#" class="tab-link" data-tab="users"><i class="fas fa-users"></i> Користувачі з роллю</a>
            </div>

            <div class="tab-content-wrapper">
                <div id="permissions" class="tab-content active">
                    <h3 class="tab-content-header">Налаштування дозволів</h3>
                    <?php 
                        // Передаємо дані ролі в шаблон
                        $permissions_source = $role;
                        include '_template_permissions_table.php';
                    ?>
                </div>

                <div id="users" class="tab-content">
                    <h3 class="tab-content-header">Список користувачів</h3>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ім'я</th>
                                <th>Email</th>
                                <th style="text-align: center;">Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usersInRole)): ?>
                                <?php foreach ($usersInRole as $user): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="actions-cell" style="justify-content: center;">
                                        <?php if ($this->hasPermission('users', 'v')): ?>
                                            <a href="<?php echo BASE_URL; ?>/users/watch/<?php echo $user['id']; ?>" class="action-btn" title="Переглянути користувача"><i class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align: center;"><em>Користувачі з цією роллю відсутні.</em></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <h2>Роль не знайдено</h2>
    <?php endif; ?>
</div>

<script>
// Простий скрипт для перемикання вкладок
document.addEventListener('DOMContentLoaded', () => {
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

