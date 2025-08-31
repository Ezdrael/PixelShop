<?php
    //Підключаємо специфічні стилі та скрипти для цієї сторінки
    $this->addCSS(PROJECT_URL . '/resources/css/admin/photo-album.css');
    $this->addJS(PROJECT_URL . '/resources/js/photo-album-view.js');

    $breadcrumbs = [ ['name' => 'Фотоальбоми', 'url' => BASE_URL . '/albums'] ];
    if (isset($ancestors)) {
        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [ 'name' => $ancestor['name'], 'url' => BASE_URL . '/albums/view/' . $ancestor['id'] ];
        }
    }
    if (isset($album)) {
        $breadcrumbs[] = ['name' => $album['name'], 'url' => BASE_URL . '/albums/view/' . $album['id']];
    }

    include '_template_breadcrumbs.php';
?>

<div class="content-card">
    <div class="form-header">
        <div>
            <h2>Альбом: <?php echo htmlspecialchars($album['name']); ?></h2>
        </div>
        <div class="actions-cell">
            <?php if ($this->hasPermission('albums', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/albums/upload/<?php echo $album['id']; ?>" class="action-btn save" title="Завантажити фото"><i class="fas fa-upload"></i></a>
            <?php endif; ?>
            <?php if ($this->hasPermission('albums', 'e')): ?>
                <a href="<?php echo BASE_URL; ?>/albums/edit/<?php echo $album['id']; ?>" class="action-btn" title="Редагувати альбом"><i class="fas fa-pencil-alt"></i></a>
            <?php endif; ?>
            
            <?php if ($this->hasPermission('albums', 'd')): ?>
            <?php $isAlbumEmpty = empty($photos) && empty($children); ?>
            <button class="action-btn delete delete-album-btn"
                    title="Видалити альбом"
                    data-album-id="<?php echo $album['id']; ?>"
                    data-album-name="<?php echo htmlspecialchars($album['name']); ?>"
                    data-is-empty="<?php echo $isAlbumEmpty ? 'true' : 'false'; ?>">
                <i class="fas fa-trash"></i>
            </button>
        <?php endif; ?>

            <a href="<?php echo BASE_URL; ?>/albums" class="action-btn" title="До списку альбомів"><i class="fas fa-arrow-left"></i></a>
        </div>
    </div>

    <p><?php echo htmlspecialchars($album['description']); ?></p>
    
    <?php if (!empty($children)): ?>
    <div class="info-section">
        <h3><i class="fas fa-folder"></i> Вкладені альбоми</h3>
        <div class="album-gallery">
            <?php foreach ($children as $child): ?>
                <a href="<?php echo BASE_URL . '/albums/view/' . $child['id']; ?>" class="album-thumbnail">
                    <div class="album-cover-preview">
                        <?php if (!empty($child['cover_image_filename'])): ?>
                            <img src="<?php echo PROJECT_URL . '/resources/img/products/' . htmlspecialchars($child['cover_image_filename']); ?>" alt="<?php echo htmlspecialchars($child['name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-folder-open"></i>
                        <?php endif; ?>
                    </div>
                    <div class="album-name"><?php echo htmlspecialchars($child['name']); ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="info-section">
        <div class="photo-gallery-header">
            <h3><i class="fas fa-images"></i> Фотографії в цьому альбомі</h3>
            <div class="selection-controls">
                <div id="dynamic-selection-actions">
                    <span id="selection-counter-badge" class="selection-counter-badge">0</span>
                    <button id="select-all-btn" class="action-btn" title="Виділити всі"><i class="fas fa-check-double"></i></button>
                    <button id="move-selected-btn" class="action-btn" title="Перемістити вибрані"><i class="fas fa-folder-open"></i></button>
                    <button id="delete-selected-btn" class="action-btn delete" title="Видалити вибрані"><i class="fas fa-trash"></i></button>
                </div>
                <button id="toggle-selection-mode" class="btn-primary"><i class="fas fa-check-square"></i> Вибрати</button>
            </div>
        </div>

        <div id="lightgallery-container" 
             class="photo-gallery" 
             data-album-id="<?php echo $album['id']; ?>" 
             data-csrf-token="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

            <?php if (!empty($photos)): foreach ($photos as $photo): ?>
                <div class="gallery-item-wrapper" data-photo-id="<?php echo $photo['id']; ?>">
                    <label class="photo-selection-label">
                        <input type="checkbox" class="photo-selection-checkbox" data-photo-id="<?php echo $photo['id']; ?>">
                        <span class="custom-checkbox"></span>
                    </label>
                    <a  class="gallery-item"
                        href="<?php echo PROJECT_URL . '/resources/img/products/' . htmlspecialchars($photo['filename']); ?>"
                        data-sub-html="<h4><?php echo htmlspecialchars($photo['note']); ?></h4>"
                        data-photo-id="<?php echo $photo['id']; ?>"
                        data-album-id="<?php echo $album['id']; ?>"
                        data-filename="<?php echo htmlspecialchars($photo['filename']); ?>">
                        <img src="<?php echo PROJECT_URL . '/resources/img/products/' . htmlspecialchars($photo['filename']); ?>" alt="Фотографія" />
                    </a>
                </div>
            <?php endforeach; else: ?>
                <p class="empty-list-message">В цьому альбомі ще немає фотографій.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="module" src="<?php echo BASE_URL; ?>/resources/js/photo-album-view.js"></script>

