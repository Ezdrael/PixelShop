<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2><?php echo $this->title; ?></h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Провести переміщення"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/transfers" class="action-btn" title="До списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        
        <div class="form-body">
            <div class="form-group-inline">
                <label for="order-date">Дата та час<span class="required-field">*</span></label>
                <input type="datetime-local" id="order-date" name="order_date" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            <div class="form-group-inline">
                <label for="comment">Коментар</label>
                <textarea id="comment" name="comment" class="form-control" rows="3"></textarea>
            </div>
        </div>
        
        <div class="info-section">
            <h3>Товари для переміщення</h3>
            <table class="orders-table">
                <thead><tr><th>Товар</th><th>Зі складу</th><th>На склад</th><th>Кількість</th><th></th></tr></thead>
                <tbody id="transfer-items-body"></tbody>
            </table>
            <button type="button" id="add-item-btn" class="btn-primary" style="margin-top: 1rem;"><i class="fas fa-plus"></i> Додати позицію</button>
        </div>
    </form>
</div>

<template id="transfer-item-template">
    <tr>
        <td>
            <select name="good_id[]" class="form-control" required>
                <option value="">-- Виберіть товар --</option>
                <?php foreach($goods as $g): ?><option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option><?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="from_warehouse_id[]" class="form-control" required>
                <option value="">-- Зі складу --</option>
                <?php foreach($warehouses as $w): ?><option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option><?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="to_warehouse_id[]" class="form-control" required>
                <option value="">-- На склад --</option>
                <?php foreach($warehouses as $w): ?><option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option><?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" name="quantity[]" class="form-control" value="1" min="0.001" step="0.001" required></td>
        <td class="actions-cell"><button type="button" class="action-btn remove-item-btn" title="Видалити"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // JS-код аналогічний тому, що у v_arrival_add.php
    const addBtn = document.getElementById('add-item-btn');
    const tbody = document.getElementById('transfer-items-body');
    const template = document.getElementById('transfer-item-template');
    const addRow = () => { tbody.appendChild(template.content.cloneNode(true)); };
    addRow(); // Додаємо один рядок одразу
    addBtn.addEventListener('click', addRow);
    tbody.addEventListener('click', e => {
        if (e.target.closest('.remove-item-btn')) e.target.closest('tr').remove();
    });
});
</script>