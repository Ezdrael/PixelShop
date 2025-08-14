<div class="content-card">
    <div class="form-header">
        <h2>Всі склади</h2>
        <div class="actions-cell">
             <?php if ($this->hasPermission('warehouses', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/warehouses/add" class="action-btn save" title="Додати склад"><i class="fas fa-plus"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <table class="orders-table">
        <thead><tr><th>ID</th><th>Назва</th><th>Адреса</th><th>Дії</th></tr></thead>
        <tbody>
            <?php foreach ($warehouses as $warehouse): ?>
                <tr>
                    <td>#<?php echo $warehouse['id']; ?></td>
                    <td><?php echo htmlspecialchars($warehouse['name']); ?></td>
                    <td><?php echo htmlspecialchars($warehouse['address']); ?></td>
                    <td class="actions-cell">
                        <?php if ($this->hasPermission('warehouses', 'v')): ?>
                            <a href="<?php echo BASE_URL; ?>/warehouses/watch/<?php echo $warehouse['id']; ?>" class="action-btn" title="Переглянути"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('warehouses', 'e')): ?>
                            <a href="<?php echo BASE_URL; ?>/warehouses/edit/<?php echo $warehouse['id']; ?>" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('warehouses', 'd')): ?>
                            <button type="button" class="action-btn delete-btn" 
                                    data-entity="warehouses" data-user-id="<?php echo $warehouse['id']; ?>" 
                                    data-user-name="<?php echo htmlspecialchars($warehouse['name']); ?>" 
                                    title="Видалити"><i class="fas fa-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>