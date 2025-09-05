<div class="content-card">
    <div class="form-header">
        <h2>Промокоди та знижки</h2>
        <div class="actions-cell">
            <?php if ($this->hasPermission('discounts', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/discounts/add" class="action-btn save" title="Створити знижку"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <p style="padding-top: 1rem;">Тут буде відображатися список всіх створених промокодів та знижок.</p>
</div>