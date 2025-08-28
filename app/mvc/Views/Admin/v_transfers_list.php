<div class="content-card">
    <div class="form-header">
        <h2>Переміщення товарів</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('transfers', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/transfers/add" class="action-btn save" title="Створити переміщення">
                    <i class="fas fa-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Дата</th>
                <th>Користувач</th>
                <th>Позиції</th>
                <th>Статус</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transfers)): foreach ($transfers as $transferDoc): ?>
                <?php
                    $isCanceled = ($transferDoc['status'] === 'canceled');
                    $rowClass = $isCanceled ? 'canceled-operation' : '';
                ?>
                <tr class="<?php echo $rowClass; ?>">
                    <td><?php echo date('d.m.Y H:i', strtotime($transferDoc['transaction_date'])); ?></td>
                    <td><?php echo htmlspecialchars($transferDoc['user_name'] ?? 'N/A'); ?></td>
                    
                    <td class="positions-cell">
                        <?php echo $transferDoc['positions_html']; ?>
                    </td>
                    
                    <td>
                        <?php if ($isCanceled): ?>
                            <span class="status-badge canceled">Скасовано</span>
                        <?php else: ?>
                            <span class="status-badge completed">Проведено</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="<?php echo BASE_URL; ?>/transfers/watch/<?php echo $transferDoc['transaction_ids']; ?>" class="action-btn" title="Переглянути">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Ще не створено жодного переміщення.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>