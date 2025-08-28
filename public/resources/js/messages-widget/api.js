// public/resources/js/messages-widget/api.js

export function getApi(config) {
    return {
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
}