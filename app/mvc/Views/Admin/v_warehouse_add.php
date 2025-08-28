<div class="content-card">
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2><?php echo $this->title; ?></h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/warehouses" class="action-btn" title="До списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        <div class="form-body">
            <div class="form-group-inline">
                <label for="warehouse-name">Назва складу<span class="required-field">*</span></label>
                <div class="form-control-wrapper"><input type="text" id="warehouse-name" name="name" class="form-control" value="<?php echo htmlspecialchars($warehouse['name'] ?? ''); ?>" required></div>
            </div>
             <div class="form-group-inline">
                <label for="warehouse-address">Адреса</label>
                <div class="form-control-wrapper"><textarea id="warehouse-address" name="address" class="form-control" rows="5"><?php echo htmlspecialchars($warehouse['address'] ?? ''); ?></textarea></div>
            </div>
        </div>
    </form>
</div>