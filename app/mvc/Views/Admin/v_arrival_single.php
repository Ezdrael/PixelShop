<div class="content-card">
    <?php if (isset($arrivalData) && $arrivalData): ?>
        
        <?php
            $status = $arrivalData['details']['status'] ?? 'completed';
            $isCompleted = ($status === 'completed');
        ?>

        <div class="form-header">
            <div>
                <h2>Надходження від <?php echo date('d.m.Y', strtotime($arrivalData['details']['transaction_date'])); ?></h2>
                <p class="user-id-text">Документ №: <?php echo htmlspecialchars($arrivalData['details']['document_id']); ?></p>
            </div>
            <div class="actions-cell">
                <?php if ($isCompleted && $this->hasPermission('arrivals', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/arrivals/edit/<?php echo urlencode($arrivalData['details']['document_id']); ?>" class="action-btn" title="Редагувати">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                <?php endif; ?>
                
                <?php if ($isCompleted && $this->hasPermission('arrivals', 'd')): ?>
                    <button type="button" class="action-btn delete-btn" 
                            data-entity="arrivals" 
                            data-id="<?php echo htmlspecialchars($arrivalData['details']['document_id']); ?>" 
                            data-name="<?php echo htmlspecialchars($arrivalData['details']['document_id']); ?>" 
                            title="Видалити (Скасувати)">
                        <i class="fas fa-trash"></i>
                    </button>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/arrivals" class="action-btn" title="До історії надходжень">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>

        <?php if (isset($correctiveDetails) && ($status === 'edited' || $status === 'canceled')): ?>
            <div class="alert alert-warning" style="margin-top: 1.5rem;">
                <p>
                    <strong>Це архівна версія!</strong><br>
                    Цей документ було
                    <?php echo $status === 'edited' ? 'відредаговано' : 'скасовано'; ?>
                    <strong><?php echo date('d.m.Y в H:i:s', strtotime($correctiveDetails['details']['details']['transaction_date'])); ?></strong>
                    користувачем 
                    <strong><?php echo htmlspecialchars($correctiveDetails['details']['details']['user_name'] ?? 'N/A'); ?></strong>.
                </p>
            </div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Деталі операції</h3>
                <div class="info-card-body">
                    <p><strong>Час:</strong> <span><?php echo date('d.m.Y H:i:s', strtotime($arrivalData['details']['transaction_date'])); ?></span></p>
                    <p><strong>Користувач:</strong> <span><?php echo htmlspecialchars($arrivalData['details']['user_name'] ?? '[Видалений користувач]'); ?></span></p>
                    <p><strong>Статус:</strong>
                        <span>
                            <?php if ($status === 'canceled'): ?>
                                <span class="status-badge canceled" style="background-color: var(--danger-color);">Скасовано</span>
                            <?php elseif ($status === 'edited'): ?>
                                 <span class="status-badge inactive">Відредаговано</span>
                            <?php else: ?>
                                <span class="status-badge completed">Проведено</span>
                            <?php endif; ?>
                        </span>
                    </p>
                    
                    <?php if (isset($arrivalVersions) && count($arrivalVersions) > 1): ?>
                    <div class="form-group-inline" style="margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                        <label for="version-switcher">Історія версій:</label>
                        <div class="form-control-wrapper">
                            <select id="version-switcher" class="form-control">
                                <?php
                                    $statusMap = [
                                        'completed' => 'Проведено',
                                        'edited' => 'Відредаговано',
                                        'canceled' => 'Скасовано'
                                    ];
                                    $sortedVersions = array_reverse($arrivalVersions);
                                    foreach($sortedVersions as $index => $version):
                                        $versionNumber = $index + 1; 
                                        $translatedStatus = $statusMap[$version['status']] ?? $version['status'];
                                ?>
                                    <option value="<?php echo urlencode($version['document_id']); ?>" <?php if ($version['document_id'] == $arrivalData['details']['document_id']) echo 'selected'; ?>>
                                        Версія <?php echo $versionNumber; ?>. <?php echo date('d.m.Y H:i', strtotime($version['transaction_date'])); ?> (<?php echo $translatedStatus; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-boxes"></i> Підсумки</h3>
                <div class="info-card-body">
                    <p><strong>Всього позицій:</strong> <span><?php echo htmlspecialchars($arrivalData['details']['total_positions']); ?></span></p>
                    <p><strong>Всього одиниць товару:</strong> <span><?php echo htmlspecialchars($arrivalData['details']['total_quantity']); ?></span></p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($arrivalData['details']['comment'])): ?>
        <div class="info-section">
            <h3><i class="fas fa-comment-dots"></i> Коментар до документа</h3>
            <p style="padding: 1rem; background-color: #f9fafb; border-radius: 8px; border: 1px solid var(--border-color);">
                <?php echo nl2br(htmlspecialchars($arrivalData['details']['comment'])); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <div class="info-section">
            <h3><i class="fas fa-dolly-flatbed"></i> Позиції документа</h3>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Назва товару</th>
                        <th>Склад призначення</th>
                        <th style="text-align: right;">Кількість</th>
                        <th style="text-align: center;">Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arrivalData['items'] as $item): ?>
                    <tr>
                        <td>
                            <?php if (empty($item['good_name'])): ?>
                                <span class="inactive-good">[Товар видалено]</span>
                            <?php elseif (empty($item['is_active'])): ?>
                                <span class="inactive-good" title="Товар вимкнено">
                                    <?php echo htmlspecialchars($item['good_name']); ?>
                                </span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($item['good_name']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['warehouse_name'] ?? '[Склад видалено]'); ?></td>
                        <td style="text-align: right;"><?php echo rtrim(rtrim($item['quantity'], '0'), '.'); ?></td>
                        <td class="actions-cell" style="justify-content: center;">
                            <?php if ($this->hasPermission('goods', 'v') && !empty($item['good_id'])): ?>
                                <a href="<?php echo BASE_URL; ?>/goods/watch/<?php echo $item['good_id']; ?>" class="action-btn" title="Переглянути товар"><i class="fas fa-eye"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php else: ?>
        <p>Надходження не знайдено</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const versionSwitcher = document.getElementById('version-switcher');
        if (versionSwitcher) {
            versionSwitcher.addEventListener('change', function() {
                const selectedDocumentId = this.value;
                if (selectedDocumentId) {
                    window.location.href = `<?php echo BASE_URL; ?>/arrivals/watch/${selectedDocumentId}`;
                }
            });
        }
    });
</script>