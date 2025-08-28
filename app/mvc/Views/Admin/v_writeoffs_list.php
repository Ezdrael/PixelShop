<div class="content-card">
    <div class="form-header">
        <h2>Списання товарів</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('writeoffs', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/writeoffs/add" class="action-btn save" title="Створити списання">
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
            <?php if (!empty($writeoffs)): foreach ($writeoffs as $doc): ?>
                <tr class="<?php echo ($doc['status'] === 'canceled') ? 'canceled-operation' : ''; ?>">
                    <td><?php echo date('d.m.Y H:i', strtotime($doc['transaction_date'])); ?></td>
                    <td><?php echo htmlspecialchars($doc['user_name'] ?? 'N/A'); ?></td>
                    <td class="positions-cell">
                        <?php echo $doc['positions_html']; ?>
                        <div class="comment-badge">
                            <strong>Склад:</strong> <?php echo htmlspecialchars($doc['warehouse_name']); ?><br>
                            <strong>Коментар:</strong> <?php echo htmlspecialchars($doc['comment']); ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($doc['status'] === 'canceled'): ?>
                            <span class="status-badge canceled">Скасовано</span>
                        <?php else: ?>
                            <span class="status-badge completed">Проведено</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <?php if ($this->hasPermission('writeoffs', 'e') && $doc['status'] !== 'canceled'): ?>
                            <a href="<?php echo BASE_URL . '/writeoffs/edit/' . $doc['transaction_ids']; ?>" class="action-btn" title="Редагувати">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('writeoffs', 'd') && $doc['status'] !== 'canceled'): ?>
                            <button type="button" class="action-btn delete-writeoff-btn" 
                                    data-ids="<?php echo $doc['transaction_ids']; ?>" 
                                    title="Видалити/Скасувати">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Ще не створено жодного списання.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>