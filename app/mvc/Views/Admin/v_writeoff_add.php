<div class="content-card">
    <form action="" method="POST" id="writeoff-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2>Нове списання</h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Провести списання"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/writeoffs" class="action-btn" title="До списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        
        <div class="form-body">
            <div class="form-group-inline">
                <label>Дата та час <span class="required-field">*</span></label>
                <input type="datetime-local" name="date" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            <div class="form-group-inline">
                <label>Причина списання (коментар)</label>
                <textarea name="comment" class="form-control" rows="3" required></textarea>
            </div>

            <h3 style="margin-top: 2em; margin-bottom: 1em;">Позиції для списання</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Склад</th>
                        <th>Кількість</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="writeoff-items-tbody">
                    </tbody>
            </table>
        </div>
        
        <div class="form-footer">
            <br><button type="button" id="add-item-btn" class="btn-primary"><i class="fas fa-plus"></i> Додати позицію</button>
        </div>
    </form>
</div>

<template id="item-row-template">
    <tr>
        <td>
            <select name="goods[][id]" class="form-control item-select-good" required>
                <option value="">-- Оберіть товар --</option>
                <?php foreach ($goods as $good): ?>
                    <option value="<?php echo $good['id']; ?>"><?php echo htmlspecialchars($good['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="goods[][warehouse_id]" class="form-control item-select-warehouse" required disabled>
                <option value="">-- Спочатку оберіть товар --</option>
            </select>
        </td>
        <td><input type="number" name="goods[][quantity]" class="form-control item-quantity" value="" min="0.001" step="0.001" required disabled></td>
        <td><button type="button" class="action-btn delete remove-item-btn" title="Видалити"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('writeoff-form');
    const tbody = document.getElementById('writeoff-items-tbody');
    const addBtn = document.getElementById('add-item-btn');
    const template = document.getElementById('item-row-template');
    const sessionStorageKey = 'writeoffFormData';

    // --- 1. ЛОГІКА ЗБЕРЕЖЕННЯ ---
    const saveFormState = () => {
        const items = [];
        tbody.querySelectorAll('tr').forEach(row => {
            const goodSelect = row.querySelector('.item-select-good');
            const warehouseSelect = row.querySelector('.item-select-warehouse');
            const quantityInput = row.querySelector('.item-quantity');
            
            items.push({
                good_id: goodSelect.value,
                warehouse_id: warehouseSelect.value,
                quantity: quantityInput.value,
                warehouses_html: warehouseSelect.innerHTML, // Зберігаємо HTML складів
                quantity_placeholder: quantityInput.placeholder,
                warehouse_disabled: warehouseSelect.disabled,
                quantity_disabled: quantityInput.disabled,
            });
        });
        
        const formData = {
            date: form.elements['date'].value,
            comment: form.elements['comment'].value,
            items: items
        };
        sessionStorage.setItem(sessionStorageKey, JSON.stringify(formData));
    };

    // --- 2. ЛОГІКА ВІДНОВЛЕННЯ ---
    const restoreFormState = () => {
        const savedData = sessionStorage.getItem(sessionStorageKey);
        if (!savedData) {
            addRow(); // Якщо немає збережених даних, додаємо один порожній рядок
            return;
        }

        const data = JSON.parse(savedData);
        form.elements['date'].value = data.date;
        form.elements['comment'].value = data.comment;
        tbody.innerHTML = ''; // Очищуємо перед відновленням

        if (data.items.length > 0) {
            data.items.forEach(item => {
                const newRow = template.content.cloneNode(true);
                const goodSelect = newRow.querySelector('.item-select-good');
                const warehouseSelect = newRow.querySelector('.item-select-warehouse');
                const quantityInput = newRow.querySelector('.item-quantity');
                
                goodSelect.value = item.good_id;
                warehouseSelect.innerHTML = item.warehouses_html;
                warehouseSelect.value = item.warehouse_id;
                warehouseSelect.disabled = item.warehouse_disabled;
                quantityInput.value = item.quantity;
                quantityInput.placeholder = item.quantity_placeholder;
                quantityInput.disabled = item.quantity_disabled;

                tbody.appendChild(newRow);
            });
        } else {
            addRow(); // Якщо збережено порожній документ, додаємо один рядок
        }
    };

    // --- 3. ІНША ЛОГІКА (завантаження складів, додавання/видалення рядків) ---
    const fetchWarehousesForGood = async (goodId, warehouseSelect, quantityInput) => {
        // ... (код цієї функції без змін, як у попередній відповіді) ...
    };

    const addRow = () => {
        tbody.appendChild(template.content.cloneNode(true));
        saveFormState();
    };

    // --- 4. ПРИВ'ЯЗКА ПОДІЙ ---
    form.addEventListener('input', saveFormState); // Зберігаємо при будь-якій зміні
    form.addEventListener('submit', () => {
        sessionStorage.removeItem(sessionStorageKey); // Очищуємо при успішній відправці
    });

    addBtn.addEventListener('click', addRow);

    tbody.addEventListener('change', async (e) => {
        const target = e.target;
        const row = target.closest('tr');
        
        if (target.classList.contains('item-select-good')) {
            const goodId = target.value;
            const warehouseSelect = row.querySelector('.item-select-warehouse');
            const quantityInput = row.querySelector('.item-quantity');
            
            quantityInput.value = ''; quantityInput.placeholder = ''; quantityInput.disabled = true;
            warehouseSelect.innerHTML = '<option value="">-- Оберіть товар --</option>'; warehouseSelect.disabled = true;

            if (goodId) {
                await fetchWarehousesForGood(goodId, warehouseSelect, quantityInput);
            }
        }

        if (target.classList.contains('item-select-warehouse')) {
            const quantityInput = row.querySelector('.item-quantity');
            const selectedOption = target.options[target.selectedIndex];
            const maxQuantity = selectedOption.dataset.quantity;
            
            if (maxQuantity) {
                quantityInput.disabled = false;
                quantityInput.value = '';
                quantityInput.placeholder = `Макс: ${maxQuantity}`;
                quantityInput.max = maxQuantity;
            } else {
                quantityInput.disabled = true;
                quantityInput.value = '';
                quantityInput.placeholder = '';
            }
        }
    });

    tbody.addEventListener('click', e => {
        if (e.target.closest('.remove-item-btn')) {
            e.target.closest('tr').remove();
            saveFormState(); // Зберігаємо стан після видалення рядка
        }
    });

    // --- ЗАПУСК ---
    restoreFormState();
});
</script>