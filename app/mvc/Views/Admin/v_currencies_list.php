<div class="content-card">
    <div class="form-header">
        <h2>Керування валютами</h2>
        <div class="actions-cell" style="gap: 0.5rem;">
            <?php if ($this->hasPermission('currencies', 'e')): ?>
                <button id="update-rates-btn" type="button" class="action-btn" title="Оновити курси з API">
                    <i class="fas fa-sync-alt"></i>
                </button>
            <?php endif; ?>
            <?php if ($this->hasPermission('currencies', 'a')): ?>
                <button id="add-currency-btn" type="button" class="action-btn save" title="Додати валюту">
                    <i class="fas fa-plus"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <table class="orders-table">
        <thead>
            <tr>
                <th>Код валюти</th>
                <th>Банк</th>
                <th>Купівля</th>
                <th>Продаж</th>
                <th>Останнє оновлення</th>
                <th style="width: 100px;">Дії</th>
            </tr>
        </thead>
        <tbody id="currencies-tbody">
            <?php foreach ($currencies as $currency): ?>
                <tr data-id="<?php echo $currency['id']; ?>">
                    <td data-code="<?php echo htmlspecialchars($currency['code']); ?>"><?php echo htmlspecialchars($currency['code']); ?></td>
                    <td><?php echo htmlspecialchars($currency['bank']); ?></td>
                    <td><?php echo htmlspecialchars($currency['rate_buy'] ?? '---'); ?></td>
                    <td><?php echo htmlspecialchars($currency['rate_sale'] ?? '---'); ?></td>
                    <td class="time-ago" data-timestamp="<?php echo $currency['last_updated'] ? gmdate('Y-m-d\TH:i:s\Z', strtotime($currency['last_updated'])) : ''; ?>">
                    <td class="actions-cell">
                        <?php if ($this->hasPermission('currencies', 'd')): ?>
                            <button type="button" class="action-btn delete-currency-btn" title="Видалити">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<template id="currency-row-template">
    <tr data-id="">
        <td><input type="text" class="form-control" name="code" placeholder="Код (USD)"></td>
        <td><input type="text" class="form-control" name="bank" placeholder="Банк (НБУ)"></td>
        <td colspan="3"></td> <td class="actions-cell">
            <button type="button" class="action-btn save-new-currency-btn" title="Зберегти"><i class="fas fa-save"></i></button>
            <button type="button" class="action-btn cancel-new-currency-btn" title="Скасувати"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('currencies-tbody');
    const addBtn = document.getElementById('add-currency-btn');
    const updateBtn = document.getElementById('update-rates-btn');
    const template = document.getElementById('currency-row-template');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const deleteModal = document.getElementById('deleteModalOverlay');
    const modalTitle = deleteModal.querySelector('#modalTitle');
    const modalBody = deleteModal.querySelector('#modalBody');
    const modalConfirmBtn = deleteModal.querySelector('#modalConfirmBtn');

    // Функція для збереження flash-повідомлення перед перезавантаженням
    const setFlashMessage = (type, text) => {
        sessionStorage.setItem('flashMessage', JSON.stringify({ type, text }));
    };

    // Додавання нового рядка
    addBtn?.addEventListener('click', () => {
        const newRow = template.content.cloneNode(true);
        tbody.appendChild(newRow);
    });
    
    // Оновлення курсів
    updateBtn?.addEventListener('click', async () => {
        const icon = updateBtn.querySelector('i');
        icon.classList.add('fa-spin');
        updateBtn.disabled = true;

        try {
            const response = await fetch(`<?php echo BASE_URL; ?>/currencies/update-rates`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const result = await response.json();
            
            setFlashMessage(result.success ? 'success' : 'error', result.message);
            window.location.reload();

        } catch(e) {
            console.error("Помилка оновлення:", e);
            setFlashMessage('error', 'Сталася непередбачувана помилка.');
            window.location.reload();
        }
    });

    // Обробник подій для всієї таблиці (збереження, скасування, видалення)
    tbody.addEventListener('click', async (e) => {
        const target = e.target;
        const row = target.closest('tr');

        // Збереження нового рядка
        if (target.closest('.save-new-currency-btn')) {
            const codeInput = row.querySelector('input[name="code"]');
            const bankInput = row.querySelector('input[name="bank"]');
            
            if (!codeInput.value.trim()) {
                alert('Код валюти не може бути порожнім.');
                return;
            }

            const response = await fetch('<?php echo BASE_URL; ?>/currencies/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ code: codeInput.value, bank: bankInput.value })
            });
            const result = await response.json();

            if (result.success) {
                setFlashMessage('success', 'Валюту успішно додано.');
                window.location.reload();
            } else {
                alert(result.message || 'Помилка збереження.');
            }
        }

        // Скасування додавання
        if (target.closest('.cancel-new-currency-btn')) {
            row.remove();
        }
        
        // Видалення існуючого рядка
        if (target.closest('.delete-currency-btn')) {
            const id = row.dataset.id;
            const code = row.querySelector('td[data-code]').dataset.code;

            modalTitle.textContent = 'Підтвердження видалення';
            modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити валюту <strong>"${code}"</strong>?</p>`;
            deleteModal.style.display = 'flex';

            modalConfirmBtn.addEventListener('click', async function handleDelete() {
                deleteModal.style.display = 'none';

                const response = await fetch(`<?php echo BASE_URL; ?>/currencies/delete/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const result = await response.json();
                
                if (result.success) {
                    row.remove();
                    // Використовуємо глобальну функцію з main.js для показу повідомлення без перезавантаження
                    if (window.showFlashMessage) {
                        window.showFlashMessage('success', 'Валюту успішно видалено.');
                    }
                } else {
                    alert(result.message || 'Помилка видалення.');
                }
            }, { once: true });
        }
    });

    /**
     * Функція для правильної відміни слів в українській мові (1 секунду, 2 секунди, 5 секунд).
     */
    function getUkrainianPlural(number, one, few, many) {
        number = Math.abs(number) % 100;
        if (number % 10 === 1 && number % 100 !== 11) {
            return one;
        }
        if ([2, 3, 4].includes(number % 10) && ![12, 13, 14].includes(number % 100)) {
            return few;
        }
        return many;
    }

    /**
     * Форматує рядок дати у формат "X одиниць тому".
     */
    function formatTimeAgo(dateString) {
        if (!dateString) return 'Ніколи';

        const now = new Date();
        const past = new Date(dateString.replace(' ', 'T')); // Робимо час сумісним з ISO
        const secondsAgo = Math.round((now - past) / 1000);

        if (secondsAgo < 0) return 'Щойно';
        if (secondsAgo < 60) {
            return `${secondsAgo} ${getUkrainianPlural(secondsAgo, 'секунду', 'секунди', 'секунд')} тому`;
        }

        const minutesAgo = Math.floor(secondsAgo / 60);
        if (minutesAgo < 60) {
            return `${minutesAgo} ${getUkrainianPlural(minutesAgo, 'хвилину', 'хвилини', 'хвилин')} тому`;
        }

        const hoursAgo = Math.floor(minutesAgo / 60);
        if (hoursAgo < 24) {
            return `${hoursAgo} ${getUkrainianPlural(hoursAgo, 'годину', 'години', 'годин')} тому`;
        }

        const daysAgo = Math.floor(hoursAgo / 24);
        return `${daysAgo} ${getUkrainianPlural(daysAgo, 'день', 'дні', 'днів')} тому`;
    }

    /**
     * Оновлює текст для всіх елементів з відліком часу.
     */
    function updateTimestamps() {
        const timeElements = document.querySelectorAll('.time-ago');
        timeElements.forEach(el => {
            const timestamp = el.dataset.timestamp;
            if (timestamp) {
                el.textContent = formatTimeAgo(timestamp);
            }
        });
    }

    // Запускаємо оновлення кожні 5 секунд
    setInterval(updateTimestamps, 5000);

    // Оновлюємо одразу після завантаження сторінки
    updateTimestamps();
});
</script>