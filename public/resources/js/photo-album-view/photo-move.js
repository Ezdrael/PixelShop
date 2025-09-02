// public/resources/js/photo-album-view/photo-move.js

/**
 * ✅ ОНОВЛЕНО: Показує модальне вікно для переміщення вибраних фото.
 *
 * @param {Array<string>} photoIds - Масив ID фотографій для переміщення.
 * @param {object} config - Спільний об'єкт конфігурації.
 * @param {function} showErrorMessage - Функція для відображення помилок.
 */
export const showMovePhotoModal = async (photoIds, config, showErrorMessage) => {
    const confirmationModal = document.getElementById('deleteModalOverlay');
    if (!confirmationModal) return;

    if (photoIds.length === 0) {
        showErrorMessage('Спочатку виберіть фото для переміщення.');
        return;
    }

    const buildAlbumOptions = (albums, currentAlbumId, level = 0) => {
        let html = '';
        const indent = '— '.repeat(level);
        albums.forEach(album => {
            if (album.id != currentAlbumId) {
                html += `<option value="${album.id}">${indent}${album.name}</option>`;
            }
            if (album.children && album.children.length > 0) {
                html += buildAlbumOptions(album.children, currentAlbumId, level + 1);
            }
        });
        return html;
    };

    try {
        const currentAlbumId = config.galleryContainer.dataset.albumId;
        const response = await fetch(`${config.baseUrl}/albums/get-for-move?exclude=${currentAlbumId}`);
        const data = await response.json();

        if (!data.success || !data.albums || data.albums.length === 0) {
            showErrorMessage('Немає доступних альбомів для переміщення.');
            return;
        }

        const albumOptionsHtml = buildAlbumOptions(data.albums, currentAlbumId);
        const modalTitle = confirmationModal.querySelector('#modalTitle');
        const modalBody = confirmationModal.querySelector('#modalBody');
        const modalConfirmBtn = confirmationModal.querySelector('#modalConfirmBtn');

        modalTitle.textContent = `Переміщення ${photoIds.length} фото`;
        modalBody.innerHTML = `
            <p>Будь ласка, оберіть альбом для переміщення:</p>
            <select id="target-album-select" class="form-control" style="width: 100%; margin-top: 1rem;">
                <option value="0">-- Кореневий альбом --</option>
                ${albumOptionsHtml}
            </select>
        `;
        modalConfirmBtn.textContent = 'Так, перемістити';

        const newConfirmBtn = modalConfirmBtn.cloneNode(true);
        modalConfirmBtn.parentNode.replaceChild(newConfirmBtn, modalConfirmBtn);

        newConfirmBtn.addEventListener('click', async () => {
            const targetAlbumId = document.getElementById('target-album-select').value;
            const moveResponse = await fetch(`${config.baseUrl}/photos/move`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ photo_ids: photoIds, target_album_id: targetAlbumId })
            });
            const result = await moveResponse.json();
            if (result.success) {
                config.showFlashMessage('success', `Успішно переміщено ${photoIds.length} фото.`);
                window.location.reload();
            } else { showErrorMessage(result.message || 'Помилка.'); }
        }, { once: true });
        confirmationModal.style.display = 'flex';
    } catch (error) {
        console.error(error);
        showErrorMessage('Не вдалося завантажити список альбомів.');
    }
};

/**
 * Ініціалізує кнопку "Перемістити вибрані" в режимі вибору.
 */
export function initPhotoMove(config, showErrorMessage) {
    const moveSelectedBtn = document.getElementById('move-selected-btn');
    if (moveSelectedBtn) {
        moveSelectedBtn.addEventListener('click', () => {
            const selectedIds = Array.from(config.galleryContainer.querySelectorAll('.photo-selection-checkbox:checked')).map(cb => cb.dataset.photoId);
            showMovePhotoModal(selectedIds, config, showErrorMessage);
        });
    }
}