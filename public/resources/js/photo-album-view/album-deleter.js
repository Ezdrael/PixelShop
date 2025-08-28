/**
 * Універсальний обробник для видалення фотоальбомів.
 */

const simpleModal = document.getElementById('deleteModalOverlay');
const complexModal = document.getElementById('deleteAlbumModalOverlay');
const baseUrl = document.body.dataset.baseUrl || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function setupModalCloseHandlers(modalElement) {
    const closeModal = () => modalElement.style.display = 'none';
    modalElement.querySelectorAll('.modal-close, .cancel').forEach(btn => btn.onclick = closeModal);
    modalElement.addEventListener('click', e => { if (e.target === modalElement) closeModal(); });
}

async function performDeleteRequest(albumId, payload) {
    document.body.style.cursor = 'wait';
    try {
        // ✅ ВИПРАВЛЕНО: шлях змінено на 'albums'
        const response = await fetch(`${baseUrl}/albums/delete/${albumId}`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        if (result.success) {
            // ✅ ВИПРАВЛЕНО: шлях для переадресації змінено на 'albums'
            window.location.href = `${baseUrl}/albums`;
        } else {
            alert(`Помилка: ${result.message || 'Не вдалося виконати дію.'}`);
        }
    } catch (error) {
        console.error('Помилка під час видалення альбому:', error);
        alert('Сталася непередбачувана помилка. Перевірте консоль (F12).');
    } finally {
        document.body.style.cursor = 'default';
        if (simpleModal) simpleModal.style.display = 'none';
        if (complexModal) complexModal.style.display = 'none';
    }
}

function showSimpleDeleteModal(albumId, albumName) {
    if (!simpleModal) return;
    simpleModal.querySelector('#modalTitle').textContent = 'Підтвердження видалення';
    simpleModal.querySelector('#modalBody').innerHTML = `<p>Ви впевнені, що хочете видалити порожній альбом <strong>"${albumName}"</strong>?</p>`;
    const confirmBtn = simpleModal.querySelector('#modalConfirmBtn');
    confirmBtn.onclick = () => performDeleteRequest(albumId, { action: 'delete_content' });
    setupModalCloseHandlers(simpleModal);
    simpleModal.style.display = 'flex';
}

async function showComplexDeleteModal(albumId, albumName) {
    if (!complexModal) return;
    complexModal.querySelector('#deleteAlbumModalText').innerHTML = `Альбом <strong>"${albumName}"</strong> не порожній. Що зробити з його вмістом?`;
    const targetSelect = complexModal.querySelector('select[name="target_album_id"]');
    targetSelect.innerHTML = '<option>Завантаження...</option>';
    const moveOptionLabel = complexModal.querySelector('input[value="move_content"]').parentElement;
    if (moveOptionLabel) moveOptionLabel.style.display = 'flex';

    try {
        // ✅ ВИПРАВЛЕНО: шлях змінено на 'albums'
        const response = await fetch(`${baseUrl}/albums/get-for-move?exclude=${albumId}`);
        const data = await response.json();
        if (data.success && data.albums.length > 0) {
            targetSelect.innerHTML = data.albums.map(album => `<option value="${album.id}">${album.name}</option>`).join('');
        } else {
            targetSelect.innerHTML = '<option value="">Немає куди переміщувати</option>';
            if (moveOptionLabel) moveOptionLabel.style.display = 'none';
            complexModal.querySelector('input[value="delete_content"]').checked = true;
        }
    } catch (error) {
        targetSelect.innerHTML = '<option value="">Помилка завантаження</option>';
    }

    const confirmBtn = complexModal.querySelector('#confirmAlbumDeleteBtn');
    confirmBtn.onclick = () => {
        const action = complexModal.querySelector('input[name="delete_action"]:checked').value;
        const targetAlbumId = targetSelect.value;
        if (action === 'move_content' && !targetAlbumId) {
            alert('Будь ласка, виберіть альбом для переміщення.');
            return;
        }
        performDeleteRequest(albumId, { action, target_album_id: targetAlbumId });
    };
    setupModalCloseHandlers(complexModal);
    complexModal.style.display = 'flex';
}

export function initAlbumDeleteHandler() {
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