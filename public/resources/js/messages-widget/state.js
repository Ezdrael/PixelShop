// public/resources/js/messages-widget/state.js

export const state = {
    activeChat: { type: null, id: null },
    // Використовуємо Set для унікальних ID непрочитаних розмов
    unreadConversations: new Set(JSON.parse(localStorage.getItem('chat_unread_conversations')) || [])
};

// Зберігає стан у localStorage
export function saveState(dom) {
    const activeTab = dom.userTab?.classList.contains('active') ? 'users' : 'groups';
    localStorage.setItem('chat_active_tab', activeTab);
    localStorage.setItem('chat_unread_conversations', JSON.stringify(Array.from(state.unreadConversations)));
}

// Завантажує стан з localStorage
export function loadState(dom) {
    const activeTab = localStorage.getItem('chat_active_tab');
    if (activeTab && dom.tabsContainer) {
        const tabToActivate = dom.tabsContainer.querySelector(`[data-tab="${activeTab}"]`);
        if (tabToActivate) tabToActivate.click();
    }
}

// Зберігає чернетку повідомлення
export function saveDraft(dom) {
    if (state.activeChat.id) {
        const draftKey = `chat_draft_${state.activeChat.type}_${state.activeChat.id}`;
        const text = dom.messageInput.value;
        if (text) {
            localStorage.setItem(draftKey, text);
        } else {
            localStorage.removeItem(draftKey);
        }
    }
}

// Завантажує чернетку повідомлення
export function loadDraft(dom, initTextareaAutoResize) {
    if (state.activeChat.id) {
        const draftKey = `chat_draft_${state.activeChat.type}_${state.activeChat.id}`;
        dom.messageInput.value = localStorage.getItem(draftKey) || '';
        if (initTextareaAutoResize) {
            initTextareaAutoResize(dom.messageInput);
        }
    }
}