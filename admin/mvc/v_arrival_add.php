<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2><?php echo $this->title; ?></h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Провести надходження"><i class="fas fa-save"></i></button>
            </div>
        </div>
        
        <div class="form-body">
            <div class="form-group-inline">
                <label for="arrival-datetime">Дата та час<span class="required-field">*</span></label>
                <div class="form-control-wrapper">
                    <input type="datetime-local" id="arrival-datetime" name="arrival_datetime" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <h3>Товари</h3>
            <table class="orders-table">
                <thead><tr><th>Товар</th><th>Склад</th><th>Кількість</th><th></th></tr></thead>
                <tbody id="arrival-items-body"></tbody>
            </table>
            <button type="button" id="add-item-btn" class="btn-primary" style="margin-top: 1rem;"><i class="fas fa-plus"></i> Додати товар</button>
        </div>
    </form>
</div>

<template id="arrival-item-template">
    <tr>
        <td>
            <select name="good_id[]" class="form-control" required>
                <option value="">-- Виберіть товар --</option>
                <?php foreach($goods as $g): ?>
                <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="warehouse_id[]" class="form-control" required>
                <option value="">-- Виберіть склад --</option>
                <?php foreach($warehouses as $w): ?>
                <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" name="quantity[]" class="form-control" value="1" min="1" required step="any">
        </td>
        <td class="actions-cell">
            <button type="button" class="action-btn remove-item-btn" title="Видалити рядок"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('add-item-btn');
    const tbody = document.getElementById('arrival-items-body');
    const template = document.getElementById('arrival-item-template');
    
    const addRow = () => { 
        if(template) {
            tbody.appendChild(template.content.cloneNode(true)); 
        }
    };
    
    // Додаємо один рядок одразу при завантаженні
    addRow();

    addBtn.addEventListener('click', addRow);
    tbody.addEventListener('click', (e) => {
        if (e.target && e.target.closest('.remove-item-btn')) { 
            e.target.closest('tr').remove(); 
        }
    });
});
</script>