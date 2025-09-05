<div class="tab-content active" id="general">
    <div class="form-body">
        <div class="form-group-inline"><label for="good-active">Статус</label><div class="form-control-wrapper"><label class="toggle-switch"><input type="checkbox" id="good-active" name="is_active" value="1" <?php if (!isset($good) || !empty($good['is_active'])) echo 'checked'; ?>><span class="slider"></span></label></div></div>
        <div class="form-group-inline"><label for="good-name">Назва товару<span class="required-field">*</span></label><div class="form-control-wrapper"><input type="text" id="good-name" name="name" class="form-control" value="<?php echo htmlspecialchars($good['name'] ?? ''); ?>" required></div></div>
        <div class="form-group-inline"><label for="good-description">Опис</label><div class="form-control-wrapper"><textarea id="good-description" name="description" class="form-control" rows="8"><?php echo htmlspecialchars($good['description'] ?? ''); ?></textarea></div></div>
        <div class="form-group-inline"><label for="good-keywords">Ключові слова</label><div class="form-control-wrapper"><input type="text" id="good-keywords" name="keywords" class="form-control" value="<?php echo htmlspecialchars($good['keywords'] ?? ''); ?>"></div></div>
    </div>
</div>