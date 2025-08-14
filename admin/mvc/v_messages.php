<div class="chat-container" data-current-user-id="<?php echo htmlspecialchars($currentUser['id']); ?>">
    <aside class="conversations-sidebar">
        <div class="chat-tabs">
            <button class="tab-link active" data-tab="users"><i class="fas fa-user"></i> Користувачі</button>
            <button class="tab-link" data-tab="groups"><i class="fas fa-users"></i> Групи</button>
        </div>
        <div class="list-container">
            <ul id="users" class="conversation-list tab-content active">
                <?php foreach ($users as $user): ?>
                    <li data-type="user" data-item-id="<?php echo $user['id']; ?>">
                        <div class="conv-title-wrapper">
                            <div class="conv-title"><?php echo htmlspecialchars($user['name']); ?></div>
                            <span class="unread-badge" style="display: none;">0</span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul id="groups" class="conversation-list tab-content">
                <?php foreach ($groups as $group): ?>
                    <li data-type="group" data-item-id="<?php echo $group['id']; ?>">
                        <div class="conv-title-wrapper">
                            <div class="conv-title"><?php echo htmlspecialchars($group['title']); ?></div>
                            <span class="unread-badge" style="display: none;">0</span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>
    <main class="chat-main">
        <div id="chat-welcome" class="chat-window welcome-message"><p>Оберіть чат</p></div>
        <div id="chat-window" class="chat-window" style="display: none;">
            <div id="message-list" class="message-list"></div>
            <form id="message-form" class="message-form">
                <textarea id="message-body-input" name="body" placeholder="Напишіть повідомлення..." rows="1" required></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </main>
</div>
<script type="module" src="<?php echo BASE_URL; ?>/resources/js/messages.js"></script>

<style>
/* ===================================================================
   Локальні стилі, специфічні для сторінки /messages
   =================================================================== */

/* --- Загальний контейнер чату --- */
.chat-container { display: flex; height: 75vh; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }

/* --- Ліва панель: вкладки та списки чатів --- */
.conversations-sidebar { width: 300px; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; }
.chat-tabs { display: flex; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
.tab-link { flex: 1; background: none; border: none; padding: 1rem; font-size: 1rem; cursor: pointer; color: var(--secondary-text); border-bottom: 3px solid transparent; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: color .2s, border-color .2s; }
.tab-link:hover { color: var(--primary-text); }
.tab-link.active { color: var(--accent-color); border-bottom-color: var(--accent-color); }
.list-container { flex-grow: 1; overflow-y: auto; }
.conversation-list { list-style: none; padding: 0; margin: 0; }
.conversation-list li { padding: 1rem; cursor: pointer; border-bottom: 1px solid var(--border-color); }
.conversation-list li:hover { background-color: var(--sidebar-hover-bg); }
.conversation-list li.active { background-color: var(--accent-color); color: #fff; }
.conv-title-wrapper { display: flex; justify-content: space-between; align-items: center; }
.conv-title { font-weight: 600; }

/* --- Права панель: вікно повідомлень --- */
.chat-main { flex-grow: 1; display: flex; flex-direction: column; overflow: hidden; }
#chat-window { display: flex; flex-direction: column; height: 100%; overflow: hidden; }
.welcome-message { display: flex; justify-content: center; align-items: center; height: 100%; color: var(--secondary-text); }
#message-list { flex-grow: 1; overflow-y: auto; padding: 1rem; }
#message-form { display: flex; align-items: flex-end; /* Вирівнюємо по низу */ padding: 1rem; border-top: 1px solid var(--border-color); flex-shrink: 0; gap: 0.5rem; }

/* --- Нові стилі для поля вводу та кнопки --- */
#message-body-input {
    flex-grow: 1;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 1rem;
    font-family: 'Roboto', sans-serif;
    resize: none; /* Забороняємо ручну зміну розміру */
    overflow-y: auto; /* Додаємо скрол, коли висота максимальна */
    line-height: 1.5;
    max-height: 120px; /* Максимальна висота (приблизно 5 рядків) */
}

#message-form button {
    flex-shrink: 0; /* Забороняємо кнопці стискатися */
    width: 48px;
    height: 48px;
    background-color: var(--accent-color);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* --- Елементи всередині чату --- */
.message-item {
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Вирівнювання по лівому краю за замовчуванням */
}

.message-item .meta {
    font-size: 0.8em;
    color: var(--secondary-text);
    margin-bottom: 0.25rem;
}

.message-item .meta strong {
    color: var(--primary-text);
}

.message-item .body {
    background-color: var(--sidebar-hover-bg);
    color: var(--primary-text);
    padding: 0.75rem 1rem;
    border-radius: 12px;
    max-width: 70%; /* Трохи зменшимо максимальну ширину */
    word-wrap: break-word;
}

/* Стилі для власних повідомлень */
.message-item.own {
    align-items: flex-end; /* Вирівнювання по правому краю */
}

.message-item.own .meta {
    text-align: right;
}

.message-item.own .body {
    background-color: var(--accent-color);
    color: #fff;
    border-bottom-right-radius: 3px; /* Змінюємо заокруглення для кращого візуального відділення */
}

/* Стилі для чужих повідомлень */
.message-item:not(.own) .body {
    border-bottom-left-radius: 3px; /* Змінюємо заокруглення */
}
.empty-chat-message { text-align: center; color: var(--secondary-text); padding-top: 2rem; }
</style>