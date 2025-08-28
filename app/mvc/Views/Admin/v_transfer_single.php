<div class="content-card">
    <?php if ($transferData): ?>
        <div class="form-header">
            <div>
                <h2>Перегляд переміщення</h2>
                <p class="user-id-text">
                    Операція від <strong><?php echo date('d.m.Y H:i:s', strtotime($transferData['details']['transaction_date'])); ?></strong>,
                    створив: <strong><?php echo htmlspecialchars($transferData['details']['user_name']); ?></strong>
                </p>
            </div>
            <div class="actions-cell">
                <?php if ($this->hasPermission('transfers', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/transfers/edit/<?php echo $ids; ?>" class="action-btn" title="Редагувати">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/transfers" class="action-btn" title="До списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        
        <?php if (!empty($transferData['details']['comment'])): ?>
        <div class="info-section">
            <h3><i class="fas fa-comment"></i> Коментар</h3>
            <p><?php echo nl2br(htmlspecialchars($transferData['details']['comment'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="info-section">
            <h3><i class="fas fa-dolly-flatbed"></i> Позиції документа</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Зі складу</th>
                        <th>На склад</th>
                        <th style="text-align: right;">Кількість</th>
                        <th style="text-align: center;">Дії</th> </tr>
                </thead>
                <tbody>
                    <?php foreach ($transferData['items'] as $item): ?>
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
                        <td><?php echo htmlspecialchars($item['from_warehouse']); ?></td>
                        <td><?php echo htmlspecialchars($item['to_warehouse']); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo abs($item['quantity']); ?></td>
                        
                        <td class="actions-cell" style="justify-content: center;">
                            <?php if ($this->hasPermission('goods', 'v') && isset($item['good_id'])): ?>
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
        <h2>Операцію переміщення не знайдено</h2>
    <?php endif; ?>
</div>
