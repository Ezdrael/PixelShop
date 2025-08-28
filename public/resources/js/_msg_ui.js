/* ===================================================================
   Файл:      _msg_ui.js
   Призначення: Функції для маніпуляції з UI чату.
   =================================================================== */

// Функція для екранування HTML-тегів
const htmlspecialchars = (str) => str.toString().replace(/[&<>"']/g, match => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[match]);

/**
 * Додає одне повідомлення в кінець списку та прокручує вниз.
 */
export function appendMessage(message, messageList, currentUserId) {
    const emptyMsg = messageList.querySelector('.empty-chat-message');
    if (emptyMsg) emptyMsg.remove();
    
    const isOwn = message.sender_id === currentUserId;
    const item = document.createElement('div');
    item.className = `message-item ${isOwn ? 'own' : ''}`;
    const date = new Date(message.created_at).toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
    
    item.innerHTML = `<div class="meta"><strong>${isOwn ? 'Ви' : htmlspecialchars(message.sender_name)}</strong> <span>${date}</span></div><div class="body">${htmlspecialchars(message.body)}</div>`;
    messageList.appendChild(item);
    messageList.scrollTop = messageList.scrollHeight;
}

/**
 * Відображає масив повідомлень, очищуючи попередні.
 */
export function displayMessages(messages, messageList, currentUserId) {
    messageList.innerHTML = '';
    if (messages?.length) {
        messages.forEach(msg => appendMessage(msg, messageList, currentUserId));
    } else {
        messageList.innerHTML = '<p class="empty-chat-message">Повідомлень ще немає.</p>';
    }
    messageList.scrollTop = messageList.scrollHeight;
}

/**
 * Оновлює всі значки непрочитаних повідомлень.
 */
export function updateAllBadges(counts) {
    let totalUnread = 0;
    document.querySelectorAll('.unread-badge').forEach(badge => {
        badge.textContent = '0';
        badge.style.display = 'none';
    });

    for (const convId in counts) {
        const count = counts[convId];
        if (count > 0) {
            totalUnread += count;
            // Потрібно буде реалізувати логіку пошуку li за conversation_id
        }
    }

    const totalBadge = document.getElementById('unread-counter');
    if (totalBadge) {
        totalBadge.textContent = totalUnread > 0 ? totalUnread : '';
        totalBadge.style.display = totalUnread > 0 ? 'inline-block' : 'none';
    }
}

/**
 * Ініціалізує логіку авторозширення для textarea.
 */
export function initTextareaAutoResize(textarea, form) {
    if (!textarea || !form) return;
    
    const adjustTextareaHeight = () => {
        textarea.style.height = 'auto';
        const newHeight = Math.min(textarea.scrollHeight, 120); // max-height 120px
        textarea.style.height = `${newHeight}px`;
    };
    
    textarea.addEventListener('input', adjustTextareaHeight);
    
    textarea.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    });
}