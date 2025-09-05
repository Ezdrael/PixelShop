<div class="content-card">
    <div class="form-header">
        <h2>Баланс бонусних балів</h2>
        <div class="actions-cell">
            <?php if ($this->hasPermission('bonus_points', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/bonuspoints/add" class="action-btn save" title="Нарахувати/Списати бали"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <p style="padding-top: 1rem;">Тут буде таблиця з балансом бонусних балів кожного користувача.</p>
</div>