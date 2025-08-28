// public/resources/js/messages-widget/state.js

export const state = {
    activeChat: { type: null, id: null },
    unreadConversations: new Set(JSON.parse(localStorage.getItem('chat_unread_conversations')) || [])
};

export function saveState(dom) {
    const activeTab = dom.userTab.classList.contains('active') ? 'users' : 'groups';
    localStorage.setItem('chat_active_tab', activeTab);
    localStorage.setItem('chat_unread_conversations', JSON.stringify(Array.from(state.unreadConversations)));
}

export function loadState(dom) {
    const activeTab = localStorage.getItem('chat_active_tab');
    if (activeTab) {
        const tabToActivate = dom.tabsContainer.querySelector(`[data-tab="${activeTab}"]`);
        if (tabToActivate) tabToActivate.click();
    }
}

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

export function loadDraft(dom, initTextareaAutoResize) {
    if (state.activeChat.id) {
        const draftKey = `chat_draft_${state.activeChat.type}_${state.activeChat.id}`;
        dom.messageInput.value = localStorage.getItem(draftKey) || '';
        initTextareaAutoResize(dom.messageInput, dom.messageForm);
    }
}