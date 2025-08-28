<div class="content-card">
    <?php if (isset($good) && $good): ?>
        
        <div class="form-header">
            <div>
                <h2><?php echo htmlspecialchars($good['name']); ?></h2>
                <p class="user-id-text">ID товару: #<?php echo $good['id']; ?></p>
            </div>
            <div class="actions-cell">
                <?php if ($this->hasPermission('goods', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/goods/edit/<?php echo $good['id']; ?>" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/goods" class="action-btn" title="До списку товарів"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tab-nav">
                <a href="#" class="tab-link active" data-tab="general"><i class="fas fa-info-circle"></i> Загальні</a>
                <a href="#" class="tab-link" data-tab="stock"><i class="fas fa-warehouse"></i> Наявність на складах</a>
                <a href="#" class="tab-link" data-tab="history"><i class="fas fa-history"></i> Історія рухів</a>
            </div>

            <div class="tab-content-wrapper">
                <div class="tab-content active" id="general">
                    <div class="info-grid">
                        <div class="info-card">
                            <h3>Основна інформація</h3>
                            <div class="info-card-body">
                                <p><strong>Статус:</strong> <?php echo !empty($good['is_active']) ? '<span class="status-badge active">Активний</span>' : '<span class="status-badge inactive">Вимкнено</span>'; ?></p>
                                <p><strong>Категорія:</strong> <a href="<?php echo BASE_URL; ?>/categories/watch/<?php echo $good['category_id']; ?>" class="styled-link"><?php echo htmlspecialchars($good['category_name']); ?></a></p>
                            </div>
                        </div>
                        <div class="info-card">
                            <h3>Дані товару</h3>
                            <div class="info-card-body">
                                <p><strong>Ціна:</strong> <span style="font-weight: bold; color: var(--success-color);"><?php echo htmlspecialchars(number_format($good['price'], 2, '.', ' ')); ?> грн</span></p>
                                <p><strong>Вага:</strong> <span><?php echo ($good['weight'] > 0) ? htmlspecialchars($good['weight']) . ' кг' : 'не вказано'; ?></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <h3>Опис товару</h3>
                        <p><?php echo !empty($good['description']) ? nl2br(htmlspecialchars($good['description'])) : '<em>Опис відсутній.</em>'; ?></p>
                    </div>
                </div>

                <div class="tab-content" id="stock">
                    <table class="orders-table">
                        <thead><tr><th>Назва складу</th><th style="text-align: right;">Поточний залишок</th><th style="text-align: center;">Дії</th></tr></thead>
                        <tbody>
                            <?php if (!empty($stockLevels)): foreach ($stockLevels as $stock): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($stock['warehouse_name']); ?></td>
                                    <td style="text-align: right; font-weight: 700;"><?php echo htmlspecialchars($stock['stock_level']); ?></td>
                                    <td class="actions-cell" style="justify-content: center;">
                                        <a href="<?php echo BASE_URL; ?>/warehouses/watch/<?php echo $stock['warehouse_id']; ?>" class="action-btn" title="Переглянути склад"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="3" style="text-align: center;"><em>Цей товар відсутній на складах.</em></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tab-content" id="history">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Дата та час</th>
                                <th style="text-align: center;">Операція</th>
                                <th>Склад</th>
                                <th style="text-align: right;">Зміна</th>
                                <th style="text-align: right;">Залишок</th>
                                <th>Користувач</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history)): foreach ($history as $t): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($t['transaction_date'])); ?></td>
                                    
                                    <td style="text-align: center; font-size: 1.2rem;">
                                        <?php
                                            $iconClass = '';
                                            $iconTitle = 'Невідома операція';

                                            switch ($t['document_type']) {
                                                case 'arrival_form':
                                                    $iconClass = 'fas fa-truck-loading'; // Іконка як у меню "Надходження"
                                                    $iconTitle = 'Надходження';
                                                    break;
                                                
                                                case 'transfer_form':
                                                    $iconClass = 'fas fa-retweet'; // Іконка як у меню "Переміщення"
                                                    $iconTitle = 'Переміщення';
                                                    break;
                                                
                                                // Тут можна додати інші типи операцій в майбутньому (продаж, списання)
                                            }

                                            if ($iconClass) {
                                                echo '<i class="' . $iconClass . '" title="' . $iconTitle . '"></i>';
                                            }
                                        ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($t['warehouse_name']); ?></td>
                                    <td style="text-align: right; color: <?php echo ($t['quantity'] > 0) ? 'var(--success-color)' : 'var(--danger-color)'; ?>; font-weight: 700;">
                                        <?php echo ($t['quantity'] > 0 ? '+' : '') . rtrim(rtrim($t['quantity'], '0'), '.'); ?>
                                    </td>
                                    <td style="text-align: right;"><?php echo rtrim(rtrim($t['balance'], '0'), '.'); ?></td>
                                    <td><?php echo htmlspecialchars($t['user_name']); ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="6" style="text-align: center;"><em>Історія рухів для цього товару відсутня.</em></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <h2>Товар не знайдено</h2>
    <?php endif; ?>
</div>

<script>
// Простий скрипт для перемикання вкладок
document.addEventListener('DOMContentLoaded', () => {
    const tabContainer = document.querySelector('.tabs-container');
    if (tabContainer) {
        const tabLinks = tabContainer.querySelectorAll('.tab-link');
        const tabContents = tabContainer.querySelectorAll('.tab-content');

        tabLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const tabId = link.dataset.tab;
                
                tabLinks.forEach(item => item.classList.remove('active'));
                tabContents.forEach(item => item.classList.remove('active'));
                
                link.classList.add('active');
                document.getElementById(tabId)?.classList.add('active');
            });
        });
    }
});
</script>
