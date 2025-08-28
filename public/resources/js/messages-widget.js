// public/resources/js/messages-widget.js

import { initTextareaAutoResize } from './messages-widget/msg_ui.js';
import { getApi } from './messages-widget/api.js';
import { state, saveState, loadState, saveDraft } from './messages-widget/state.js';
import { loadConversations, activateChat, updateBlinkingUI } from './messages-widget/ui.js';
import { connectWebSocket } from './messages-widget/websocket.js';

document.addEventListener('DOMContentLoaded', () => {
    const config = {
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        currentUserId: parseInt(document.body.dataset.currentUserId, 10) || 0,
        wsHost: window.location.hostname,
        wsToken: document.body.dataset.wsToken || ''
    };

    const dom = {
        toggleBtn: document.getElementById('messages-toggle-btn'),
        widget: document.getElementById('messages-widget'),
        closeBtn: document.querySelector('.messages-close-btn'),
        tabsContainer: document.querySelector('.chat-tabs'),
        userTab: document.querySelector('.tab-link[data-tab="users"]'),
        groupTab: document.querySelector('.tab-link[data-tab="groups"]'),
        groupListContainer: document.getElementById('group-list-container'),
        conversationsUsers: document.getElementById('conversations-list-users'),
        conversationsGroups: document.getElementById('conversations-list-groups'),
        settingsBtn: document.getElementById('chat-settings-btn'),
        settingsPanel: document.getElementById('chat-settings-panel'),
        sidebarContent: document.querySelector('.sidebar-content'),
        createGroupShowFormBtn: document.getElementById('create-group-show-form-btn'),
        groupManagementModal: document.getElementById('groupManagementModalOverlay'),
        groupModalTitle: document.getElementById('groupModalTitle'),
        groupModalBody: document.getElementById('groupModalBody'),
        chatWelcome: document.getElementById('chat-welcome'),
        chatWindow: document.getElementById('chat-window'),
        messageList: document.getElementById('message-list'),
        messageForm: document.getElementById('message-form'),
        messageInput: document.getElementById('message-body-input'),
        emojiBtn: document.getElementById('emoji-btn'),
        emojiPicker: document.getElementById('emoji-picker'),
        notificationSound: document.getElementById('notification-sound'),
        sendSound: document.getElementById('send-sound')
    };

    if (!dom.widget || !dom.toggleBtn) return;

    let api = null;
    let conn = null;
    let isInitialized = false;

    function initializeChat() {
        if (isInitialized) return;
        
        api = getApi(config);
        conn = connectWebSocket(config, dom);
        loadConversations(dom, api);
        setupEventListeners();

        isInitialized = true;
    }

    function setupEventListeners() {
        // --- Кнопка закриття віджета ---
        dom.closeBtn.addEventListener('click', () => {
            dom.widget.classList.remove('open');
            if (dom.emojiPicker) dom.emojiPicker.style.display = 'none';
        });

        // --- Вкладки "Користувачі" / "Групи" ---
        dom.tabsContainer.addEventListener('click', (e) => {
            const tabBtn = e.target.closest('.tab-link');
            if (!tabBtn) return;
            const tabId = tabBtn.dataset.tab;

            document.querySelectorAll('.chat-tabs .tab-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.list-container .tab-content').forEach(c => c.classList.remove('active'));
            tabBtn.classList.add('active');
            const contentEl = document.getElementById(`conversations-list-${tabId}`);
            if (contentEl) contentEl.classList.add('active');
            
            saveState(dom);
        });

        // --- Списки чатів ---
        [dom.conversationsUsers, dom.conversationsGroups].forEach(list => {
            if (list) {
                list.addEventListener('click', (e) => {
                    const listItem = e.target.closest('li');
                    if (listItem && listItem.dataset.itemId) {
                        saveDraft(dom);
                        activateChat(listItem.dataset.type, parseInt(listItem.dataset.itemId, 10), listItem, dom, api, config, initTextareaAutoResize);
                    }
                });
            }
        });

        // --- Форма відправки повідомлення ---
        dom.messageForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const body = dom.messageInput.value.trim();
            if (!body || !state.activeChat.id || !conn || conn.readyState !== WebSocket.OPEN) return;
            const payload = { type: 'message', sender_id: config.currentUserId, body, chat_type: state.activeChat.type, chat_id: state.activeChat.id };
            conn.send(JSON.stringify(payload));
            if (dom.sendSound) { try { dom.sendSound.play(); } catch(err) {} }
            localStorage.removeItem(`chat_draft_${state.activeChat.type}_${state.activeChat.id}`);
            dom.messageInput.value = '';
            initTextareaAutoResize(dom.messageInput, dom.messageForm);
        });

        dom.messageInput.addEventListener('input', () => saveDraft(dom));

        //-- Налаштування чату ---
        if (dom.settingsBtn) {
            dom.settingsBtn.addEventListener('click', () => {
                dom.sidebarContent.classList.toggle('settings-open');
                if (dom.sidebarContent.classList.contains('settings-open')) {
                    loadSettings(dom, api, (data) => { chatSettingsData = data; });
                }
            });
        }

        //-- Створення групи ---
        if (dom.createGroupShowFormBtn) {
            dom.createGroupShowFormBtn.addEventListener('click', () => {
                showGroupModal(null, dom, api, chatSettingsData, () => {
                    loadSettings(dom, api, (data) => { chatSettingsData = data; });
                    loadConversations(dom, api, config);
                });
            });
        }

        //-- Налаштування групи ---
        if (dom.settingsPanel) {
            dom.settingsPanel.addEventListener('click', e => {
                const groupItem = e.target.closest('.group-item');
                if (!groupItem) return;
                const groupId = groupItem.dataset.groupId;
                const group = chatSettingsData.groups.find(g => g.id == groupId);
                
                if (e.target.closest('.edit-group-btn')) {
                    showGroupModal(group, dom, api, chatSettingsData, () => {
                        loadSettings(dom, api, (data) => { chatSettingsData = data; });
                        loadConversations(dom, api, config);
                    });
                }
                
                if (e.target.closest('.delete-group-btn')) {
                    if (confirm(`Ви впевнені, що хочете видалити групу "${group.group_name}"?`)) {
                        api.deleteGroup(groupId).then(result => {
                            if(result.success) {
                                loadSettings(dom, api, (data) => { chatSettingsData = data; });
                                loadConversations(dom, api, config);
                            }
                        });
                    }
                }
            });
        }

        // --- Логіка Emoji ---
        if (dom.emojiBtn && dom.emojiPicker) {
            dom.emojiBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dom.emojiPicker.style.display = dom.emojiPicker.style.display === 'block' ? 'none' : 'block';
            });
            dom.emojiPicker.addEventListener('click', (e) => e.stopPropagation());
            dom.emojiPicker.addEventListener('emoji-click', event => {
                dom.messageInput.value += event.detail.unicode;
                dom.messageInput.focus();
            });
        }
        
        // Закриття вікна емодзі при кліку поза ним
        document.body.addEventListener('click', () => {
            if (dom.emojiPicker && dom.emojiPicker.style.display === 'block') {
                dom.emojiPicker.style.display = 'none';
            }
        }, true); // Використовуємо capturing, щоб подія спрацювала раніше
    }

    // --- Головний обробник для відкриття віджета ---
    dom.toggleBtn.addEventListener('click', () => {
        dom.widget.classList.toggle('open');
        dom.toggleBtn.classList.remove('blinking');
        
        if (dom.widget.classList.contains('open')) {
            initializeChat(); // Ініціалізуємо все тільки при першому відкритті
        }
    });
    
    // Ініціалізуємо лише ті частини, що потрібні до відкриття віджету
    initTextareaAutoResize(dom.messageInput, dom.messageForm);
    loadState(dom);
    updateBlinkingUI(dom);
});