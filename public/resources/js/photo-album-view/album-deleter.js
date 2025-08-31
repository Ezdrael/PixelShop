// public/resources/js/photo-album-view/album-deleter.js

/**
 * Ініціалізує всю логіку, пов'язану з видаленням альбомів.
 */
export function initAlbumDeleteHandler() {
    // --- 1. Знаходимо всі необхідні елементи на сторінці ---
    const simpleModal = document.getElementById('deleteModalOverlay');
    const complexModal = document.getElementById('deleteAlbumModalOverlay');
    const baseUrl = document.body.dataset.baseUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Перевірка, чи існують модальні вікна
    if (!simpleModal || !complexModal) {
        console.error("Модальні вікна для видалення не знайдено!");
        return;
    }

    // --- 2. Універсальна функція для відправки запиту на видалення ---
    const performDeleteRequest = async (albumId, payload) => {
        document.body.style.cursor = 'wait';
        try {
            const response = await fetch(`${baseUrl}/albums/delete/${albumId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (result.success) {
                sessionStorage.setItem('flashMessage', JSON.stringify({ type: 'success', text: 'Альбом успішно видалено.' }));
                window.location.href = `${baseUrl}/albums`;
            } else {
                alert(`Помилка: ${result.message || 'Не вдалося виконати дію.'}`);
            }
        } catch (error) {
            console.error('Помилка під час видалення альбому:', error);
            alert('Сталася непередбачувана помилка. Перевірте консоль (F12).');
        } finally {
            document.body.style.cursor = 'default';
        }
    };

    // --- 3. Функції для показу відповідних модальних вікон ---

    // Для порожніх альбомів
    const showSimpleDeleteModal = (albumId, albumName) => {
        const modalTitle = simpleModal.querySelector('#modalTitle');
        const modalBody = simpleModal.querySelector('#modalBody');
        const confirmBtn = simpleModal.querySelector('#modalConfirmBtn');

        modalTitle.textContent = 'Підтвердження видалення';
        modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити порожній альбом <strong>"${albumName}"</strong>?</p>`;
        
        // Використовуємо { once: true }, щоб обробник спрацював лише один раз
        confirmBtn.addEventListener('click', () => {
            performDeleteRequest(albumId, { action: 'delete_empty' });
        }, { once: true });

        simpleModal.style.display = 'flex';
    };

    // Для альбомів з вмістом
    const showComplexDeleteModal = async (albumId, albumName) => {
        const modalText = complexModal.querySelector('#deleteAlbumModalText');
        const targetSelect = complexModal.querySelector('select[name="target_album_id"]');
        const confirmBtn = complexModal.querySelector('#confirmAlbumDeleteBtn');
        
        modalText.innerHTML = `Альбом <strong>"${albumName}"</strong> не порожній. Що зробити з його вмістом?`;
        targetSelect.innerHTML = '<option>Завантаження...</option>';

        try {
            const response = await fetch(`${baseUrl}/albums/get-for-move?exclude=${albumId}`);
            const data = await response.json();
            if (data.success && data.albums.length > 0) {
                targetSelect.innerHTML = data.albums.map(album => `<option value="${album.id}">${album.name}</option>`).join('');
                targetSelect.disabled = false;
            } else {
                targetSelect.innerHTML = '<option value="">Немає куди переміщувати</option>';
                targetSelect.disabled = true;
                complexModal.querySelector('input[value="delete_content"]').checked = true;
            }
        } catch (error) {
            targetSelect.innerHTML = '<option value="">Помилка завантаження</option>';
            targetSelect.disabled = true;
        }

        confirmBtn.addEventListener('click', () => {
            const action = complexModal.querySelector('input[name="delete_action"]:checked').value;
            const targetAlbumId = targetSelect.value;

            if (action === 'move_content' && !targetAlbumId) {
                alert('Будь ласка, виберіть альбом для переміщення.');
                return;
            }
            performDeleteRequest(albumId, { action, target_album_id: targetAlbumId });
        }, { once: true });

        complexModal.style.display = 'flex';
    };

    // --- 4. Головний обробник подій, який все запускає ---
    document.body.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('.delete-album-btn');
        if (!deleteButton) return;

        const { albumId, albumName, isEmpty } = deleteButton.dataset;
        
        if (isEmpty === 'true') {
            showSimpleDeleteModal(albumId, albumName);
        } else {
            showComplexDeleteModal(albumId, albumName);
        }
    });

    // Налаштовуємо закриття для обох модальних вікон
    [simpleModal, complexModal].forEach(modal => {
        const closeModal = () => modal.style.display = 'none';
        modal.querySelectorAll('.modal-close, .cancel').forEach(btn => btn.addEventListener('click', closeModal));
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    });
}