<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="transaction_ids" value="<?php echo htmlspecialchars($transaction_ids); ?>">
        <div class="form-header">
            <h2>Редагування списання</h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти та перепровести"><i class="fas fa-save"></i></button>
            </div>
        </div>
        
        <div class="form-body">
            <div class="form-group-inline">
                <label>Склад списання</label>
                <select name="warehouse_id" class="form-control" required>
                    <?php foreach ($warehouses as $wh): ?>
                        <option value="<?php echo $wh['id']; ?>" <?php echo ($wh['id'] == $document['warehouse_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($wh['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group-inline">
                <label>Коментар</label>
                <input type="text" name="comment" class="form-control" value="<?php echo htmlspecialchars($document['comment']); ?>" required>
            </div>

            <h3 style="margin-top: 2em; margin-bottom: 1em;">Позиції для списання</h3>
            <table class="orders-table">
                </table>
            <button type="button" id="add-item-btn" class="btn-primary" style="margin-top: 1rem;">+ Додати позицію</button>
        </div>
    </form>
</div>