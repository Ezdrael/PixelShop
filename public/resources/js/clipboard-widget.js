document.addEventListener('DOMContentLoaded', () => {
    const config = {
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        hasAddPermission: !!document.querySelector('#clipboard-widget') 
    };

    const dom = {
        widget: document.getElementById('clipboard-widget'),
        list: document.getElementById('clipboard-list'),
        clearBtn: document.getElementById('clear-clipboard-btn'),
        deleteModal: document.getElementById('deleteModalOverlay'),
        modalTitle: document.getElementById('modalTitle'),
        modalBody: document.getElementById('modalBody'),
        modalConfirmBtn: document.getElementById('modalConfirmBtn')
    };

    if (!dom.widget) return;

    let isCopyingFromWidget = false;
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
        if (isCopyingFromWidget) { // <-- ДОДАЙТЕ ЦЮ ПЕРЕВІРКУ
            return;
        }

        if (!config.hasAddPermission) return;
        
        const selection = document.getSelection();
        
        // Попередню перевірку можна залишити як додатковий захист
        const isSelectionInsideWidget = selection.anchorNode && dom.widget.contains(selection.anchorNode.parentElement);
        if (isSelectionInsideWidget) {
            return;
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

    // --- Обробники подій для внутрішньої логіки віджета ---
    
    // Завантажуємо дані, коли віджет стає видимим
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            if (mutation.attributeName === 'class' && dom.widget.classList.contains('open')) {
                loadItems();
            }
        }
    });
    observer.observe(dom.widget, { attributes: true });

    // Обробник для кнопки очищення буфера
    if (dom.clearBtn) {
        dom.clearBtn.addEventListener('click', () => {
            dom.modalTitle.textContent = 'Очищення буфера обміну';
            dom.modalBody.innerHTML = '<p>Ви впевнені, що хочете повністю очистити буфер обміну? Ця дія є незворотною.</p>';
            dom.deleteModal.style.display = 'flex';

            dom.modalConfirmBtn.addEventListener('click', async function handleClear() {
                dom.deleteModal.style.display = 'none';
                const result = await api.clearItems();
                if (result.success) {
                    loadItems();
                } else {
                    alert('Не вдалося очистити буфер.');
                }
            }, { once: true });
        });
    }

    // Обробник для копіювання з буфера
    dom.list.addEventListener('click', (e) => {

        const copyBtn = e.target.closest('.clipboard-copy-btn');
        if (!copyBtn) return;

        const content = copyBtn.dataset.content;
        isCopyingFromWidget = true;

        const copyToClipboard = (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed'; // Запобігаємо прокрутці
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                } catch (err) {
                    console.error('Не вдалося скопіювати текст: ', err);
                }
                document.body.removeChild(textArea);
            }
        };

        copyToClipboard(content);

        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
        }, 800);

        setTimeout(() => { isCopyingFromWidget = false; }, 100);
    });
});