<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2><?php echo $this->title; ?></h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/albums" class="action-btn" title="До списку альбомів"><i class="fas fa-arrow-left"></i></a>
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
                        <?php foreach($albums as $cat): ?>
                            <?php // Заборона робити альбом дочірнім для самого себе
                            if (isset($album) && $album['id'] == $cat['id']) continue; ?>
                            <option value="<?php echo $cat['id']; ?>" <?php if (isset($album) && $album['parent_id'] == $cat['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
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