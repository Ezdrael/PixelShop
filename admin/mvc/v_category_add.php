<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2><?php echo $this->title; ?></h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/categories" class="action-btn" title="Повернутися до списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        
        <div class="form-group-inline">
            <label for="category-active">Увімк. / Вимкн.</label>
            <div class="form-control-wrapper">
                <label class="toggle-switch">
                    <input type="checkbox" id="category-active" name="is_active" value="1" <?php 
                        if (!isset($category) || !empty($category['is_active'])) echo 'checked'; 
                    ?>>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="form-body">
            <div class="form-group-inline">
                <label for="category-name">Назва категорії</label>
                <input type="text" id="category-name" name="name" class="form-control" value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group-inline">
                <label for="parent-id">Батьківська категорія</label>
                <select id="parent-id" name="parent_id" class="form-control">
                    <option value="0">-- Немає --</option>
                    <?php foreach($categories as $cat): ?>
                        <?php // Заборона робити категорію дочірньою для самої себе
                        if (isset($category) && $category['id'] == $cat['id']) continue; ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if (isset($category) && $category['parent_id'] == $cat['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
</div>