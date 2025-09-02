<?php
//app/mvc/Views/Admin/v_photo_album_add.php

/**
 * Рекурсивна функція для генерації <option> з ієрархічними відступами.
 */
function renderAlbumOptions($albums, $level = 0, $selectedId = 0) {
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level); // Створюємо відступ
    foreach ($albums as $album) {
        // Перевіряємо, чи потрібно вибрати цю опцію
        $isSelected = ($album['id'] == $selectedId) ? 'selected' : '';
        echo "<option value=\"{$album['id']}\" {$isSelected}>{$indent}" . htmlspecialchars($album['name']) . "</option>";

        // Якщо є дочірні альбоми, викликаємо функцію для них
        if (isset($album['children']) && !empty($album['children'])) {
            renderAlbumOptions($album['children'], $level + 1, $selectedId);
        }
    }
}
?>

<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2><?php echo $this->title; ?></h2>
            <div class="actions-cell">
                <button type="submit" id="save-album-btn" class="action-btn save" title="Зберегти [Ctrl+Enter]"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/albums" id="back-to-list-btn" class="action-btn" title="До списку альбомів [Esc]"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        
        <div class="form-body">
            <div class="form-group-inline">
                <label for="album-name">Назва альбому<span class="required-field">*</span></label>
                <div class="form-control-wrapper">
                    <input type="text" id="album-name" name="name" class="form-control" value="<?php echo htmlspecialchars($album['name'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group-inline">
                <label for="album-parent">Батьківський альбом</label>
                <div class="form-control-wrapper">
                    <select id="album-parent" name="parent_id" class="form-control">
                        <option value="0">-- Немає (кореневий альбом) --</option>
                        <?php
                            // Визначаємо, який ID має бути вибраний за замовчуванням
                            $selectedParentId = $album['parent_id'] ?? ($preselected_parent_id ?? 0);
                            // Викликаємо нашу нову функцію для побудови списку
                            renderAlbumOptions($albumsTree, 0, $selectedParentId);
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group-inline">
                <label for="album-description">Опис</label>
                <div class="form-control-wrapper">
                    <textarea id="album-description" name="description" class="form-control" rows="5"><?php echo htmlspecialchars($album['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('keydown', (event) => {
        // Якщо фокус на полі вводу, реагуємо тільки на Ctrl+Enter
        if (event.key === 'Enter' && event.ctrlKey) {
            event.preventDefault();
            document.getElementById('save-album-btn')?.click();
        }

        // Якщо фокус не на полі вводу, слухаємо Esc
        if (event.key === 'Escape') {
            event.preventDefault();
            document.getElementById('back-to-list-btn')?.click();
        }
    });
});
</script>