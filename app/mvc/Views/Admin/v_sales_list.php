<div class="content-card">
    <div class="form-header">
        <h2>Список акцій</h2>
        <div class="actions-cell">
            <?php if ($this->hasPermission('sales', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/sales/add" class="action-btn save" title="Створити акцію"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <p style="padding-top: 1rem;">Тут буде відображатися список всіх створених акцій.</p>
</div>