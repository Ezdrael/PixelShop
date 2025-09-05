<div class="tab-content" id="relations">
    <div class="form-body">
        <div class="form-group-inline">
            <label for="good-category">Категорія<span class="required-field">*</span></label>
            <div class="form-control-wrapper">
                <select id="good-category" name="category_id" class="form-control" required>
                    <option value="">-- Виберіть категорію --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if (isset($good) && $good['category_id'] == $cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>