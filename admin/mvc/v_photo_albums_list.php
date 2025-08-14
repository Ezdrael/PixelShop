<?php
    $breadcrumbs = [['name' => 'Фотоальбоми', 'url' => BASE_URL . '/albums']];
    include '_template_breadcrumbs.php';

    function renderAlbumTree($albums, $controller, $level = 0) {
        echo '<ul class="category-tree level-' . $level . '">';
        foreach ($albums as $album) {
            echo '<li>';
            echo '<div class="category-item">';

            // --- НОВИЙ БЛОК: Зображення обкладинки ---
            echo '<div class="album-list-cover">';
            if (!empty($album['cover_image_filename'])) {
                $cover_url = BASE_URL . '/resources/img/products/' . htmlspecialchars($album['cover_image_filename']);
                echo '<img src="' . $cover_url . '" alt="' . htmlspecialchars($album['name']) . '">';
            } else {
                // Іконка-заглушка, якщо обкладинка не встановлена
                echo '<i class="fas fa-folder"></i>';
            }
            echo '</div>';
            // --- Кінець нового блоку ---

            echo '<span>' . htmlspecialchars($album['name']) 
               . ' <span class="category-id-badge"><i class="fas fa-image"></i> ' . ($album['photo_count'] ?? 0) . '</span>'
               . '</span>';
            
            echo '<div class="actions-cell">';
            if ($controller->hasPermission('albums', 'v')) {
                echo '<a href="' . BASE_URL . '/albums/view/' . $album['id'] . '" class="action-btn" title="Переглянути"><i class="fas fa-eye"></i></a>';
            }
            if ($controller->hasPermission('albums', 'e')) {
                echo '<a href="' . BASE_URL . '/albums/edit/' . $album['id'] . '" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>';
            }
            if ($controller->hasPermission('albums', 'd')) {
                echo '<button type="button" class="action-btn delete-btn" data-entity="albums" data-user-id="' . $album['id'] . '" data-user-name="' . htmlspecialchars($album['name']) . '" title="Видалити"><i class="fas fa-trash"></i></button>';
            }
            echo '</div>';
            echo '</div>';

            if (isset($album['children']) && !empty($album['children'])) {
                renderAlbumTree($album['children'], $controller, $level + 1);
            }
            echo '</li>';
        }
        echo '</ul>';
    }
?>
<div class="content-card">
    <div class="form-header">
        <h2>Фотоальбоми</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('albums', 'a')): ?>
                <a href="<?php echo BASE_URL . '/albums/add'; ?>" class="action-btn save" title="Створити альбом"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($albumsTree)): ?>
        <?php renderAlbumTree($albumsTree, $this); ?>
    <?php else: ?>
        <p class="empty-list-message" style="padding: 1rem;">Ще не створено жодного альбому.</p>
    <?php endif; ?>
</div>


<style>
/* --- Оновлені стилі для дерева альбомів --- */
.category-tree { list-style: none; padding-left: 20px; }
.category-tree .category-tree { margin-top: 10px; }

.category-item { 
    display: flex; 
    /* justify-content: space-between; -- прибираємо, щоб текст був ближче до іконки */
    align-items: center; 
    gap: 1rem; /* Відступ між елементами */
    padding: 10px; 
    border: 1px solid var(--border-color); 
    border-radius: 8px; 
    margin-top: 5px; 
    transition: background-color 0.2s; 
}
.category-item:hover { background-color: var(--sidebar-hover-bg); }

/* Вирівнюємо кнопки дій по правому краю */
.category-item .actions-cell {
    margin-left: auto;
}

/* Нові стилі для обкладинки */
.album-list-cover {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    background-color: var(--sidebar-hover-bg);
    border-radius: 6px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.5rem;
    color: var(--secondary-text);
    overflow: hidden;
}
.album-list-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.empty-list-message { color: var(--secondary-text); font-style: italic; }
</style>