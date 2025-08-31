document.addEventListener('DOMContentLoaded', () => {
    const config = {
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    const dom = {
        widget: document.getElementById('notes-widget'),
        notesList: document.getElementById('notes-list'),
        newNoteContent: document.getElementById('new-note-content'),
        addNoteBtn: document.getElementById('add-note-btn'),
        deleteModal: document.getElementById('deleteModalOverlay'),
        modalTitle: document.getElementById('modalTitle'),
        modalBody: document.getElementById('modalBody'),
        modalConfirmBtn: document.getElementById('modalConfirmBtn')
    };

    if (!dom.widget) return;

    // --- API Calls ---
    const api = {
        async getNotes() {
            const res = await fetch(`${config.baseUrl}/notes/get`);
            return res.json();
        },
        async createNote(content) {
            const res = await fetch(`${config.baseUrl}/notes/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ content })
            });
            return res.json();
        },
        async updateNote(id, content) {
            const res = await fetch(`${config.baseUrl}/notes/update/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ content })
            });
            return res.json();
        },
        async deleteNote(id) {
            const res = await fetch(`${config.baseUrl}/notes/delete/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken }
            });
            return res.json();
        }
    };

    // --- UI Logic ---
    const renderNote = (note) => {
        const li = document.createElement('li');
        li.dataset.noteId = note.id;
        li.innerHTML = `
            <div class="note-content">${note.content.replace(/\n/g, '<br>')}</div>
            <div class="note-actions">
                <button class="action-btn note-edit-btn" title="Редагувати"><i class="fas fa-pencil-alt"></i></button>
                <button class="action-btn note-delete-btn" title="Видалити"><i class="fas fa-trash"></i></button>
            </div>
            <div class="note-edit-form" style="display:none;">
                <textarea class="form-control">${note.content}</textarea>
                <div class="note-edit-actions">
                    <button class="btn-secondary note-cancel-btn"><i class="fa-solid fa-xmark"></i>Скасувати</button>
                    <button class="btn-primary note-save-btn" title="Ctrl+Enter"><i class="fas fa-save"></i> Зберегти</button>
                </div>
            </div>
        `;
        return li;
    };

    const renumberNotes = () => {
        const items = dom.notesList.querySelectorAll('li');
        items.forEach((item, index) => {
            item.style.setProperty('--note-number', `'${index + 1}.'`);
        });
    };

    const loadNotes = async () => {
        const result = await api.getNotes();
        dom.notesList.innerHTML = '<li>Завантаження...</li>';
        if (result.success && result.notes) {
            dom.notesList.innerHTML = '';
            result.notes.forEach(note => dom.notesList.appendChild(renderNote(note)));
            renumberNotes();
        } else {
            dom.notesList.innerHTML = '<li>Не вдалося завантажити нотатки.</li>';
        }
    };
    
    // --- Event Handlers ---
    
    // Завантажуємо нотатки, коли віджет стає видимим (сигнал від ui-handlers.js)
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            if (mutation.attributeName === 'class' && dom.widget.classList.contains('open')) {
                loadNotes();
                setTimeout(() => dom.newNoteContent?.focus(), 100);
            }
        }
    });
    observer.observe(dom.widget, { attributes: true });

    // Обробники для додавання, видалення та редагування нотаток
    if (dom.addNoteBtn) {
        const addNewNote = async () => {
            const content = dom.newNoteContent.value.trim();
            if (!content) return;
            const result = await api.createNote(content);
            if (result.success) {
                dom.notesList.prepend(renderNote(result.note));
                renumberNotes();
                dom.newNoteContent.value = '';
            } else {
                alert(result.message || 'Помилка створення нотатки');
            }
        };
        dom.addNoteBtn.addEventListener('click', addNewNote);
        dom.newNoteContent.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                addNewNote();
            }
        });
    }

    dom.notesList.addEventListener('click', async (e) => {
        const target = e.target;
        const li = target.closest('li');
        if (!li) return;
        const noteId = li.dataset.noteId;
        if (target.closest('.note-delete-btn')) {
            const noteContent = li.querySelector('.note-content').textContent;
            dom.modalTitle.textContent = 'Підтвердження видалення';
            dom.modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити нотатку?</p><blockquote style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: #f8f9fa; border-left: 3px solid #e2e8f0; font-style: italic;">${noteContent}</blockquote>`;
            dom.deleteModal.style.display = 'flex';
            dom.modalConfirmBtn.addEventListener('click', async function handleDelete() {
                dom.deleteModal.style.display = 'none';
                const result = await api.deleteNote(noteId);
                if (result.success) {
                    li.remove();
                    renumberNotes();
                } else {
                    alert(result.message || 'Помилка видалення');
                }
            }, { once: true });
        }
        if (e.target.closest('.note-edit-btn')) {
            li.querySelector('.note-content').style.display = 'none';
            li.querySelector('.note-actions').style.display = 'none';
            const editForm = li.querySelector('.note-edit-form');
            editForm.style.display = 'flex';
            const textarea = editForm.querySelector('textarea');
            if (textarea) {
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            }
        }
        if (target.closest('.note-cancel-btn')) {
            li.querySelector('.note-content').style.display = 'block';
            li.querySelector('.note-actions').style.display = 'flex';
            li.querySelector('.note-edit-form').style.display = 'none';
        }
        if (target.closest('.note-save-btn')) {
            const textarea = li.querySelector('.note-edit-form textarea');
            const newContent = textarea.value.trim();
            if (!newContent) return;
            const result = await api.updateNote(noteId, newContent);
            if (result.success) {
                li.querySelector('.note-content').innerHTML = result.note.content.replace(/\n/g, '<br>');
                li.querySelector('.note-content').style.display = 'block';
                li.querySelector('.note-actions').style.display = 'flex';
                li.querySelector('.note-edit-form').style.display = 'none';
            } else {
                alert(result.message || 'Помилка збереження');
            }
        }
    });

    dom.notesList.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.ctrlKey) {
            if (e.target.tagName === 'TEXTAREA' && e.target.closest('.note-edit-form')) {
                e.preventDefault();
                const noteItem = e.target.closest('li');
                noteItem.querySelector('.note-save-btn')?.click();
            }
        }
    });
});