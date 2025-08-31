<?php
// app/Mvc/Views/admin/v_user_single.php
?>
<div class="content-card">
    <?php if ($user): ?>
        <div class="form-header">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div>
                    <?php if (!empty($user['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 0 0 8px rgba(171, 200, 207, 1);">
                    <?php else: ?>
                        <i class="fas fa-user-circle" style="font-size: 80px; color: var(--border-color);"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="user-id-text" style="margin-top: 0.5rem;"><strong>ID:</strong> #<?php echo htmlspecialchars($user['id']); ?></p>
                    <p class="user-id-text"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="user-id-text"><strong>Роль:</strong> 
                        <a href="<?php echo BASE_URL; ?>/roles/watch/<?php echo $user['role_id']; ?>" class="styled-link">
                            <?php echo htmlspecialchars($user['role_name']); ?>
                        </a>
                    </p>
                </div>
            </div>
            <div class="actions-cell">
                <?php if ($this->hasPermission('users', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo htmlspecialchars($user['id']); ?>" class="action-btn" title="Редагувати">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/users" class="action-btn" title="Повернутися до списку">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
        
        <div class="info-section">
            <h3><i class="fas fa-user-shield"></i> Дозволи цієї ролі</h3>
            <?php 
                // Передаємо дані користувача (які містять дозволи його ролі) в шаблон
                $permissions_source = $user;
                include '_template_permissions_table.php';
            ?>
        </div>

    <?php else: ?>
        <h2>Користувача не знайдено</h2>
        <a href="<?php echo BASE_URL; ?>/users" class="action-btn" title="Повернутися до списку" style="margin-top: 1.5em;">
            <i class="fas fa-arrow-left"></i>
        </a>
    <?php endif; ?>
</div>

<style>
    .styled-link {
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 500;
    }
    .styled-link:hover {
        text-decoration: underline;
    }
</style>