<?php
// app/Mvc/Views/admin/v_users_list.php
?>
<div class="content-card">
    <div class="form-header">
        <h2>Всі користувачі системи</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('users', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/users/add" class="action-btn save" title="Додати користувача">
                    <i class="fas fa-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <table class="orders-table">
        <thead>
            <tr>
                <th>ID</th>
                <th colspan="2">Ім'я</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($user['id']); ?></td>
                    
                    <td style="width: 50px; padding-right: 0;">
                        <?php if (!empty($user['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="user-avatar-small">
                        <?php else: ?>
                            <i class="fas fa-user-circle user-avatar-placeholder"></i>
                        <?php endif; ?>
                    </td>
                    
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                    <td class="actions-cell">
                        <a href="<?php echo BASE_URL; ?>/users/watch/<?php echo htmlspecialchars($user['id']); ?>" class="action-btn" title="Переглянути">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($this->hasPermission('users', 'e')): ?>
                            <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo htmlspecialchars($user['id']); ?>" class="action-btn" title="Редагувати">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('users', 'd')): ?>
                            <button type="button" class="action-btn delete-btn" 
                                    data-entity="users" data-user-id="<?php echo htmlspecialchars($user['id']); ?>" 
                                    data-user-name="<?php echo htmlspecialchars($user['name']); ?>" 
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