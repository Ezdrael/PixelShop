// public/resources/js/calendar-init.js

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar-container');
    if (!calendarEl) return; // Виконуємо код тільки на сторінці календаря

    const yearInput = document.getElementById('year-input');
    const monthSelect = document.getElementById('month-select');
    let allFetchedEvents = []; // Кеш для завантажених подій

    // --- Керування бічною панеллю (список та деталі) ---
    const eventListTitle = document.getElementById('event-list-title');
    const eventList = document.getElementById('event-list');
    const detailContainer = document.getElementById('event-detail-container');

    // ✅ ВИПРАВЛЕННЯ 2: Всі функції-хелпери перенесено НАГОРУ,
    // щоб вони були доступні для колбеків календаря.
    const updateEventList = (date = null) => {
        detailContainer.style.display = 'none';
        eventList.innerHTML = '';
        let eventsToShow = [];

        if (date) {
            const selectedDateStr = date.toISOString().split('T')[0];
            eventListTitle.textContent = `Події на ${date.toLocaleDateString('uk-UA')}`;
            eventsToShow = allFetchedEvents.filter(event => event.start.startsWith(selectedDateStr));
        } else {
            eventListTitle.textContent = 'Найближчі події';
            const now = new Date();
            eventsToShow = allFetchedEvents
                .filter(event => new Date(event.start) >= now)
                .sort((a, b) => new Date(a.start) - new Date(b.start))
                .slice(0, 10);
        }

        if (eventsToShow.length === 0) {
            eventList.innerHTML = '<li>Немає подій для відображення.</li>';
            return;
        }

        eventsToShow.forEach(event => {
            const li = document.createElement('li');
            li.dataset.eventId = event.id;
            const startTime = new Date(event.start).toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
            li.innerHTML = `<div>${event.title}</div><div class="event-time">${startTime}</div>`;
            eventList.appendChild(li);
        });
    };

    const showEventDetails = (eventId) => {
        const event = allFetchedEvents.find(e => e.id == eventId);
        if (!event) return;

        eventList.querySelectorAll('li').forEach(li => {
            li.classList.toggle('active', li.dataset.eventId == eventId);
        });

        document.getElementById('event-detail-title').textContent = event.title;
        const startTime = new Date(event.start).toLocaleString('uk-UA');
        const endTime = event.end ? new Date(event.end).toLocaleString('uk-UA') : 'не вказано';
        document.getElementById('event-detail-time').textContent = `${startTime} - ${endTime}`;
        document.getElementById('event-detail-description').innerHTML = event.description ? event.description.replace(/\n/g, '<br>') : 'Опис відсутній.';
        
        document.getElementById('event-edit-btn').href = `${document.body.dataset.baseUrl}/calendar/edit/${event.id}`;
        document.getElementById('event-delete-btn').dataset.eventId = event.id;

        detailContainer.style.display = 'block';
    };

    // --- Ініціалізація календаря ---
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'uk',
        headerToolbar: false,
        height: 'auto',
        width: 'auto',

        events: `${document.body.dataset.baseUrl}/calendar/events`,

        eventsSet: function(events) {
            allFetchedEvents = events.map(e => ({
                id: e.id,
                title: e.title,
                start: e.startStr,
                end: e.endStr,
                description: e.extendedProps.description || ''
            }));
            updateEventList();
        },

        dateClick: function(info) {
            updateEventList(info.date);
        },
        
        eventClick: function(info) {
            showEventDetails(info.event.id);
        }
    });
    calendar.render();

    // --- Прив'язка обробників подій до елементів ---
    eventList.addEventListener('click', (e) => {
        const li = e.target.closest('li');
        if (li && li.dataset.eventId) {
            showEventDetails(li.dataset.eventId);
        }
    });

    const navigateCalendar = () => {
        const year = yearInput.value;
        const month = monthSelect.value;
        calendar.gotoDate(`${year}-${String(parseInt(month) + 1).padStart(2, '0')}-01`);
    };
    yearInput.addEventListener('change', navigateCalendar);
    monthSelect.addEventListener('change', navigateCalendar);
});