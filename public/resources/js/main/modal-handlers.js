// public/resources/js/main/modal-handlers.js

export function initModalHandlers() {
    const simpleDeleteModal = document.getElementById('deleteModalOverlay');
    if (!simpleDeleteModal) return;

    const baseUrl = document.body.dataset.baseUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const confirmBtn = simpleDeleteModal.querySelector('#modalConfirmBtn');

    document.body.addEventListener('click', (event) => {
        const button = event.target.closest('.delete-btn, .delete-writeoff-btn, .delete-currency-btn');

        // === ОСНОВНЕ ВИПРАВЛЕННЯ ===
        // Ігноруємо будь-які кнопки, що знаходяться всередині віджетів,
        // оскільки вони мають власні, унікальні обробники.
        if (!button || button.closest('.notes-widget, .clipboard-widget, .messages-widget')) {
            return;
        }
        
        const entity = button.dataset.entity;
        const id = button.dataset.id || button.dataset.userId || button.dataset.ids;
        const name = button.dataset.userName || button.dataset.name || button.dataset.id || button.dataset.ids;

        if (!id) {
             console.warn('Кнопка видалення не має атрибута data-id, data-user-id або data-ids.');
             return;
        }
        
        const modalTitle = simpleDeleteModal.querySelector('#modalTitle');
        const modalBody = simpleDeleteModal.querySelector('#modalBody');
        
        modalTitle.textContent = 'Підтвердження видалення';
        modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити <strong>"${name}"</strong>?</p>`;
        
        // Клонуємо кнопку, щоб очистити всі попередні обробники подій
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', async function handleDelete() {
            let deleteUrl;
            if (button.classList.contains('delete-writeoff-btn')) {
                deleteUrl = `${baseUrl}/writeoffs/delete/${id}`;
            } else if (entity) {
                 deleteUrl = `${baseUrl}/${entity}/delete/${id}`;
            } else {
                console.error('Не вдалося визначити URL для видалення.');
                return;
            }

            try {
                const response = await fetch(deleteUrl, { 
                    method: 'POST', 
                    headers: { 'X-CSRF-TOKEN': csrfToken } 
                });
                
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Сталася помилка під час видалення.');
                }
            } catch (error) {
                console.error("Помилка видалення:", error);
                alert('Не вдалося виконати запит.');
            }
        });
        
        simpleDeleteModal.style.display = 'flex';
    });
}