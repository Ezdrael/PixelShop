<?php
// ===================================================================
// Файл: mvc/v_roles_list.php 🆕
// ===================================================================

function format_permissions($role, $key, $label) {
    if (!empty($role[$key])) {
        return "<strong>$label:</strong> " . htmlspecialchars($role[$key]);
    }
    return '';
}
?>
<div class="content-card">
    <div class="form-header">
        <h2>Всі ролі системи</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('roles', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/roles/add" class="action-btn save" title="Додати роль">
                    <i class="fas fa-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <table class="orders-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Назва ролі</th>
                <th>Дозволи</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($role['id']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($role['role_name']); ?>
                        <span class="category-id-badge" title="Кількість користувачів з цією роллю">
                            <i class="fas fa-users"></i> <?php echo $role['user_count']; ?>
                        </span>
                    </td>
                    <td>
                        <?php
                            $permissions = [
                                format_permissions($role, 'perm_chat', 'Повідомлення'),
                                format_permissions($role, 'perm_roles', 'Ролі'),
                                format_permissions($role, 'perm_users', 'Користувачі'),
                                format_permissions($role, 'perm_categories', 'Категорії'),
                                format_permissions($role, 'perm_goods', 'Товари'),
                                format_permissions($role, 'perm_warehouses', 'Склади'),
                                format_permissions($role, 'perm_arrivals', 'Надходження'),
                                format_permissions($role, 'perm_transfers', 'Переміщення'),
                                format_permissions($role, 'perm_albums', 'Фотоальбоми'),
                                format_permissions($role, 'perm_currencies', 'Валюти'),
                                format_permissions($role, 'perm_writeoffs', 'Списання'),
                                format_permissions($role, 'perm_settings', 'Налаштування')
                            ];
                            echo implode('<br>', array_filter($permissions));
                        ?>
                    </td>
                    <td class="actions-cell">
                        <a href="<?php echo BASE_URL; ?>/roles/watch/<?php echo htmlspecialchars($role['id']); ?>" class="action-btn" title="Переглянути">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($this->hasPermission('roles', 'e')): ?>
                            <a href="<?php echo BASE_URL; ?>/roles/edit/<?php echo htmlspecialchars($role['id']); ?>" class="action-btn" title="Редагувати">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('roles', 'd') && $role['id'] != 1): ?>
                            <button type="button" class="action-btn delete-btn" 
                                    data-entity="roles" data-user-id="<?php echo htmlspecialchars($role['id']); ?>" 
                                    data-user-name="<?php echo htmlspecialchars($role['role_name']); ?>" 
                                    title="Видалити">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

