// public/resources/js/messages-widget/ui.js

import { state, saveState, loadDraft } from './state.js';

/**
 * Створює HTML-елемент для одного повідомлення.
 * Додає клас .new-message для підсвічування.
 */
export function renderMessage(msg, currentUserId) {
    const isOwn = msg.sender_id == currentUserId;
    const item = document.createElement('div');
    const isNew = msg.isNew || msg.is_read === 0;
    item.className = `message-item ${isOwn ? 'own' : ''} ${isNew ? 'new-message' : ''}`;
    
    const date = new Date(msg.created_at).toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
    item.innerHTML = `
        <div class="meta"><strong>${isOwn ? 'Ви' : msg.sender_name}</strong> <span>${date}</span></div>
        <div class="body">${msg.body.replace(/\n/g, '<br>')}</div>
    `;
    return item;
}

/**
 * Обробляє нове повідомлення: додає в чат і запускає анімацію.
 */
export function handleNewMessage(message, dom, config) {
    dom.messageList.appendChild(renderMessage(message, config.currentUserId));
    dom.messageList.scrollTop = dom.messageList.scrollHeight;
    markMessagesAsRead(dom);
}

/**
 * Запускає анімацію згасання для нових повідомлень.
 */
export function markMessagesAsRead(dom) {
    const newMessages = dom.messageList.querySelectorAll('.new-message');
    if (newMessages.length > 0) {
        setTimeout(() => {
            newMessages.forEach(msg => {
                msg.classList.remove('new-message'); // Прибираємо клас для підсвічування
                msg.classList.add('is-read');     // Додаємо клас для анімації
            });
        }, 300);
    }
}

/**
 * Оновлює миготіння для всіх елементів інтерфейсу.
 */
export function updateBlinkingUI(dom) {
    document.querySelectorAll('.blinking').forEach(el => el.classList.remove('blinking'));
    let hasUserUnread = false;
    let hasGroupUnread = false;

    state.unreadConversations.forEach(convId => {
        const [type, id] = convId.split('-');
        const listItem = dom.widget.querySelector(`.conversation-list li[data-type="${type}"][data-item-id="${id}"]`);
        if (listItem) listItem.classList.add('blinking');
        
        if (type === 'user') hasUserUnread = true;
        if (type === 'group') hasGroupUnread = true;
    });

    dom.userTab?.classList.toggle('blinking', hasUserUnread);
    dom.groupTab?.classList.toggle('blinking', hasGroupUnread);
    document.getElementById('messages-toggle-btn')?.classList.toggle('blinking', state.unreadConversations.size > 0);
}

/**
 * Активує вибраний чат, завантажує повідомлення та запускає анімації.
 */
export async function activateChat(type, id, listItem, dom, api, config, initTextareaAutoResize) {
    state.activeChat = { type, id };
    document.querySelectorAll('.conversation-list li').forEach(li => li.classList.remove('active'));
    listItem.classList.add('active');
    dom.chatWelcome.style.display = 'none';
    dom.chatWindow.style.display = 'flex';
    
    const convId = `${type}-${id}`;
    if (state.unreadConversations.has(convId)) {
        state.unreadConversations.delete(convId);
        listItem.classList.remove('blinking');
        saveState(dom);
        updateBlinkingUI(dom);
    }

    loadDraft(dom, initTextareaAutoResize);
    const result = await api.fetchMessages(type, id);
    dom.messageList.innerHTML = '';
    if (result.success && result.messages) {
        result.messages.forEach(msg => dom.messageList.appendChild(renderMessage(msg, config.currentUserId)));
        dom.messageList.scrollTop = dom.messageList.scrollHeight;
    }
    
    markMessagesAsRead(dom);
    dom.messageInput.focus();
}

/**
 * Автоматично відкриває перший непрочитаний чат.
 */
export function openFirstUnread(dom) {
    if (state.unreadConversations.size > 0) {
        const firstUnread = Array.from(state.unreadConversations)[0];
        const [type, id] = firstUnread.split('-');

        const tabToActivate = dom.widget.querySelector(`.tab-link[data-tab="${type}s"]`);
        tabToActivate?.click();
        
        setTimeout(() => {
            const chatToActivate = dom.widget.querySelector(`li[data-type="${type}"][data-item-id="${id}"]`);
            chatToActivate?.click();
        }, 100);
    }
}

/**
 * Завантажує та відображає списки користувачів та груп.
 */
export async function loadConversations(dom, api, config) {
    try {
        const result = await api.getConversations();
        dom.conversationsUsers.innerHTML = '';
        dom.conversationsGroups.innerHTML = '';
        if (result.success) {
            result.users.forEach(user => {
                const li = document.createElement('li');
                li.dataset.type = 'user';
                li.dataset.itemId = user.id;
                const avatarHtml = user.avatar_url ? `<img src="${user.avatar_url}" alt="Avatar" class="conv-avatar">` : '<div class="conv-avatar-placeholder"><i class="fas fa-user"></i></div>';
                li.innerHTML = `${avatarHtml}<div class="conv-title">${user.name}</div>`;
                dom.conversationsUsers.appendChild(li);
            });
            result.groups.forEach(group => {
                const li = document.createElement('li');
                li.dataset.type = 'group';
                li.dataset.itemId = group.id;
                li.innerHTML = `<div class="conv-avatar-placeholder"><i class="fas fa-users"></i></div><div class="conv-title">${group.group_name}</div>`;
                dom.conversationsGroups.appendChild(li);
            });
            updateBlinkingUI(dom);
        }
    } catch (error) {
        console.error("Не вдалося завантажити розмови:", error);
        dom.conversationsUsers.innerHTML = '<li>Помилка завантаження</li>';
    }
}