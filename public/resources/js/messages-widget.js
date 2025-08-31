// public/resources/js/messages-widget.js

import { getApi } from './messages-widget/api.js';
import * as ui from './messages-widget/ui.js';
import * as state from './messages-widget/state.js';
import { connectWebSocket } from './messages-widget/websocket.js';

document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('messages-widget');
    if (!widget) return;

    // --- 1. Конфігурація та елементи DOM ---
    const config = {
        baseUrl: document.body.dataset.baseUrl || '',
        currentUserId: document.body.dataset.currentUserId,
        wsHost: window.location.hostname,
        wsToken: document.body.dataset.wsToken,
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    const dom = {
        widget,
        tabsContainer: widget.querySelector('.chat-tabs'),
        userTab: widget.querySelector('[data-tab="users"]'),
        groupTab: widget.querySelector('[data-tab="groups"]'),
        conversationsUsers: document.getElementById('conversations-list-users'),
        conversationsGroups: document.getElementById('conversations-list-groups'),
        chatWelcome: document.getElementById('chat-welcome'),
        chatWindow: document.getElementById('chat-window'),
        messageList: document.getElementById('message-list'),
        messageForm: document.getElementById('message-form'),
        messageInput: document.getElementById('message-body-input'),
        notificationSound: document.getElementById('notification-sound'),
        sendSound: document.getElementById('send-sound')
    };

    // Перевірка, щоб уникнути помилок, якщо основні елементи відсутні
    if (!dom.tabsContainer || !dom.conversationsUsers || !dom.messageForm) {
        console.error("Критичні елементи чату відсутні в DOM. Ініціалізація неможлива.");
        return;
    }

    const api = getApi(config);
    const conn = connectWebSocket(config, dom);

    // --- 2. Допоміжні функції та логіка відправки ---
    const adjustTextareaHeight = () => {
        const textarea = dom.messageInput;
        textarea.style.height = 'auto';
        textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
    };

    const sendMessage = () => {
        const body = dom.messageInput.value.trim();
        if (!body || !state.activeChat.id || !conn || conn.readyState !== WebSocket.OPEN) return;

        const messageData = {
            type: 'message', sender_id: config.currentUserId, body: body,
            chat_type: state.activeChat.type, chat_id: state.activeChat.id
        };
        conn.send(JSON.stringify(messageData));
        try { dom.sendSound.play(); } catch(err) {}
        dom.messageInput.value = '';
        adjustTextareaHeight();
        state.saveDraft(dom);
    };

    // --- 3. Ініціалізація обробників подій ---
    const observer = new MutationObserver(() => {
        if (dom.widget.classList.contains('open')) {
            ui.updateBlinkingUI(dom);
            ui.openFirstUnread(dom);
        }
    });
    observer.observe(dom.widget, { attributes: true, attributeFilter: ['class'] });

    dom.messageInput.addEventListener('input', adjustTextareaHeight);
    dom.messageForm.addEventListener('submit', (e) => { e.preventDefault(); sendMessage(); });
    dom.messageInput.addEventListener('keydown', (e) => { if (e.key === 'Enter' && e.ctrlKey) { e.preventDefault(); sendMessage(); } });

    dom.tabsContainer.addEventListener('click', (e) => {
        const tab = e.target.closest('.tab-link');
        if (!tab) return;
        dom.tabsContainer.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        widget.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.getElementById(`conversations-list-${tab.dataset.tab}`).classList.add('active');
        state.saveState(dom);
    });

    [dom.conversationsUsers, dom.conversationsGroups].forEach(list => {
        if(list) {
            list.addEventListener('click', (e) => {
                const li = e.target.closest('li');
                if (li) {
                    ui.activateChat(li.dataset.type, li.dataset.itemId, li, dom, api, config, adjustTextareaHeight);
                }
            });
        }
    });

    // --- 4. Перший запуск логіки ---
    ui.loadConversations(dom, api, config);
    state.loadState(dom);
});