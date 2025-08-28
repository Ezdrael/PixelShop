import { state, saveState, loadDraft } from './state.js';

function renderMessage(msg, currentUserId) {
    const isOwn = msg.sender_id == currentUserId;
    const item = document.createElement('div');
    item.className = `message-item ${isOwn ? 'own' : ''}`;
    const date = new Date(msg.created_at).toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
    item.innerHTML = `
        <div class="meta"><strong>${isOwn ? 'Ви' : msg.sender_name}</strong> <span>${date}</span></div>
        <div class="body">${msg.body.replace(/\n/g, '<br>')}</div>
    `;
    return item;
}

function updateBlinkingUI(dom) {
    document.querySelectorAll('.conversation-list li.blinking').forEach(el => el.classList.remove('blinking'));
    if(dom.userTab) dom.userTab.classList.remove('blinking');
    if(dom.groupTab) dom.groupTab.classList.remove('blinking');

    let hasUserUnread = false;
    let hasGroupUnread = false;
    
    state.unreadConversations.forEach(convId => {
        const [type, id] = convId.split('-');
        const listItem = document.querySelector(`.conversation-list li[data-type="${type}"][data-item-id="${id}"]`);
        if (listItem) listItem.classList.add('blinking');
        
        if (type === 'user') hasUserUnread = true;
        if (type === 'group') hasGroupUnread = true;
    });
    
    if(dom.userTab) dom.userTab.classList.toggle('blinking', hasUserUnread);
    if(dom.groupTab) dom.groupTab.classList.toggle('blinking', hasGroupUnread);
    if(dom.toggleBtn) dom.toggleBtn.classList.toggle('blinking', state.unreadConversations.size > 0);
}

async function loadConversations(dom, api) {
    const result = await api.getConversations();
    dom.conversationsUsers.innerHTML = '';
    dom.conversationsGroups.innerHTML = '';
    if (result.success) {
        result.users.forEach(user => {
            const li = document.createElement('li');
            li.dataset.type = 'user';
            li.dataset.itemId = user.id;
            li.innerHTML = `<div class="conv-title">${user.name}</div>`;
            dom.conversationsUsers.appendChild(li);
        });
        result.groups.forEach(group => {
            const li = document.createElement('li');
            li.dataset.type = 'group';
            li.dataset.itemId = group.id;
            li.innerHTML = `<div class="conv-title">${group.group_name}</div>`;
            dom.conversationsGroups.appendChild(li);
        });
        updateBlinkingUI(dom);
    }
}

async function activateChat(type, id, listItem, dom, api, config, initTextareaAutoResize) { // <--- ВИПРАВЛЕНО: Приймаємо 'config'
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
        // <--- ВИПРАВЛЕНО: Використовуємо 'config' для отримання ID
        result.messages.forEach(msg => dom.messageList.appendChild(renderMessage(msg, config.currentUserId)));
        dom.messageList.scrollTop = dom.messageList.scrollHeight;
    }
}

// Функція для керування модальним вікном груп (може бути розширена)
function showGroupModal(group, dom, api, chatSettingsData, onSaveCallback) {
    const isEditing = group !== null;
    dom.groupModalTitle.textContent = isEditing ? `Редагування: ${group.group_name}` : 'Створення нової групи';
    
    const selectedMembers = new Set(isEditing ? group.members.map(m => m.user_id) : []);
    const usersHtml = chatSettingsData.users
        .filter(user => user.id !== config.currentUserId)
        .map(user => `
            <label>
                <input type="checkbox" name="members" value="${user.id}" ${selectedMembers.has(user.id) ? 'checked' : ''}>
                ${user.name}
            </label>
        `).join('');
    
    dom.groupModalBody.innerHTML = `
        <form id="group-form" class="group-form">
            <input type="hidden" name="group_id" value="${isEditing ? group.id : ''}">
            <div class="input-group">
                <label>Назва групи</label>
                <input type="text" name="group_name" class="form-control" placeholder="Назва" value="${isEditing ? group.group_name : ''}" required>
            </div>
            <div class="input-group">
                <label>Учасники</label>
                <div class="members-list">${usersHtml}</div>
            </div>
            <div class="form-actions">
                <button type="button" class="modal-btn cancel">Скасувати</button>
                <button type="submit" class="modal-btn confirm">${isEditing ? 'Оновити' : 'Створити'}</button>
            </div>
        </form>
    `;
    dom.groupManagementModal.style.display = 'flex';

    const form = dom.groupModalBody.querySelector('#group-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const groupId = form.elements['group_id'].value;
        const name = form.elements['group_name'].value;
        const members = Array.from(form.querySelectorAll('input[type="checkbox"]:checked')).map(cb => parseInt(cb.value));

        const result = groupId ? await api.updateGroup(groupId, name, members) : await api.createGroup(name, members);

        if (result.success) {
            dom.groupManagementModal.style.display = 'none';
            if(onSaveCallback) onSaveCallback();
        } else {
            alert(result.message || "Сталася помилка");
        }
    });
}

export { renderMessage, updateBlinkingUI, loadConversations, activateChat, showGroupModal };