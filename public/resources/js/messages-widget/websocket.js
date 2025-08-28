// public/resources/js/messages-widget/websocket.js

import { state, saveState } from './state.js';
import { renderMessage, updateBlinkingUI } from './ui.js';

export function connectWebSocket(config, dom) {
    const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
    const conn = new WebSocket(`${protocol}://${config.wsHost}:8080`);

    conn.onopen = () => {
        console.log("WebSocket connected.");
        conn.send(JSON.stringify({ type: 'auth', token: config.wsToken }));
    };

    conn.onmessage = (e) => {
        const msg = JSON.parse(e.data);
        const isMyMessage = msg.sender_id == config.currentUserId;
        
        if (!isMyMessage) {
            try { dom.notificationSound.play(); } catch(err) {}
            const convId = msg.group_id ? `group-${msg.group_id}` : `user-${msg.sender_id}`;

            if (!dom.widget.classList.contains('open') || 
                (state.activeChat.type === 'user' && state.activeChat.id != msg.sender_id) || 
                (state.activeChat.type === 'group' && state.activeChat.id != msg.group_id)) {
                
                state.unreadConversations.add(convId);
                saveState(dom);
                updateBlinkingUI(dom);
            }
        }

        const isRelevantPrivate = !msg.group_id && state.activeChat.type === 'user' && (msg.sender_id == state.activeChat.id || (isMyMessage && msg.recipient_id == state.activeChat.id));
        const isRelevantGroup = msg.group_id && state.activeChat.type === 'group' && msg.group_id == state.activeChat.id;

        if (isRelevantPrivate || isRelevantGroup) {
            dom.messageList.appendChild(renderMessage(msg, config.currentUserId));
            dom.messageList.scrollTop = dom.messageList.scrollHeight;
        }
    };

    conn.onclose = () => {
        console.log("WebSocket disconnected. Reconnecting...");
        setTimeout(() => connectWebSocket(config, dom), 5000);
    };

    return conn;
}