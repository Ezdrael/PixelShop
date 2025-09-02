// public/resources/js/photo-album-view/album-deleter.js

/**
 * Ініціалізує всю логіку, пов'язану з видаленням альбомів.
 */
export function initAlbumDeleteHandler() {
    const simpleModal = document.getElementById('deleteModalOverlay');
    const complexModal = document.getElementById('deleteAlbumModalOverlay');
    const baseUrl = document.body.dataset.baseUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!simpleModal || !complexModal) return;

    /**
     * === ОНОВЛЕНА ФУНКЦІЯ З РОЗШИРЕНИМ ДЕБАГОМ ===
     * Універсальна функція для відправки запиту на видалення.
     */
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

            // Спочатку отримуємо відповідь як текст, щоб проаналізувати її
            const responseText = await response.text();

            // Перевіряємо, чи відповідь успішна (статус 2xx)
            if (!response.ok) {
                // Якщо ні, виводимо весь HTML помилки в консоль
                console.error('Помилка сервера (HTTP статус ' + response.status + '). Повна відповідь:', responseText);
                throw new Error(`Сервер повернув помилку: ${response.status}`);
            }

            // Якщо відповідь успішна, намагаємося розпарсити її як JSON
            try {
                const result = JSON.parse(responseText);
                if (result.success) {
                    sessionStorage.setItem('flashMessage', JSON.stringify({ type: 'success', text: 'Альбом успішно видалено.' }));
                    window.location.href = `${baseUrl}/albums`;
                } else {
                    alert(`Помилка від сервера: ${result.message || 'Не вдалося виконати дію.'}`);
                }
            } catch (jsonError) {
                // Цей блок спрацює, якщо сервер повернув успішний статус, але не JSON (наприклад, HTML-сторінку)
                console.error('Помилка розбору JSON. Оригінальна відповідь від сервера:', responseText);
                throw new Error('Сервер повернув некоректну відповідь (не JSON).');
            }

        } catch (error) {
            console.error('Помилка під час видалення альбому:', error);
            alert('Сталася непередбачувана помилка. Подробиці виведено в консоль розробника (F12).');
        } finally {
            document.body.style.cursor = 'default';
        }
    };

    // --- 3. Функції для показу відповідних модальних вікон ---

    const showSimpleDeleteModal = (albumId, albumName) => {
        const modalTitle = simpleModal.querySelector('#modalTitle');
        const modalBody = simpleModal.querySelector('#modalBody');
        const confirmBtn = simpleModal.querySelector('#modalConfirmBtn');
        
        modalTitle.textContent = 'Підтвердження видалення';
        modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити порожній альбом <strong>"${albumName}"</strong>?</p>`;
        
        // Створюємо новий обробник, щоб уникнути старих
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', () => {
            performDeleteRequest(albumId, { action: 'delete_empty' });
        });

        simpleModal.style.display = 'flex';
    };

    const showComplexDeleteModal = async (albumId, albumName) => {
        const modalText = complexModal.querySelector('#deleteAlbumModalText');
        const targetSelect = complexModal.querySelector('select[name="target_album_id"]');
        const confirmBtn = complexModal.querySelector('#confirmAlbumDeleteBtn');
        
        modalText.innerHTML = `Альбом <strong>"${albumName}"</strong> не порожній. Що зробити з його вмістом?`;
        targetSelect.innerHTML = '<option>Завантаження списку альбомів...</option>';

        // --- ДОДАНО: Рекурсивна функція для побудови дерева опцій ---
        const buildAlbumOptions = (albums, level = 0) => {
            let html = '';
            const indent = '— '.repeat(level); // Створюємо відступ для ієрархії

            albums.forEach(album => {
                html += `<option value="${album.id}">${indent}${album.name}</option>`;
                if (album.children && album.children.length > 0) {
                    html += buildAlbumOptions(album.children, level + 1);
                }
            });
            return html;
        };
        // --- Кінець нової функції ---

        try {
            const response = await fetch(`${baseUrl}/albums/get-for-move?exclude=${albumId}`);
            const data = await response.json();

            if (data.success && data.albums && data.albums.length > 0) {
                // Викликаємо нашу нову функцію для побудови дерева
                targetSelect.innerHTML = buildAlbumOptions(data.albums);
                complexModal.querySelector('input[value="move_content"]').disabled = false;
                targetSelect.disabled = false;
            } else {
                targetSelect.innerHTML = '<option value="">Немає доступних альбомів для переміщення</option>';
                complexModal.querySelector('input[value="move_content"]').disabled = true;
                targetSelect.disabled = true;
                complexModal.querySelector('input[value="delete_content"]').checked = true;
            }
        } catch (error) {
            console.error("Помилка завантаження списку альбомів:", error);
            targetSelect.innerHTML = '<option value="">Помилка завантаження</option>';
            targetSelect.disabled = true;
        }
        
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', () => {
            const action = complexModal.querySelector('input[name="delete_action"]:checked').value;
            const targetAlbumId = targetSelect.value;

            if (action === 'move_content' && !targetAlbumId) {
                alert('Будь ласка, виберіть альбом для переміщення.');
                return;
            }
            performDeleteRequest(albumId, { action, target_album_id: targetAlbumId });
        });

        complexModal.style.display = 'flex';
    };

    // --- 4. Головний обробник подій ---
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
}