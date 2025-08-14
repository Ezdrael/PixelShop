<div class="content-card">
    <div class="form-header">
        <h2>Історія переміщень</h2>
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
                <th>Дата та час</th>
                <th>Користувач</th>
                <th>Деталі переміщення</th>
                <th>Коментар</th>
                <th style="text-align: center;">Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transfersList)): ?>
                <?php foreach ($transfersList as $transfer): ?>
                    <tr>
                        <td><?php echo date('d.m.Y H:i:s', strtotime($transfer['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($transfer['user_name']); ?></td>
                        <td><?php echo $transfer['positions_html']; ?></td>
                        <td><?php echo htmlspecialchars($transfer['comment']); ?></td>
                        <td class="actions-cell" style="justify-content: center;">
                            <a href="<?php echo BASE_URL; ?>/transfers/watch/<?php echo $transfer['out_transaction_ids']; ?>" class="action-btn" title="Переглянути деталі">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($this->hasPermission('transfers', 'e')): ?>
                                <a href="<?php echo BASE_URL; ?>/transfers/edit/<?php echo $transfer['out_transaction_ids']; ?>" class="action-btn" title="Редагувати">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Операції переміщення ще не проводились.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>