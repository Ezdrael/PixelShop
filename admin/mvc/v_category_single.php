<?php
// --- Нова логіка для визначення фінального статусу ---
$finalStatusHtml = '';
$inactiveAncestor = null;

// Шукаємо першого неактивного предка
if (isset($ancestors) && is_array($ancestors)) {
    foreach ($ancestors as $ancestor) {
        if (empty($ancestor['is_active'])) {
            $inactiveAncestor = $ancestor;
            break; // Знайшли, далі шукати не потрібно
        }
    }
}

if ($inactiveAncestor) {
    // Якщо знайдено неактивного предка
    $link = BASE_URL . '/categories/watch/' . $inactiveAncestor['id'];
    $name = htmlspecialchars($inactiveAncestor['name']);
    $finalStatusHtml = '<span class="status-badge-warning">Неактивна через <a href="' . $link . '">' . $name . '</a></span>';

} elseif (isset($category) && empty($category['is_active'])) {
    // Якщо сама категорія неактивна
    $finalStatusHtml = '<span class="status-badge inactive">Вимкнена</span>';
    
} elseif (isset($category)) {
    // Якщо все активно
    $finalStatusHtml = '<span class="status-badge active">Увімкнена</span>';
}
?>

<div class="content-card">
    <?php if ($category): ?>
        <div class="form-header">
            <div>
                <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                <p class="user-id-text"><strong>ID:</strong> #<?php echo htmlspecialchars($category['id']); ?></p>
            </div>
            <div class="actions-cell">
                <?php if ($this->hasPermission('categories', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/categories/edit/<?php echo htmlspecialchars($category['id']); ?>" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/categories" class="action-btn" title="До списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Основна інформація</h3>
                <div class="info-card-body">
                    <p>
                        <strong>Статус:</strong>
                        <?php echo $finalStatusHtml; ?>
                    </p>
                    <p>
                        <strong>Батьківська категорія:</strong>
                        <?php if ($category['parent_id']): ?>
                            <a href="<?php echo BASE_URL; ?>/categories/watch/<?php echo $category['parent_id']; ?>" class="styled-link"><?php echo htmlspecialchars($category['parent_name']); ?></a>
                        <?php else: ?>
                            <span>Немає</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="info-card">
                <h3><i class="fas fa-sitemap"></i> Дочірні категорії</h3>
                <div class="info-card-body">
                    <?php if (!empty($children)): ?>
                        <ul class="styled-list">
                            <?php foreach ($children as $child): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/categories/watch/<?php echo $child['id']; ?>" 
                                    class="styled-link <?php if (empty($child['is_active'])) echo 'inactive-good'; ?>"
                                    title="<?php if (empty($child['is_active'])) echo 'Категорія вимкнена'; ?>">
                                        <?php echo htmlspecialchars($child['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-list-message">Підкатегорії відсутні.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3><i class="fas fa-box-open"></i> Товари в цій категорії та її нащадках</h3>
            <?php if (!empty($goods)): ?>
                <table class="orders-table">
                    <thead>
                        <tr><th>ID</th><th>Назва товару</th><th>Ціна</th><th>Дії</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($goods as $good): ?>
                        <tr>
                            <td>#<?php echo $good['id']; ?></td>
                            <td>
                                <?php if (!$good['is_active']): ?>
                                    <span class="inactive-good" title="Товар вимкнено"><?php echo htmlspecialchars($good['name']); ?></span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($good['name']); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(number_format($good['price'], 2, '.', ' ')); ?> грн</td>
                            <td class="actions-cell">
                                <a href="<?php echo BASE_URL; ?>/goods/watch/<?php echo $good['id']; ?>" class="action-btn" title="Переглянути товар"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-list-message">Товари в цій категорії та її нащадках відсутні.</p>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <h2>Категорію не знайдено</h2>
    <?php endif; ?>
</div>

<style>
    /*
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
    .info-card { background-color: #f9fafb; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; }
    .info-card h3 { display: flex; align-items: center; gap: 0.75rem; font-size: 1.1rem; margin-bottom: 1rem; color: var(--primary-text); border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; }
    .info-card-body p { line-height: 1.6; display:flex; justify-content: space-between; align-items: center; }
    .styled-list { list-style: none; padding: 0; }
    .styled-list li { padding: 0.5rem 0; border-bottom: 1px solid var(--border-color); }
    .styled-list li:last-child { border-bottom: none; }
    .styled-link { color: var(--accent-color); text-decoration: none; font-weight: 500; transition: color 0.2s; }
    .styled-link:hover { color: var(--primary-text); text-decoration: underline; }
    .empty-list-message { color: var(--secondary-text); font-style: italic; }
    .inactive-good { color: var(--secondary-text); font-style: italic; text-decoration: line-through; }
    .info-section { margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); }
    .info-section h3 { display: flex; align-items: center; gap: 0.75rem; font-size: 1.2rem; margin-bottom: 1.5rem; }

.status-badge-warning {
    display: inline-block;
    padding: 3px 10px;
    font-size: 0.8em;
    font-weight: 600;
    border-radius: 12px;
    color: #fff;
    line-height: 1.5;
    background-color: var(--danger-color); 
}

.status-badge-warning a {
    color: #fff;
    font-weight: 700;
    text-decoration: underline;
}

.status-badge-warning a:hover {
    opacity: 0.9;
}*/

</style>