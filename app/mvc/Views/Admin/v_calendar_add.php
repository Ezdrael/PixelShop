<div class="content-card">
    <form action="" method="POST" id="event-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2>Нова подія</h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/calendar" class="action-btn" title="Повернутися до календаря"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        <div class="form-body">
            <div class="form-group-inline">
                <label for="event-title">Назва<span class="required-field">*</span></label>
                <input type="text" id="event-title" name="title" class="form-control" required>
            </div>
            <div class="form-group-inline">
                <label for="event-start">Час початку<span class="required-field">*</span></label>
                <input type="datetime-local" id="event-start" name="start_time" class="form-control" required>
            </div>
            <div class="form-group-inline">
                <label for="event-end">Час закінчення</label>
                <input type="datetime-local" id="event-end" name="end_time" class="form-control">
            </div>
            <div class="form-group-inline">
                <label for="event-description">Опис</label>
                <textarea id="event-description" name="description" class="form-control" rows="5"></textarea>
            </div>
        </div>
    </form>
</div>

<script id="users-data" type="application/json">
    <?php echo $usersJson ?? '[]'; ?>
</script>