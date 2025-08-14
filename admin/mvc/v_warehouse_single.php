<div class="content-card">
    <?php if (isset($warehouse) && $warehouse): ?>
        
        <div class="form-header">
            <div>
                <h2><?php echo htmlspecialchars($warehouse['name']); ?></h2>
                <p class="user-id-text">ID складу: #<?php echo $warehouse['id']; ?></p>
            </div>
            <div class="actions-cell">
                <?php if ($this->hasPermission('warehouses', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/warehouses/edit/<?php echo $warehouse['id']; ?>" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/warehouses" class="action-btn" title="До списку складів"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>

        <div class="info-section">
            <h3><i class="fas fa-map-marker-alt"></i> Адреса</h3>
            <p><?php echo !empty($warehouse['address']) ? nl2br(htmlspecialchars($warehouse['address'])) : '<em>Адреса не вказана.</em>'; ?></p>
        </div>
        
        <div class="info-section">
            <h3><i class="fas fa-boxes"></i> Товари на складі</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID Товару</th>
                        <th>Назва товару</th>
                        <th style="text-align: right;">Поточний залишок</th>
                        <th style="text-align: center;">Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($product['good_id']); ?></td>
                                <td>
                                    <?php // Перевіряємо статус товару
                                    if (empty($product['is_active'])): ?>
                                        <span class="inactive-good" title="Товар вимкнено">
                                            <?php echo htmlspecialchars($product['good_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($product['good_name']); ?>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;"><?php echo htmlspecialchars($product['stock_level']); ?></td>
                                <td class="actions-cell" style="justify-content: center;">
                                    <?php if ($this->hasPermission('goods', 'v')): ?>
                                        <a href="<?php echo BASE_URL; ?>/goods/watch/<?php echo $product['good_id']; ?>" class="action-btn" title="Переглянути товар">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;"><em>На цьому складі немає товарів.</em></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <h2>Склад не знайдено</h2>
    <?php endif; ?>
</div>