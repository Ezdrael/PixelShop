/* ===================================================================
   Файл:      messages.js
   Призначення: Головний скрипт для сторінки повідомлень.
   =================================================================== */

import { appendMessage, displayMessages, updateAllBadges, initTextareaAutoResize } from './_msg_ui.js';
import { fetchMessages, checkForNotifications, markAsRead } from './_msg_api.js';

document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.querySelector('.chat-container');
    if (!chatContainer) return;

    // --- Конфіг та DOM ---
    const config = {
        localIpAddress: '192.168.1.5', // !!! ВАШ ЛОКАЛЬНИЙ IP !!!
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        currentUserId: parseInt(chatContainer.dataset.currentUserId, 10)
    };
    const dom = {
        tabs: document.querySelectorAll('.tab-link'),
        listContainer: document.querySelector('.list-container'),
        chatWelcome: document.getElementById('chat-welcome'),
        chatWindow: document.getElementById('chat-window'),
        messageList: document.getElementById('message-list'),
        messageForm: document.getElementById('message-form'),
        messageInput: document.getElementById('message-body-input')
    };
    
    // --- Збереження ---
    let activeChat = { type: null, id: null };
    let conn;

    // --- Збереження стану ---
    const saveState = () => { /* ... код з попередніх відповідей ... */ };
    const restoreState = () => { /* ... код з попередніх відповідей ... */ };
    window.addEventListener('beforeunload', saveState);

    // --- WebSocket ---
    const connectWebSocket = () => {
        const host = (window.location.hostname.includes('localhost') || window.location.hostname.includes('127.0.0.1')) ? 'localhost' : config.localIpAddress;
        conn = new WebSocket(`ws://${host}:8080`);
        conn.onopen = () => console.log("WebSocket Connected!");
        conn.onclose = () => setTimeout(connectWebSocket, 5000);
        conn.onmessage = (e) => {
            const message = JSON.parse(e.data);
            if (message && message.sender_id !== config.currentUserId) {
                // ... логіка відображення та сповіщень ...
            }
            checkForNotifications(config).then(counts => updateAllBadges(counts));
        };
    };

    // --- Логіка ---
    const activateChat = async (type, id, listItem) => {
        if (activeChat.type === type && activeChat.id === id) return;
        activeChat = { type, id };
        
        document.querySelectorAll('.conversation-list li').forEach(li => li.classList.remove('active'));
        listItem.classList.add('active');

        dom.chatWelcome.style.display = 'none';
        dom.chatWindow.style.display = 'flex';

        const messages = await fetchMessages(type, id, config);
        displayMessages(messages, dom.messageList, config.currentUserId);
        
        await markAsRead(type, id, config);
        const counts = await checkForNotifications(config);
        updateAllBadges(counts);
    };

    // --- Обробники подій ---
    dom.tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            dom.tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(tab.dataset.tab)?.classList.add('active');
        });
    });

    dom.listContainer.addEventListener('click', (e) => {
        const listItem = e.target.closest('li');
        if (!listItem) return;
        activateChat(listItem.dataset.type, parseInt(listItem.dataset.itemId, 10), listItem);
    });

    dom.messageForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const body = dom.messageInput.value.trim();
        if (!body || !activeChat.id || !conn || conn.readyState !== WebSocket.OPEN) return;

        const payload = { type: 'message', sender_id: config.currentUserId, body, chat_type: activeChat.type, chat_id: activeChat.id };
        conn.send(JSON.stringify(payload));
        
        appendMessage({ sender_id: config.currentUserId, sender_name: 'Ви', body, created_at: new Date().toISOString() }, dom.messageList, config.currentUserId);
        
        dom.messageInput.value = '';
        dom.messageInput.style.height = 'auto'; // Скидаємо висоту textarea
    });

    // --- Ініціалізація ---
    initTextareaAutoResize(dom.messageInput, dom.messageForm);
    connectWebSocket();
    setInterval(() => checkForNotifications(config).then(counts => { if (counts) updateAllBadges(counts); }), 10000);
    checkForNotifications(config).then(counts => { if (counts) updateAllBadges(counts); });
    restoreState();
});