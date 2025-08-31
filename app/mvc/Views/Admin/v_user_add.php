<?php
// ===================================================================
// Файл: mvc/v_user_add.php 
// Розміщення: /mvc/v_user_add.php
// Призначення: Вигляд для сторінки додавання нового користувача.
// ===================================================================
?>
<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <div>
                <h2>Додавання нового користувача</h2>
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
                <input type="text" id="user-name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group-inline">
                <label for="user-email">Email</label>
                <input type="email" id="user-email" name="email" class="form-control" required>
            </div>

            <div class="form-group-inline">
                <label for="user-password">Пароль</label>
                <input type="password" id="user-password" name="password" class="form-control" required>
            </div>

            <div class="form-group-inline">
                <label for="user-role">Роль</label>
                <select id="user-role" name="role_id" class="form-control" required>
                    <?php foreach($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group-inline">
                <label for="user-avatar">URL аватара</label>
                <input type="url" id="user-avatar" name="avatar_url" class="form-control" placeholder="https://example.com/photo.jpg">
            </div>
        </div>
    </form>
</div>
