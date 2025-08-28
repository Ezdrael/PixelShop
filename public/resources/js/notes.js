document.addEventListener('DOMContentLoaded', () => {
    const config = {
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    const dom = {
        toggleBtn: document.getElementById('notes-toggle-btn'),
        widget: document.getElementById('notes-widget'),
        closeBtn: document.querySelector('.notes-close-btn'),
        notesList: document.getElementById('notes-list'),
        newNoteContent: document.getElementById('new-note-content'),
        addNoteBtn: document.getElementById('add-note-btn')
    };

    if (!dom.widget) return;

    // --- API Calls (без змін) ---
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
        // !! КЛЮЧОВА ЗМІНА: Оновлено HTML-структуру кнопок !!
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
                    <button class="btn-primary note-save-btn"><i class="fas fa-save"></i> Зберегти</button>
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
    dom.toggleBtn.addEventListener('click', () => {
        dom.widget.classList.toggle('open');
        if (dom.widget.classList.contains('open')) {
            loadNotes();
        }
    });
    
    dom.closeBtn.addEventListener('click', () => dom.widget.classList.remove('open'));

    if (dom.addNoteBtn) {
        dom.addNoteBtn.addEventListener('click', async () => {
            const content = dom.newNoteContent.value.trim();
            if (!content) return;
            const result = await api.createNote(content);
            if (result.success) {
                dom.notesList.prepend(renderNote(result.note));
                renumberNotes();
                dom.newNoteContent.value = '';
            } else {
                alert(result.message || 'Помилка');
            }
        });
    }
    
    dom.notesList.addEventListener('click', async (e) => {
        const target = e.target;
        const li = target.closest('li');
        if (!li) return;
        const noteId = li.dataset.noteId;

        if (target.closest('.note-delete-btn')) {
            if (confirm('Ви впевнені, що хочете видалити нотатку?')) {
                const result = await api.deleteNote(noteId);
                if (result.success) {
                    li.remove();
                    renumberNotes();
                } else {
                    alert(result.message || 'Помилка видалення');
                }
            }
        }

        if (target.closest('.note-edit-btn')) {
            li.querySelector('.note-content').style.display = 'none';
            li.querySelector('.note-actions').style.display = 'none';
            li.querySelector('.note-edit-form').style.display = 'flex';
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
});