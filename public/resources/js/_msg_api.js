/* ===================================================================
   Файл:      _msg_api.js
   Призначення: Функції для AJAX-запитів до API чату.
   =================================================================== */

/**
 * Завантажує історію повідомлень.
 */
export async function fetchMessages(type, id, config) {
    const fd = new FormData();
    fd.append('chat_type', type);
    fd.append('chat_id', id);
    try {
        const res = await fetch(`${config.baseUrl}/messages/fetch`, { method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrfToken }, body: fd });
        if (!res.ok) throw new Error('Помилка мережі');
        const data = await res.json();
        return data.messages;
    } catch (e) {
        console.error('Помилка завантаження повідомлень:', e);
        return null;
    }
}

/**
 * Запитує у сервера кількість непрочитаних повідомлень.
 */
export async function checkForNotifications(config) {
    try {
        const res = await fetch(`${config.baseUrl}/messages/unread`);
        if (res.ok) {
            const data = await res.json();
            return data.success ? data.counts : null;
        }
        return null;
    } catch (e) {
        return null;
    }
}

/**
 * Позначає розмову як прочитану.
 */
export async function markAsRead(type, id, config) {
    const fd = new FormData();
    fd.append('chat_type', type);
    fd.append('chat_id', id);
    try {
        const res = await fetch(`${config.baseUrl}/messages/markread`, { method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrfToken }, body: fd });
        if (!res.ok) throw new Error('Помилка мережі');
        const data = await res.json();
        return data.success;
    } catch (e) {
        console.error('Помилка відмітки прочитаним:', e);
        return false;
    }
}