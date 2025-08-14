<?php
// ===================================================================
// Файл: mvc/v_categories_list.php (Оновлений)
// ===================================================================

/**
 * Рекурсивна функція для відображення дерева категорій.
 *
 * @param array $categories Масив категорій для поточного рівня
 * @param object $controller Екземпляр контролера для перевірки прав
 * @param int $level Рівень вкладеності
 * @param bool $isParentInactive Прапор, що вказує, чи є батьківська категорія неактивною
 */
function renderCategoryTree($categories, $controller, $level = 0, $isParentInactive = false) {
    echo '<ul class="category-tree level-' . $level . '">';
    foreach ($categories as $category) {
        
        // --- ОСНОВНА ЗМІНА ЛОГІКИ ---
        // Категорія вважається неактивною, якщо вона сама вимкнена АБО її батько вимкнений.
        $isInactive = $isParentInactive || empty($category['is_active']);
        
        $inactiveClass = $isInactive ? 'inactive-category' : '';

        echo '<li class="' . $inactiveClass . '">';
        echo '<div class="category-item">';
        echo '<span>' . htmlspecialchars($category['name']) 
           . ' <span class="category-id-badge"><b>ID</b> ' . $category['id'] . '</span>'
           . ' <span class="category-id-badge"><i class="fas fa-box-open"></i> ' . ($category['goods_count'] ?? 0) . '</span>'
           . '</span>';
        
        // Додаємо текстову мітку, якщо категорія вимкнена
        if ($isInactive) {
            echo ' <span class="inactive-label">Вимкнено</span>';
        }

        echo '<div class="actions-cell">';
        if ($controller->hasPermission('categories', 'v')) {
            echo '<a href="' . BASE_URL . '/categories/watch/' . $category['id'] . '" class="action-btn" title="Переглянути"><i class="fas fa-eye"></i></a>';
        }
        if ($controller->hasPermission('categories', 'e')) {
            echo '<a href="' . BASE_URL . '/categories/edit/' . $category['id'] . '" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>';
        }
        if ($controller->hasPermission('categories', 'd')) {
            echo '<button type="button" class="action-btn delete-btn" data-entity="categories" data-user-id="' . htmlspecialchars($category['id']) . '" data-user-name="' . htmlspecialchars($category['name']) . '" title="Видалити"><i class="fas fa-trash"></i></button>';
        }
        echo '</div>';
        echo '</div>';

        if (isset($category['children'])) {
            // Передаємо поточний статус неактивності $isInactive у рекурсивний виклик
            renderCategoryTree($category['children'], $controller, $level + 1, $isInactive);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<div class="content-card">
    <div class="form-header">
        <h2>Всі категорії</h2>
        <div class="actions-cell">
             <?php if ($controller->hasPermission('categories', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/categories/add" class="action-btn save" title="Додати категорію">
                    <i class="fas fa-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php 
    renderCategoryTree($categoriesTree, $this); 
    ?>
</div>
