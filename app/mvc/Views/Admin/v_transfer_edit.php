<div class="content-card">
    <?php if ($transferData): ?>
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <div>
                <h2>Редагування переміщення</h2>
                <p class="user-id-text">Операція від <?php echo date('d.m.Y H:i:s', strtotime($transferData['details']['transaction_date'])); ?></p>
            </div>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти коментар"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/transfers/watch/<?php echo $ids; ?>" class="action-btn" title="Повернутися до перегляду"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>

        <div class="form-body">
            <div class="form-group-inline">
                <label for="comment">Коментар</label>
                <textarea id="comment" name="comment" class="form-control" rows="3"><?php echo htmlspecialchars($transferData['details']['comment']); ?></textarea>
            </div>
        </div>
    </form>
    
    <div class="info-section">
        <h3><i class="fas fa-dolly-flatbed"></i> Позиції документа (не редагуються)</h3>
        <table class="orders-table">
            <thead><tr><th>Товар</th><th>Зі складу</th><th>На склад</th><th style="text-align: right;">Кількість</th></tr></thead>
            <tbody>
                <?php foreach ($transferData['items'] as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['good_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['from_warehouse']); ?></td>
                    <td><?php echo htmlspecialchars($item['to_warehouse']); ?></td>
                    <td style="text-align: right;"><?php echo abs($item['quantity']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($this->hasPermission('transfers', 'd')): ?>
            <div class="alert alert-warning">
                <p><strong>Увага:</strong> Скасування є незворотною дією. Вона створить зворотні транзакції, щоб повернути залишки на складах до стану, який був до цієї операції.</p><br>
                
                <form action="<?php echo BASE_URL; ?>/transfers/cancel/<?php echo $ids; ?>" method="POST" onsubmit="return confirm('Ви впевнені, що хочете скасувати цю операцію?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <button type="submit" class="btn-primary" style="background-color: var(--danger-color);">
                        <i class="fas fa-times-circle"></i> Скасувати переміщення
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
        <h2>Операцію переміщення не знайдено</h2>
    <?php endif; ?>
</div>