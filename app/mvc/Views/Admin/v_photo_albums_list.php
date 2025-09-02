<?php
    $breadcrumbs = [['name' => 'Фотоальбоми', 'url' => BASE_URL . '/albums', 'id' => 0]];
    include '_template_breadcrumbs.php';

    function renderAlbumTree($albums, $controller, $mAlbums, $level = 0) {
        echo '<ul class="category-tree level-' . $level . '">';
        foreach ($albums as $album) {
            $hasContent = $mAlbums->hasContent($album['id']);

            echo '<li>';
            echo '<div class="category-item">';

            // Блок з обкладинкою
            echo '<div class="album-list-cover">';
            if (!empty($album['cover_image_filename'])) {
                $cover_url = PROJECT_URL . '/resources/img/products/' . htmlspecialchars($album['cover_image_filename']);
                echo '<img src="' . $cover_url . '" alt="' . htmlspecialchars($album['name']) . '">';
            } else {
                echo '<i class="fas fa-folder"></i>';
            }
            echo '</div>';

            // Назва та лічильник фото
            echo '<span>' . htmlspecialchars($album['name'])
               . ' <span class="category-id-badge"><i class="fas fa-image"></i> ' . ($album['photo_count'] ?? 0) . '</span>'
               . '</span>';

            echo '<div class="actions-cell">';
            if ($controller->hasPermission('albums', 'a')) { 
                echo '<a href="' . BASE_URL . '/albums/upload/' . $album['id'] . '" class="action-btn save" title="Завантажити фото"><i class="fas fa-upload"></i></a>';
            }
            if ($controller->hasPermission('albums', 'v')) {
                echo '<a href="' . BASE_URL . '/albums/view/' . $album['id'] . '" class="action-btn" title="Переглянути"><i class="fas fa-eye"></i></a>';
            }
            if ($controller->hasPermission('albums', 'e')) {
                echo '<a href="' . BASE_URL . '/albums/edit/' . $album['id'] . '" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>';
            }
            if ($controller->hasPermission('albums', 'd')) {
                $isAlbumEmpty = empty($album['photo_count']) && empty($album['children']);
                echo '<button type="button" 
                            class="action-btn delete-album-btn" 
                            data-album-id="' . $album['id'] . '" 
                            data-album-name="' . htmlspecialchars($album['name']) . '" 
                            data-is-empty="' . ($isAlbumEmpty ? 'true' : 'false') . '" 
                            title="Видалити">
                        <i class="fas fa-trash-alt"></i>
                    </button>';
            }
            echo '</div>';
            echo '</div>';

            if (isset($album['children']) && !empty($album['children'])) {
                // ВИПРАВЛЕНО РЕКУРСИВНИЙ ВИКЛИК
                renderAlbumTree($album['children'], $controller, $mAlbums, $level + 1);
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
                <a id="create-album-btn" href="<?php echo BASE_URL . '/albums/add'; ?>" class="action-btn save" title="Створити альбом [Alt+=]"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($albumsTree)): ?>
        <?php // ВИПРАВЛЕНО ПОЧАТКОВИЙ ВИКЛИК
              renderAlbumTree($albumsTree, $this, $this->mAlbums);
        ?>
    <?php else: ?>
        <p class="empty-list-message" style="padding: 1rem;">Ще не створено жодного альбому.</p>
    <?php endif; ?>
</div>

<script type="module" src="<?php echo BASE_URL; ?>/resources/js/photo-album-list.js"></script>