<?php
// Створюємо масив з українськими назвами місяців, щоб не залежати від локалі сервера
$ukrainianMonths = [
    'Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень',
    'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'
];
?>

<style>
    /* Стилі для нового макету календаря */
    .calendar-controls { display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; }
    .calendar-main-grid { display: grid; grid-template-columns: 1fr 350px; gap: 1.5rem; }
    #event-list-container, #event-detail-container { border: 1px solid var(--border-color); border-radius: 8px; background: #fdfdfd; }
    #event-list-container h3, #event-detail-container h3 { padding: 1rem; border-bottom: 1px solid var(--border-color); margin: 0; font-size: 1.1rem; }
    #event-list { list-style: none; padding: 0; margin: 0; max-height: 450px; overflow-y: auto; }
    #event-list li { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background-color .2s; }
    #event-list li:hover { background-color: var(--sidebar-hover-bg); }
    #event-list li.active { background-color: var(--accent-color); color: white; }
    #event-list .event-time { font-size: 0.8em; opacity: 0.7; }
    #event-detail-content { padding: 1rem; }
    #event-detail-container { margin-top: 1.5rem; }
    .fc-event { cursor: pointer; }
</style>

<div class="content-card">
    <div class="calendar-controls">
        <div class="form-control-wrapper">
            <input type="number" id="year-input" class="form-control" value="<?php echo date('Y'); ?>">
        </div>
        <div class="form-control-wrapper">
            <select id="month-select" class="form-control">
                <?php foreach ($ukrainianMonths as $index => $monthName): ?>
                    <option value="<?php echo $index; ?>" <?php if ($index == date('n') - 1) echo 'selected'; ?>>
                        <?php echo $monthName; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="actions-cell" style="margin-left: auto;">
            <?php if ($this->hasPermission('calendar', 'a')): ?>
                <a href="<?php echo BASE_URL; ?>/calendar/add" class="action-btn save" title="Додати подію">
                    <i class="fas fa-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="calendar-main-grid">
        <div id="calendar-container"></div>
        <div id="event-list-container">
            <h3 id="event-list-title">Найближчі події</h3>
            <ul id="event-list">
                <li>Немає подій для відображення.</li>
            </ul>
        </div>
    </div>

    <div id="event-detail-container" style="display: none;">
        <h3 id="event-detail-title"></h3>
        <div id="event-detail-content">
            <p><strong>Час:</strong> <span id="event-detail-time"></span></p>
            <p><strong>Опис:</strong></p>
            <div id="event-detail-description"></div>
            <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                <a id="event-edit-btn" href="#" class="btn-primary">Редагувати</a>
                <button id="event-delete-btn" class="btn-secondary" style="background-color: var(--danger-color);">Видалити</button>
            </div>
        </div>
    </div>
</div>
