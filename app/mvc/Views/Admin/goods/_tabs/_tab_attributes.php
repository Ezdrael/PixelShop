<div class="tab-content" id="attributes">
    <div class="form-body">
        <div id="attributes-container">
            <?php if (isset($good_attributes)): foreach ($good_attributes as $attr): ?>
                <div class="attribute-row">
                    <input type="hidden" name="attributes[][id]" value="<?php echo $attr['attribute_id']; ?>">
                    <label class="form-control" style="flex-basis: 200px; background: #f8fafc;"><?php echo htmlspecialchars($attr['name']); ?></label>
                    <input type="text" name="attributes[][value]" class="form-control" value="<?php echo htmlspecialchars($attr['value']); ?>">
                    <button type="button" class="action-btn delete remove-attribute-btn"><i class="fas fa-trash"></i></button>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <div style="margin-top: 1rem;">
            <select id="add-attribute-select" class="form-control" style="width: auto; display: inline-block;">
                <option value="">-- Додати характеристику --</option>
                <?php if(isset($attributes)) foreach ($attributes as $attr): ?><option value="<?php echo $attr['id']; ?>"><?php echo htmlspecialchars($attr['name']); ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
</div>