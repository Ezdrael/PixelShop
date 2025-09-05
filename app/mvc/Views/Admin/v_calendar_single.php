<div class="content-card">
    <?php if (isset($event) && $event): ?>
        <div class="form-header">
            <div>
                <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                <p class="user-id-text">Створив: <?php echo htmlspecialchars($event['user_name'] ?? 'Невідомий користувач'); ?> | ID: #<?php echo $event['id']; ?></p>
            </div>
            <div class="actions-cell">
                <?php if ($this->hasPermission('calendar', 'e')): ?>
                    <a href="<?php echo BASE_URL; ?>/calendar/edit/<?php echo $event['id']; ?>" class="action-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/calendar" class="action-btn" title="До календаря"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3><i class="fas fa-clock"></i> Час проведення</h3>
                <div class="info-card-body">
                    <p>
                        <strong>Початок:</strong>
                        <span><?php echo date('d.m.Y в H:i', strtotime($event['start_time'])); ?></span>
                    </p>
                    <p>
                        <strong>Закінчення:</strong>
                        <span><?php echo !empty($event['end_time']) ? date('d.m.Y в H:i', strtotime($event['end_time'])) : 'Не вказано'; ?></span>
                    </p>
                </div>
            </div>
        </div>

        <?php if (!empty($event['description'])): ?>
            <div class="info-section">
                <h3><i class="fas fa-info-circle"></i> Опис події</h3>
                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <h2>Подію не знайдено</h2>
    <?php endif; ?>
</div>