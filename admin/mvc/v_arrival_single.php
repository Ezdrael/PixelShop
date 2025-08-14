<div class="content-card">
    <?php if (isset($arrivalData) && $arrivalData): ?>
        
        <div class="form-header">
            <div>
                <h2>Надходження від <?php echo date('d.m.Y', strtotime($arrivalData['details']['transaction_date'])); ?></h2>
                <p class="user-id-text">Детальний перегляд операції</p>
            </div>
            <div class="actions-cell">
                <a href="<?php echo BASE_URL; ?>/arrivals" class="action-btn" title="До історії надходжень">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Деталі операції</h3>
                <div class="info-card-body">
                    <p><strong>Час:</strong> <span><?php echo date('d.m.Y H:i:s', strtotime($arrivalData['details']['transaction_date'])); ?></span></p>
                    <p><strong>Користувач:</strong> <span><?php echo htmlspecialchars($arrivalData['details']['user_name']); ?></span></p>
                </div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-boxes"></i> Підсумки</h3>
                <div class="info-card-body">
                    <p><strong>Всього позицій:</strong> <span><?php echo htmlspecialchars($arrivalData['details']['total_positions']); ?></span></p>
                    <p><strong>Всього одиниць товару:</strong> <span><?php echo htmlspecialchars($arrivalData['details']['total_quantity']); ?></span></p>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3><i class="fas fa-dolly-flatbed"></i> Склад надходження</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Назва товару</th>
                        <th>Склад призначення</th>
                        <th style="text-align: right;">Кількість</th>
                        <th style="text-align: center;">Дії</th> </tr>
                </thead>
                <tbody>
                    <?php foreach ($arrivalData['items'] as $item): ?>
                    <tr>
                        <td>
                            <?php // Перевіряємо статус товару для застосування стилю
                            if (empty($item['is_active'])): ?>
                                <span class="inactive-good" title="Товар вимкнено">
                                    <?php echo htmlspecialchars($item['good_name']); ?>
                                </span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($item['good_name']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['warehouse_name']); ?></td>
                        <td style="text-align: right;"><?php echo htmlspecialchars($item['quantity']); ?></td>
                        
                        <td class="actions-cell" style="justify-content: center;">
                            <?php if ($this->hasPermission('goods', 'v')): ?>
                                <a href="<?php echo BASE_URL; ?>/goods/watch/<?php echo $item['good_id']; ?>" class="action-btn" title="Переглянути товар">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php else: ?>
        <h2>Надходження не знайдено</h2>
    <?php endif; ?>
</div>
