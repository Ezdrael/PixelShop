<div class="content-card">
    <div class="form-header">
        <h2>Промокоди</h2>
        <div class="actions-cell">
            <?php if ($this->hasPermission('coupons', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/coupons/add" class="action-btn save" title="Створити промокод"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <p style="padding-top: 1rem;">Тут буде відображатися список всіх створених промокодів.</p>
</div>