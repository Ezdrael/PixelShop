<style>
    /* Новий макет для дашборду: основна частина + бічна колонка */
    .dashboard-layout {
        display: grid;
        grid-template-columns: 1fr 300px; /* Основна колонка і бічна 300px */
        grid-template-areas: "main-content sidebar";
        gap: 1.5rem;
    }
    .dashboard-main { grid-area: main-content; }
    .dashboard-sidebar { grid-area: sidebar; }

    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
    .widget { background-color: #fff; border: 1px solid var(--border-color); border-radius: 8px; display: flex; flex-direction: column; }
    .widget-header { padding: 1rem; border-bottom: 1px solid var(--border-color); font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .widget-header .title { display: flex; align-items: center; gap: 0.75rem; }
    .widget-header.draggable { cursor: grab; }
    .widget-body { padding: 1rem; flex-grow: 1; }
    .widget-list { list-style: none; padding: 0; margin: 0; }
    .widget-list li { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color); }
    .widget-list li:last-child { border-bottom: none; }
    .widget-list .time { font-size: 0.9em; color: var(--secondary-text); }
</style>

<div class="dashboard-layout">
    <div class="dashboard-main">
        <div class="dashboard-grid" id="dashboard-container">
            <div class="widget" data-widget-type="welcome">
                <div class="widget-header draggable"><div class="title"><i class="fas fa-hand-sparkles"></i> Вітаємо!</div></div>
                <div class="widget-body">
                    <p>Ласкаво просимо до панелі керування, <?php echo htmlspecialchars($this->currentUser['name']); ?>!</p>
                </div>
            </div>
            <div class="widget" data-widget-type="global_stats">
                <div class="widget-header draggable"><div class="title"><i class="fas fa-chart-pie"></i> Загальна статистика</div></div>
                <div class="widget-body"><ul class="widget-list">...</ul></div>
            </div>
            <div class="widget" data-widget-type="calendar_events">
                <div class="widget-header draggable"><div class="title"><i class="fas fa-calendar-check"></i> Незабаром у календарі</div></div>
                <div class="widget-body"><ul class="widget-list">...</ul></div>
            </div>
            
            <div class="widget" data-widget-type="engine_news">
                <div class="widget-header draggable"><div class="title"><i class="fas fa-rocket"></i> Оновлення рушія</div></div>
                <div class="widget-body">
                    <ul class="widget-list">
                        <li><span>Версія 1.1.0: Додано календар.</span> <strong class="time">03.09.2025</strong></li>
                        <li><span>Версія 1.0.5: Виправлено помилки.</span> <strong class="time">28.08.2025</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <aside class="dashboard-sidebar">
        <div class="widget">
            <div class="widget-header"><div class="title"><i class="fas fa-store"></i> Новини маркетплейсу</div></div>
            <div class="widget-body">
                <ul class="widget-list">
                    <?php if (!empty($marketplaceNews)): foreach($marketplaceNews as $news): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($news['url']); ?>" target="_blank"><?php echo htmlspecialchars($news['title']); ?></a>
                        </li>
                    <?php endforeach; else: ?>
                        <li>Не вдалося завантажити новини.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('dashboard-container');
    if (container) {
        new Sortable(container, {
            animation: 150,
            handle: '.widget-header', // Дозволяє перетягувати лише за заголовок
            onEnd: function (evt) {
                // Тут буде AJAX-запит для збереження нового порядку віджетів
                const widgetOrder = Array.from(evt.to.children).map(item => item.dataset.widgetType);
                console.log('Новий порядок віджетів:', widgetOrder);
            },
        });
    }
});
</script>