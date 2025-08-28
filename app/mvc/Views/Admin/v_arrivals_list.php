<div class="content-card">
    <div class="form-header">
        <h2>Історія надходжень</h2>
        <div class="actions-cell">
            <button id="toggle-filter-btn" type="button" class="action-btn" title="Пошук та фільтрація">
                <i class="fas fa-search"></i>
            </button>
             <?php if ($this->hasPermission('arrivals', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/arrivals/add" class="action-btn save" title="Нове надходження"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="filter-container" class="filter-bar-hidden">
        <div class="filter-bar">
            <form id="filter-form" action="" method="GET">
                <div class="filter-group">
                    <input type="text" name="date_from" class="form-control date-picker" placeholder="Дата від" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                    <input type="text" name="date_to" class="form-control date-picker" placeholder="Дата до" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                </div>
                <div class="filter-group">
                    <input type="text" name="document_id" class="form-control" placeholder="Номер документа" value="<?php echo htmlspecialchars($filters['document_id'] ?? ''); ?>">
                    <select name="user_id" class="form-control">
                        <option value="">Всі користувачі</option>
                        <?php foreach($usersList as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php if (isset($filters['user_id']) && $filters['user_id'] == $user['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <input type="text" name="positions" class="form-control" placeholder="Пошук в позиціях..." value="<?php echo htmlspecialchars($filters['positions'] ?? ''); ?>" style="flex-grow: 2;">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Пошук</button>
                    <a href="<?php echo BASE_URL; ?>/arrivals" class="btn-secondary">
                        <i class="fas fa-times"></i> Скинути
                    </a>
                </div>
            </form>
        </div>
    </div>

    <table class="orders-table">
        <thead>
            <tr>
                <th>Дата та час</th>
                <th>Документ</th>
                <th>Користувач</th>
                <th>Позиції</th>
                <th>Статус</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($arrivalsList)): foreach ($arrivalsList as $arrival): ?>
                <?php
                    $status = $arrival['status'] ?? 'completed';
                    $rowClass = '';
                    if ($status === 'canceled') {
                        $rowClass = 'canceled-operation';
                    } elseif ($status === 'edited') {
                        $rowClass = 'edited-operation'; // Можна додати свій стиль для відредагованих
                    }
                ?>
                <tr class="<?php echo $rowClass; ?>">
                    <td><?php echo date('d.m.Y H:i', strtotime($arrival['transaction_date'])); ?></td>
                    <td><?php echo htmlspecialchars($arrival['document_id']); ?></td>
                    <td><?php echo htmlspecialchars($arrival['user_name'] ?? 'N/A'); ?></td>
                    <td class="positions-cell"><?php echo $arrival['positions_summary']; ?></td>
                    <td>
                        <?php if ($status === 'canceled'): ?>
                            <span class="status-badge canceled">Скасовано</span>
                        <?php elseif ($status === 'edited'): ?>
                            <span class="status-badge inactive">Відредаговано</span>
                        <?php else: ?>
                            <span class="status-badge completed">Проведено</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="<?php echo BASE_URL; ?>/arrivals/watch/<?php echo urlencode($arrival['document_id']); ?>" class="action-btn" title="Переглянути">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        <?php if ($this->hasPermission('arrivals', 'e') && $status === 'completed'): ?>
                            <a href="<?php echo BASE_URL; ?>/arrivals/edit/<?php echo urlencode($arrival['document_id']); ?>" class="action-btn" title="Редагувати">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        <?php endif; ?>

                        <?php if ($this->hasPermission('arrivals', 'd') && $status === 'completed'): ?>
                            <button type="button" class="action-btn delete-btn" 
                                    data-entity="arrivals" 
                                    data-id="<?php echo htmlspecialchars($arrival['document_id']); ?>" 
                                    data-name="<?php echo htmlspecialchars($arrival['document_id']); ?>" 
                                    title="Видалити (Скасувати)">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align: center;">Історія надходжень порожня.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<style>
    .filter-bar { background-color: #f9fafb; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 1.5rem; }
    .filter-bar form { display: flex; flex-wrap: wrap; gap: 1rem; }
    .filter-group, .filter-actions { display: flex; gap: 1rem; flex-grow: 1; }
    .filter-group .form-control { min-width: 150px; }
    .btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.7rem 1.2rem; font-size: 0.95rem; font-weight: 500; color: var(--primary-text); background-color: #e2e8f0; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; transition: background-color 0.2s; }
    .btn-secondary:hover { background-color: #cbd5e1; }
    
    /* Стилі для прихованого блоку */
    .filter-bar-hidden {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out, padding 0.4s ease-out;
        padding: 0;
    }
    .filter-bar-visible {
        max-height: 500px; /* достатньо велике значення */
        padding-bottom: 1.5rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Ініціалізація віджетів для вибору дати
    flatpickr(".date-picker", {
        dateFormat: "Y-m-d",
    });

    // Логіка для прихованого блоку пошуку
    const toggleBtn = document.getElementById('toggle-filter-btn');
    const filterContainer = document.getElementById('filter-container');
    const filterForm = document.getElementById('filter-form');
    
    if (toggleBtn && filterContainer) {
        // Якщо URL містить параметри пошуку, показуємо блок одразу
        if (window.location.pathname.includes('/search/')) {
            filterContainer.classList.add('filter-bar-visible');
        }

        toggleBtn.addEventListener('click', () => {
            filterContainer.classList.toggle('filter-bar-visible');
        });
    }

    // !! КЛЮЧОВА ЛОГІКА: Перехоплення відправки форми !!
    if (filterForm) {
        filterForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Зупиняємо стандартну відправку

            const formData = new FormData(this);
            const params = [];

            // Формуємо рядок параметрів "ключ:значення"
            for (const [key, value] of formData.entries()) {
                if (value) { // Додаємо тільки заповнені поля
                    params.push(`${encodeURIComponent(key)}:${encodeURIComponent(value)}`);
                }
            }

            // Якщо параметри є, перенаправляємо на новий URL
            if (params.length > 0) {
                const searchUrl = `<?php echo BASE_URL; ?>/arrivals/search/${params.join('/')}`;
                window.location.href = searchUrl;
            } else {
                // Якщо фільтри порожні, просто переходимо на головну сторінку списку
                window.location.href = `<?php echo BASE_URL; ?>/arrivals`;
            }
        });
    }
});
</script>