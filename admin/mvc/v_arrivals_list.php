<div class="content-card">
    <div class="form-header">
        <h2>Історія надходжень</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('arrivals', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/arrivals/add" class="action-btn save" title="Нове надходження"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Дата та час</th>
                <th>Користувач</th>
                <th>Позиції у надходженні</th>
                <th style="text-align: right;">Всього одиниць</th>
                <th>Дії</th> </tr>
        </thead>
        <tbody>
            <?php if (!empty($arrivalsList)): ?>
                <?php foreach ($arrivalsList as $arrival): ?>
                    <tr>
                        <td>
                            <?php echo date('d.m.Y H:i:s', strtotime($arrival['transaction_date'])); ?>
                        </td>
                        <td><?php echo htmlspecialchars($arrival['user_name']); ?></td>
                        <td><?php echo $arrival['positions_html']; ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo htmlspecialchars($arrival['total_quantity']); ?></td>
                        
                        <td class="actions-cell">
                            <?php if ($this->hasPermission('arrivals', 'v')): ?>
                                <a href="<?php echo BASE_URL; ?>/arrivals/watch/<?php echo urlencode($arrival['transaction_date']); ?>/<?php echo $arrival['user_id']; ?>" class="action-btn" title="Переглянути">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Історія надходжень порожня.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
