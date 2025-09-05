<div class="content-card">
    <div class="form-header">
        <h2>Групи атрибутів</h2>
        <div class="actions-cell">
            <?php if ($this->hasPermission('attributes', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/attributes/add" class="action-btn save" title="Створити атрибут"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <p style="padding-top: 1rem;">Тут буде список всіх створених груп атрибутів (Колір, Матеріал тощо).</p>
</div>