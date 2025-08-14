<?php
// ===================================================================
// Файл: mvc/v_user_edit.php 🕰️
// Розміщення: /mvc/v_user_edit.php
// Призначення: Вигляд для сторінки редагування користувача.
// ===================================================================

// Допоміжна функція для відображення іконок дозволів
function render_permission_icon($permissions, $char) {
    if (strpos($permissions ?? '', $char) !== false) {
        return '<i class="fas fa-check-circle perm-icon yes"></i>';
    } else {
        return '<i class="fas fa-times-circle perm-icon no"></i>';
    }
}
?>
<div class="content-card">
    <?php if ($user): ?>
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <div>
                <h2>Редагування: <?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="user-id-text"><strong>ID:</strong> #<?php echo htmlspecialchars($user['id']); ?></p>
            </div>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти">
                    <i class="fas fa-save"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>/users" class="action-btn" title="Повернутися до списку">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
        
        <div class="form-body">
            <div class="form-group-inline">
                <label for="user-name">Ім'я</label>
                <input type="text" id="user-name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>">
            </div>
            
            <div class="form-group-inline">
                <label for="user-email">Email</label>
                <input type="email" id="user-email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <div class="form-group-inline">
                <label for="user-role">Роль</label>
                <select id="user-role" name="role_id" class="form-control">
                    <?php foreach($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" <?php echo ($role['id'] == $user['role_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <h3 style="margin-top: 2em; margin-bottom: 1em;">
            Дозволи ролі (редагуються <a href="<?php echo BASE_URL; ?>/roles">тут</a>)
        </h3>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Розділ</th>
                    <th>Перегляд (v)</th>
                    <th>Додавання (a)</th>
                    <th>Редагування (e)</th>
                    <th>Видалення (d)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Користувачі</td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_users'], 'v'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_users'], 'a'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_users'], 'e'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_users'], 'd'); ?></td>
                </tr>
                <tr>
                    <td>Ролі</td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_roles'], 'v'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_roles'], 'a'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_roles'], 'e'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_roles'], 'd'); ?></td>
                </tr>
                <tr>
                    <td>Категорії</td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_categories'], 'v'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_categories'], 'a'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_categories'], 'e'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_categories'], 'd'); ?></td>
                </tr>
                <tr>
                    <td>Товари</td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_goods'], 'v'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_goods'], 'a'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_goods'], 'e'); ?></td>
                    <td style="text-align: center;"><?php echo render_permission_icon($user['perm_goods'], 'd'); ?></td>
                </tr>
            </tbody>
        </table>

    </form>
    <?php else: ?>
        <h2>Користувача не знайдено</h2>
        <a href="<?php echo BASE_URL; ?>/users" class="action-btn" title="Повернутися до списку" style="margin-top: 1.5em;">
            <i class="fas fa-arrow-left"></i>
        </a>
    <?php endif; ?>
</div>
