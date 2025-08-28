import { initTextareaAutoResize } from './_msg_ui.js'; // <-- КРОК 1: ІМПОРТУВАТИ ФУНКЦІЮ

document.addEventListener('DOMContentLoaded', () => {
    const config = {
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        currentUserId: parseInt(document.body.dataset.currentUserId, 10) || 0,
        wsHost: window.location.hostname
    };

    const dom = {
        toggleBtn: document.getElementById('messages-toggle-btn'),
        widget: document.getElementById('messages-widget'),
        closeBtn: document.querySelector('.messages-close-btn'),
        tabsContainer: document.querySelector('.chat-tabs'),
        conversationsUsers: document.getElementById('conversations-list-users'),
        conversationsGroups: document.getElementById('conversations-list-groups'),
        settingsBtn: document.getElementById('chat-settings-btn'),
        sidebarContent: document.querySelector('.sidebar-content'),
        settingsPanel: document.getElementById('chat-settings-panel'),
        groupListContainer: document.getElementById('group-list-container'),
        groupFormContainer: document.getElementById('group-form-container'),
        createGroupShowFormBtn: document.getElementById('create-group-show-form-btn'),
        chatWelcome: document.getElementById('chat-welcome'),
        chatWindow: document.getElementById('chat-window'),
        messageList: document.getElementById('message-list'),
        messageForm: document.getElementById('message-form'),
        messageInput: document.getElementById('message-body-input'),
        unreadBadge: document.getElementById('unread-counter-widget')
    };

    if (!dom.toggleBtn || !dom.widget) return;

    let activeChat = { type: null, id: null };
    let conn;
    let chatSettingsData = { users: [], groups: [] };

    // --- API Calls ---
    const api = {
        async getConversations() {
            const res = await fetch(`${config.baseUrl}/messages/get-conversations`);
            return res.json();
        },
        async fetchMessages(type, id) {
            const fd = new FormData();
            fd.append('chat_type', type);
            fd.append('chat_id', id);
            const res = await fetch(`${config.baseUrl}/messages/fetch`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken },
                body: fd
            });
            return res.json();
        },
        async sendMessage(type, id, body) {
            const fd = new FormData();
            fd.append('chat_type', type);
            fd.append('chat_id', id);
            fd.append('body', body);
            const res = await fetch(`${config.baseUrl}/messages/send`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken },
                body: fd
            });
            return res.json();
        },
        async markAsRead(type, id) {
            const fd = new FormData();
            fd.append('chat_type', type);
            fd.append('chat_id', id);
            await fetch(`${config.baseUrl}/messages/markread`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken }
            });
        },
        async getUnreadCount() {
            const res = await fetch(`${config.baseUrl}/messages/unread`);
            return res.json();
        },
        async getChatSettings() {
            const res = await fetch(`${config.baseUrl}/messages/settings`);
            return res.json();
        },
        async createGroup(name, members) {
             const res = await fetch(`${config.baseUrl}/messages/groups/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ name, members })
            });
            return res.json();
        },
        async updateGroup(id, name, members) {
            const res = await fetch(`${config.baseUrl}/messages/groups/update/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ name, members })
            });
            return res.json();
        },
        async deleteGroup(id) {
            const res = await fetch(`${config.baseUrl}/messages/groups/delete/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken }
            });
            return res.json();
        }
    };

    // --- UI Logic ---
    const renderConversation = (item, type) => {
        const li = document.createElement('li');
        li.dataset.type = type;
        li.dataset.itemId = item.id;
        li.innerHTML = `<div class="conv-title">${item.name || item.group_name}</div>`;
        return li;
    };

    const renderMessage = (msg) => {
        const isOwn = msg.sender_id === config.currentUserId;
        const item = document.createElement('div');
        item.className = `message-item ${isOwn ? 'own' : ''}`;
        const date = new Date(msg.created_at).toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
        item.innerHTML = `
            <div class="meta"><strong>${isOwn ? 'Ви' : msg.sender_name}</strong> <span>${date}</span></div>
            <div class="body">${msg.body.replace(/\\n/g, '<br>')}</div>
        `;
        return item;
    };

    const updateUnreadBadge = (count) => {
        if (dom.unreadBadge) {
            dom.unreadBadge.textContent = count > 0 ? count : '';
            dom.unreadBadge.style.display = count > 0 ? 'flex' : 'none';
        }
    };
    
    const renderGroupForm = (group = null) => {
        const isEditing = group !== null;
        const selectedMembers = new Set(isEditing ? group.members : []);
        const usersHtml = chatSettingsData.users
            .filter(user => user.id !== config.currentUserId)
            .map(user => `
                <label>
                    <input type="checkbox" value="${user.id}" ${selectedMembers.has(user.id) ? 'checked' : ''}>
                    ${user.name}
                </label>
            `).join('');
        dom.groupFormContainer.innerHTML = `
            <form id="group-form" class="group-form">
                <input type="hidden" name="group_id" value="${isEditing ? group.id : ''}">
                <input type="text" name="group_name" class="form-control" placeholder="Назва групи" value="${isEditing ? group.group_name : ''}" required>
                <div class="members-list">${usersHtml}</div>
                <div class="form-actions">
                    <button type="button" id="cancel-group-form-btn" class="btn-secondary">Скасувати</button>
                    <button type="submit" class="btn-primary">${isEditing ? 'Оновити' : 'Створити'}</button>
                </div>
            </form>
        `;
        dom.groupFormContainer.style.display = 'block';
    };

    const renderGroupList = () => {
        dom.groupListContainer.innerHTML = '';
        if (chatSettingsData.groups.length > 0) {
            chatSettingsData.groups.forEach(group => {
                const item = document.createElement('div');
                item.className = 'group-item';
                item.dataset.groupId = group.id;
                item.innerHTML = `
                    <span>${group.group_name}</span>
                    <div class="group-actions">
                        <button class="action-btn edit-group-btn"><i class="fas fa-pencil-alt"></i></button>
                        <button class="action-btn delete-group-btn"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                dom.groupListContainer.appendChild(item);
            });
        } else {
            dom.groupListContainer.innerHTML = '<p class="empty-list">Ви ще не створили жодної групи.</p>';
        }
    };

    const loadSettings = async () => {
        const result = await api.getChatSettings();
        if (result.success) {
            chatSettingsData = result;
            renderGroupList();
        }
    };

    // --- Main Logic ---
    const loadConversations = async () => {
        dom.conversationsUsers.innerHTML = '<li>Завантаження...</li>';
        dom.conversationsGroups.innerHTML = '<li>Завантаження...</li>';
        const result = await api.getConversations();
        dom.conversationsUsers.innerHTML = '';
        dom.conversationsGroups.innerHTML = '';
        if (result.success) {
            result.users.forEach(user => dom.conversationsUsers.appendChild(renderConversation(user, 'user')));
            result.groups.forEach(group => dom.conversationsGroups.appendChild(renderConversation(group, 'group')));
        } else {
             dom.conversationsUsers.innerHTML = '<li>Помилка завантаження</li>';
        }
    };

    const activateChat = async (type, id, listItem) => {
        activeChat = { type, id };
        document.querySelectorAll('.conversation-list li').forEach(li => li.classList.remove('active'));
        listItem.classList.add('active');
        dom.chatWelcome.style.display = 'none';
        dom.chatWindow.style.display = 'flex';
        const result = await api.fetchMessages(type, id);
        dom.messageList.innerHTML = '';
        if (result.success && result.messages) {
            result.messages.forEach(msg => dom.messageList.appendChild(renderMessage(msg)));
            dom.messageList.scrollTop = dom.messageList.scrollHeight;
        }
        api.markAsRead(type, id);
        checkNotifications();
    };

    const checkNotifications = async () => {
        const result = await api.getUnreadCount();
        if (result.success && result.counts) {
            const total = Object.values(result.counts).reduce((sum, count) => sum + count, 0);
            updateUnreadBadge(total);
        }
    };

    const connectWebSocket = () => {
        conn = new WebSocket(`ws://${config.wsHost}:8080`);
        conn.onopen = () => {
            conn.send(JSON.stringify({ type: 'auth', user_id: config.currentUserId }));
        };
        conn.onmessage = (e) => {
            const msg = JSON.parse(e.data);
            if ((msg.group_id && msg.group_id === activeChat.id) || 
                (msg.recipient_id && (msg.recipient_id === activeChat.id || msg.sender_id === activeChat.id))) {
                dom.messageList.appendChild(renderMessage(msg));
                dom.messageList.scrollTop = dom.messageList.scrollHeight;
            }
            checkNotifications();
        };
        conn.onclose = () => {
            setTimeout(connectWebSocket, 5000);
        };
    };

    // --- Event Listeners ---
    dom.toggleBtn.addEventListener('click', () => {
        dom.widget.classList.toggle('open');
        if (dom.widget.classList.contains('open')) {
            loadConversations();
        }
    });

    dom.closeBtn.addEventListener('click', () => dom.widget.classList.remove('open'));
    
    if (dom.tabsContainer) {
        dom.tabsContainer.addEventListener('click', (e) => {
            const tabBtn = e.target.closest('.tab-link');
            if (!tabBtn) return;
            const tabId = tabBtn.dataset.tab;
            document.querySelectorAll('.chat-tabs .tab-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.list-container .tab-content').forEach(c => c.classList.remove('active'));
            tabBtn.classList.add('active');
            document.getElementById(`conversations-list-${tabId}`)?.classList.add('active');
        });
    }

    if (dom.settingsBtn) {
        dom.settingsBtn.addEventListener('click', () => {
            dom.sidebarContent.classList.toggle('settings-open');
            if (dom.sidebarContent.classList.contains('settings-open')) {
                loadSettings();
            } else {
                dom.groupFormContainer.style.display = 'none';
            }
        });
    }
    
    if(dom.createGroupShowFormBtn) {
        dom.createGroupShowFormBtn.addEventListener('click', () => renderGroupForm());
    }

    if (dom.settingsPanel) {
        dom.settingsPanel.addEventListener('click', async e => {
            const target = e.target;
            if (target.id === 'cancel-group-form-btn') {
                dom.groupFormContainer.style.display = 'none';
            }
            const groupItem = target.closest('.group-item');
            if (!groupItem) return;
            const groupId = groupItem.dataset.groupId;
            if (target.closest('.edit-group-btn')) {
                const group = chatSettingsData.groups.find(g => g.id == groupId);
                renderGroupForm(group);
            }
            if (target.closest('.delete-group-btn')) {
                if(confirm('Ви впевнені, що хочете видалити групу?')) {
                    await api.deleteGroup(groupId);
                    loadSettings();
                    loadConversations();
                }
            }
        });
        dom.settingsPanel.addEventListener('submit', async e => {
            e.preventDefault();
            if(e.target.id !== 'group-form') return;
            const form = e.target;
            const groupId = form.elements['group_id'].value;
            const name = form.elements['group_name'].value;
            const members = Array.from(form.querySelectorAll('input[type="checkbox"]:checked')).map(cb => parseInt(cb.value));
            if (groupId) {
                await api.updateGroup(groupId, name, members);
            } else {
                await api.createGroup(name, members);
            }
            dom.groupFormContainer.style.display = 'none';
            loadSettings();
            loadConversations();
        });
    }

    [dom.conversationsUsers, dom.conversationsGroups].forEach(list => {
        if(list) {
            list.addEventListener('click', (e) => {
                const listItem = e.target.closest('li');
                if (listItem && listItem.dataset.itemId) {
                    activateChat(listItem.dataset.type, parseInt(listItem.dataset.itemId), listItem);
                }
            });
        }
    });

    dom.messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = dom.messageInput.value.trim();
        if (!body || !activeChat.id) return;

        const messageBody = body;
        dom.messageInput.value = '';
        dom.messageInput.style.height = 'auto';
        
        const result = await api.sendMessage(activeChat.type, activeChat.id, messageBody);
        
        if (result.success) {
            if (!conn || conn.readyState !== WebSocket.OPEN) {
                dom.messageList.appendChild(renderMessage(result.message));
                dom.messageList.scrollTop = dom.messageList.scrollHeight;
            }
        } else {
            dom.messageInput.value = messageBody;
            alert(result.message || 'Помилка відправки повідомлення.');
        }
    });

    // --- Initialization ---
    initTextareaAutoResize(dom.messageInput, dom.messageForm); // <-- КРОК 2: ВИКЛИКАТИ ФУНКЦІЮ
    connectWebSocket();
    setInterval(checkNotifications, 30000);
    checkNotifications();
});