<?php
    // --- Формуємо масив для хлібних крихт ---
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
            <p><?php echo htmlspecialchars($album['description']); ?></p>
        </div>
        <div class="actions-cell">
            <?php if ($this->hasPermission('albums', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/albums/upload/<?php echo $album['id']; ?>" class="action-btn save" title="Завантажити фото"><i class="fas fa-upload"></i></a>
            <?php endif; ?>
            <?php if ($this->hasPermission('albums', 'e')): ?>
                <a href="<?php echo BASE_URL; ?>/albums/edit/<?php echo $album['id']; ?>" class="action-btn" title="Редагувати альбом"><i class="fas fa-pencil-alt"></i></a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/albums" class="action-btn" title="До списку альбомів"><i class="fas fa-arrow-left"></i></a>
        </div>
    </div>
    
    <?php if (!empty($children)): ?>
    <div class="info-section">
        <h3><i class="fas fa-folder"></i> Вкладені альбоми</h3>
        <div class="album-gallery">
            <?php foreach ($children as $child): ?>
                <a href="<?php echo BASE_URL . '/albums/view/' . $child['id']; ?>" class="album-thumbnail">
                    <div class="album-cover-preview">
                        <?php if (!empty($child['cover_image_filename'])): ?>
                            <img src="<?php echo BASE_URL . '/resources/img/products/' . htmlspecialchars($child['cover_image_filename']); ?>" alt="<?php echo htmlspecialchars($child['name']); ?>">
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
                <button id="toggle-selection-mode" class="btn-primary"><i class="fas fa-check-square"></i> Вибрати</button>
            </div>
        </div>
        
        <div id="selection-actions-bar" class="selection-actions-bar">
            <span>Вибрано: <span id="selection-counter">0</span> фото</span>
            <div class="actions-cell">
                <button class="action-btn" title="Перемістити вибрані"><i class="fas fa-folder-open"></i></button>
                <button class="action-btn" title="Видалити вибрані"><i class="fas fa-trash"></i></button>
            </div>
        </div>

        <div id="lightgallery-container" class="photo-gallery">
            <?php if (!empty($photos)): foreach ($photos as $photo): ?>
                <div class="gallery-item-wrapper">
                    <input type="checkbox" class="photo-selection-checkbox" data-photo-id="<?php echo $photo['id']; ?>">
                    <a  class="gallery-item"
                        href="<?php echo BASE_URL . '/resources/img/products/' . htmlspecialchars($photo['filename']); ?>"
                        data-sub-html="<h4><?php echo htmlspecialchars($photo['note']); ?></h4>"
                        data-photo-id="<?php echo $photo['id']; ?>"
                        data-filename="<?php echo htmlspecialchars($photo['filename']); ?>">
                        <img src="<?php echo BASE_URL . '/resources/img/products/' . htmlspecialchars($photo['filename']); ?>" />
                    </a>
                </div>
            <?php endforeach; else: ?>
                <p class="empty-list-message">В цьому альбомі ще немає фотографій.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* ... ваші існуючі стилі ... */
.photo-gallery-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.selection-actions-bar { display: none; justify-content: space-between; align-items: center; background-color: var(--sidebar-hover-bg); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
.selection-actions-bar.visible { display: flex; }
.gallery-item-wrapper { position: relative; }
.photo-selection-checkbox { position: absolute; top: 10px; left: 10px; width: 20px; height: 20px; z-index: 10; display: none; }
.selection-mode .photo-selection-checkbox { display: block; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const galleryContainer = document.getElementById('lightgallery-container');
    if (!galleryContainer) return;

    // --- ЛОГІКА ВИБОРУ ФОТО ---
    const toggleSelectionBtn = document.getElementById('toggle-selection-mode');
    const selectionActionsBar = document.getElementById('selection-actions-bar');
    const selectionCounter = document.getElementById('selection-counter');
    const checkboxes = galleryContainer.querySelectorAll('.photo-selection-checkbox');

    toggleSelectionBtn.addEventListener('click', () => {
        galleryContainer.classList.toggle('selection-mode');
        const inSelectionMode = galleryContainer.classList.contains('selection-mode');
        toggleSelectionBtn.innerHTML = inSelectionMode ? '<i class="fas fa-times"></i> Скасувати' : '<i class="fas fa-check-square"></i> Вибрати';
        if (!inSelectionMode) {
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectionActions();
        }
    });

    const updateSelectionActions = () => {
        const selectedCount = galleryContainer.querySelectorAll('.photo-selection-checkbox:checked').length;
        selectionCounter.textContent = selectedCount;
        if (selectedCount > 0) {
            selectionActionsBar.classList.add('visible');
        } else {
            selectionActionsBar.classList.remove('visible');
        }
    };

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionActions);
    });

    // --- ЛОГІКА LIGHTGALLERY ---
    const lg = lightGallery(galleryContainer, {
        selector: '.gallery-item', // Важливо: клікати треба на посилання, а не на обгортку
        // ... (решта налаштувань lightGallery з попередньої відповіді) ...
    });

    // ... (решта JS-коду для кнопок в лайтбоксі та оновлення після видалення) ...
});
</script>