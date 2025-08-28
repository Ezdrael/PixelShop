<div class="content-card">
    <div class="form-header">
        <h2>Всі товари</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('goods', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/goods/add" class="action-btn save" title="Додати товар"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <table class="orders-table">
        <thead>
            <tr>
                <th>ID</th><th>Назва</th><th>Категорія</th><th>Ціна</th><th>Статус</th><th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($goods as $good): ?>
                <tr>
                    <td>#<?php echo $good['id']; ?></td>
                    <td>
                        <?php // Перевіряємо статус товару для застосування стилю
                        if (empty($good['is_active'])): ?>
                            <span class="inactive-good">
                                <?php echo htmlspecialchars($good['name']); ?>
                            </span>
                        <?php else: ?>
                            <?php echo htmlspecialchars($good['name']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($good['category_name']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($good['price'], 2, '.', ' ')); ?> грн</td>
                    <td>
                        <?php if (!empty($good['is_active'])): ?>
                            <span class="status-badge active">Активний</span>
                        <?php else: ?>
                            <span class="status-badge inactive">Вимкнено</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <?php if ($this->hasPermission('goods', 'v')): ?>
                            <a href="<?php echo BASE_URL; ?>/goods/watch/<?php echo $good['id']; ?>" class="action-btn" title="Переглянути"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('goods', 'e')): ?>
                            <a href="<?php echo BASE_URL; ?>/goods/edit/<?php echo $good['id']; ?>" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('goods', 'd')): ?>
                            <button type="button" class="action-btn delete-btn" 
                                    data-entity="goods" data-user-id="<?php echo $good['id']; ?>" 
                                    data-user-name="<?php echo htmlspecialchars($good['name']); ?>" 
                                    title="Видалити"><i class="fas fa-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
