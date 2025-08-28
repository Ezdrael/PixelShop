document.addEventListener('DOMContentLoaded', () => {
    const config = {
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        hasAddPermission: !!document.querySelector('#clipboard-widget') 
    };

    const dom = {
        toggleBtn: document.getElementById('clipboard-toggle-btn'),
        widget: document.getElementById('clipboard-widget'),
        closeBtn: document.querySelector('.clipboard-close-btn'),
        list: document.getElementById('clipboard-list'),
        clearBtn: document.getElementById('clear-clipboard-btn')
    };

    if (!dom.toggleBtn || !dom.widget) return;

    let lastCopiedText = '';

    const api = {
        async getItems() {
            const response = await fetch(`${config.baseUrl}/clipboard/get`);
            return response.json();
        },
        async addItem(content) {
            if (!content || !content.trim()) {
                return;
            }
            const response = await fetch(`${config.baseUrl}/clipboard/add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ content })
            });
            return response.json();
        },
        async clearItems() {
            const response = await fetch(`${config.baseUrl}/clipboard/clear`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken }
            });
            return response.json();
        }
    };

    const renderItem = (item) => {
        const li = document.createElement('li');
        const contentDiv = document.createElement('div');
        contentDiv.className = 'clipboard-content';
        contentDiv.textContent = item.content;
        const copyBtn = document.createElement('button');
        copyBtn.className = 'action-btn clipboard-copy-btn';
        copyBtn.title = 'Скопіювати';
        copyBtn.dataset.content = item.content;
        copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
        li.appendChild(contentDiv);
        li.appendChild(copyBtn);
        return li;
    };

    const loadItems = async () => {
        dom.list.innerHTML = '<li>Завантаження...</li>';
        try {
            const result = await api.getItems();
            dom.list.innerHTML = '';
            if (result.success && result.items && result.items.length > 0) {
                result.items.forEach(item => dom.list.appendChild(renderItem(item)));
            } else {
                dom.list.innerHTML = '<li class="empty-clipboard">Буфер обміну порожній.</li>';
            }
        } catch (error) {
            dom.list.innerHTML = '<li class="empty-clipboard">Не вдалося завантажити дані.</li>';
            console.error("Clipboard load error:", error);
        }
    };

    // --- ОСНОВНА ЛОГІКА: Відстеження копіювання ---
    document.addEventListener('copy', () => {
        if (!config.hasAddPermission) return;
        
        // !! КЛЮЧОВА ЗМІНА: Перевіряємо, чи джерело копіювання не знаходиться всередині віджета !!
        const selection = document.getSelection();
        const isCopyingFromWidget = selection.anchorNode && dom.widget.contains(selection.anchorNode.parentElement);
        
        if (isCopyingFromWidget) {
            return; // Ігноруємо копіювання з самого буфера обміну
        }

        const selectedText = selection.toString().trim();
        if (selectedText && selectedText !== lastCopiedText) {
            lastCopiedText = selectedText;
            api.addItem(selectedText);
            if (dom.widget.classList.contains('open')) {
                loadItems();
            }
        }
    });

    // --- Обробники подій для віджета ---
    dom.toggleBtn.addEventListener('click', () => {
        dom.widget.classList.toggle('open');
        if (dom.widget.classList.contains('open')) {
            loadItems();
        }
    });
    
    dom.closeBtn.addEventListener('click', () => dom.widget.classList.remove('open'));

    if (dom.clearBtn) {
        dom.clearBtn.addEventListener('click', async () => {
            if (confirm('Ви впевнені, що хочете повністю очистити буфер обміну?')) {
                const result = await api.clearItems();
                if (result.success) {
                    loadItems();
                } else {
                    alert('Не вдалося очистити буфер.');
                }
            }
        });
    }

    dom.list.addEventListener('click', (e) => {
        const copyBtn = e.target.closest('.clipboard-copy-btn');
        if (!copyBtn) return;
        const content = copyBtn.dataset.content;
        navigator.clipboard.writeText(content).then(() => {
            copyBtn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                dom.widget.classList.remove('open');
                copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
            }, 300);
        });
    });
});