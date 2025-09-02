<?php
// ===================================================================
// Файл: mvc/v_role_add.php (Оновлений)
// ===================================================================

// Оновлена функція для генерації перемикачів (toggle switches)
function render_permission_checkboxes($role, $resource_name, $label) {
    $permissions = $role['perm_' . $resource_name] ?? '';
    echo '<tr>';
    echo '<td><strong>' . htmlspecialchars($label) . '</strong></td>';
    
    // Оновлено: додано дозвіл 'c' (скасування)
    $actions = ['v' => 'Перегляд', 'a' => 'Додавання', 'e' => 'Редагування', 'd' => 'Видалення'];
    
    foreach ($actions as $char => $action_label) {
        $checked = strpos($permissions, $char) !== false ? 'checked' : '';
        echo '<td style="text-align: center;">';
        
        // --- ОСНОВНЕ ВИПРАВЛЕННЯ: Додано правильну HTML-структуру ---
        echo '<label class="toggle-switch" title="' . $action_label . '">';
        echo '<input type="checkbox" name="perms[' . $resource_name . '][]" value="' . $char . '" ' . $checked . '>';
        echo '<span class="slider"></span>';
        echo '</label>';
        
        echo '</td>';
    }
    echo '</tr>';
}
?>

<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <div>
                <h2><?php echo $this->title; ?></h2>
                <?php if (isset($role) && $role): ?>
                    <p class="user-id-text"><strong>ID:</strong> #<?php echo htmlspecialchars($role['id']); ?></p>
                <?php endif; ?>
            </div>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти">
                    <i class="fas fa-save"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>/roles" class="action-btn" title="Повернутися до списку">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
        
        <div class="form-body">
            <div class="form-group-inline">
                <label for="role-name">Назва ролі <span class="required-field">*</span></label>
                <input type="text" id="role-name" name="role_name" class="form-control" value="<?php echo htmlspecialchars($role['role_name'] ?? ''); ?>" required>
            </div>
        </div>
        
        <h3 style="margin-top: 2em; margin-bottom: 1em;">Налаштування дозволів</h3>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Розділ</th>
                    <th style="text-align: center;">Перегляд</th>
                    <th style="text-align: center;">Додавання</th>
                    <th style="text-align: center;">Редагування</th>
                    <th style="text-align: center;">Видалення</th>
                </tr>
            </thead>
            <tbody>
                <?php render_permission_checkboxes($role ?? [], 'chat', 'Повідомлення'); ?>
                <?php render_permission_checkboxes($role ?? [], 'roles', 'Ролі'); ?>
                <?php render_permission_checkboxes($role ?? [], 'users', 'Користувачі'); ?>
                <?php render_permission_checkboxes($role ?? [], 'categories', 'Категорії'); ?>
                <?php render_permission_checkboxes($role ?? [], 'goods', 'Товари'); ?>
                <?php render_permission_checkboxes($role ?? [], 'warehouses', 'Склади'); ?>
                <?php render_permission_checkboxes($role ?? [], 'arrivals', 'Надходження'); ?>
                <?php render_permission_checkboxes($role ?? [], 'transfers', 'Переміщення'); ?>
                <?php render_permission_checkboxes($role ?? [], 'albums', 'Фотоальбоми'); ?>
                <?php render_permission_checkboxes($role ?? [], 'currencies', 'Валюти'); ?>
                <?php render_permission_checkboxes($role ?? [], 'writeoffs', 'Списання'); ?>
                <?php render_permission_checkboxes($role ?? [], 'settings', 'Налаштування'); ?>
                <?php render_permission_checkboxes($role ?? [], 'notes', 'Нотатки'); ?>
                <?php render_permission_checkboxes($role ?? [], 'clipboard', 'Буфер обміну'); ?>
            </tbody>
        </table>
    </form>
</div>