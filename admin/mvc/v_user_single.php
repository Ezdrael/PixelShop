<?php
// ===================================================================
// Файл: mvc/v_user_single.php 🕰️
// Розміщення: /mvc/v_user_single.php
// ===================================================================
?>
<div class="content-card">
    <?php if ($user): ?>
        <div class="form-header">
            <div>
                <h2>Профіль: <?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="user-id-text"><strong>ID:</strong> #<?php echo htmlspecialchars($user['id']); ?></p>
                <p class="user-id-text"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="user-id-text"><strong>Роль:</strong> <?php echo htmlspecialchars($user['role_name']); ?></p>
            </div>
            <div class="actions-cell">
                <?php if (strpos($currentUser['perm_users'] ?? '', 'e') !== false): ?>
                    <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo htmlspecialchars($user['id']); ?>" class="action-btn" title="Редагувати">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/users" class="action-btn" title="Повернутися до списку">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
        
        <h3 style="margin-top: 2em; margin-bottom: 1em;">Дозволи</h3>
        <?php 
            // Передаємо дані користувача (які містять дозволи його ролі) в шаблон
            $permissions_source = $user;
            include '_template_permissions_table.php';
        ?>

    <?php else: ?>
        <h2>Користувача не знайдено</h2>
        <a href="<?php echo BASE_URL; ?>/users" class="action-btn" title="Повернутися до списку" style="margin-top: 1.5em;">
            <i class="fas fa-arrow-left"></i>
        </a>
    <?php endif; ?>
</div>