// public/resources/js/photo-album-view.js

import { initLightGallery } from './photo-album-view/gallery.js';
import { initPhotoSelection } from './photo-album-view/photo-selection.js';
import { initPhotoMove, showMovePhotoModal } from './photo-album-view/photo-move.js';
import { initAlbumDeleteHandler } from './photo-album-view/album-deleter.js';
import { initHotKeys } from './photo-album-view/hotkeys.js';

document.addEventListener('DOMContentLoaded', () => {
    initAlbumDeleteHandler();
    initHotKeys();

    const galleryContainer = document.getElementById('lightgallery-container');
    if (!galleryContainer) return;

    const config = {
        galleryContainer,
        baseUrl: document.body.dataset.baseUrl || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        showFlashMessage: (type, text) => sessionStorage.setItem('flashMessage', JSON.stringify({ type, text }))
    };

    const simpleModal = document.getElementById('deleteModalOverlay');
    const errorModal = document.getElementById('errorModalOverlay');

    const showErrorMessage = (message) => {
        if (!errorModal) return;
        const title = errorModal.querySelector('#errorModalTitle');
        const body = errorModal.querySelector('#errorModalBody');
        title.textContent = 'Помилка';
        body.innerHTML = `<p>${message}</p>`;
        errorModal.style.display = 'flex';
    };

    const deletePhotos = async (photoIds) => {
        if (!Array.isArray(photoIds) || photoIds.length === 0 || !simpleModal) return;
        
        const modalTitle = simpleModal.querySelector('#modalTitle');
        const modalBody = simpleModal.querySelector('#modalBody');
        const modalConfirmBtn = simpleModal.querySelector('#modalConfirmBtn');
        modalTitle.textContent = 'Підтвердження видалення';
        modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити ${photoIds.length === 1 ? 'це фото' : `${photoIds.length} фото`}?</p>`;
        simpleModal.style.display = 'flex';

        const newConfirmBtn = modalConfirmBtn.cloneNode(true);
        modalConfirmBtn.parentNode.replaceChild(newConfirmBtn, modalConfirmBtn);
        newConfirmBtn.addEventListener('click', async () => {
            simpleModal.style.display = 'none';
            let successCount = 0;
            for (const id of photoIds) {
                try {
                    const response = await fetch(`${config.baseUrl}/albums/delete-photo/${id}`, {
                        method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrfToken }
                    });
                    const result = await response.json();
                    if (result.success) successCount++;
                } catch (error) { console.error('Помилка видалення фото:', error); }
            }
            if (successCount > 0) {
                config.showFlashMessage('success', `Успішно видалено ${successCount} фото.`);
                window.location.reload();
            } else {
                showErrorMessage('Не вдалося видалити фото.');
            }
        }, { once: true });
    };

    // Ініціалізуємо всі модулі
    initLightGallery(config); // Більше не передаємо колбеки
    initPhotoSelection(config, deletePhotos);
    initPhotoMove(config, showErrorMessage);

    // ✅ ДОДАНО: Головний "слухач" подій від галереї
    galleryContainer.addEventListener('galleryAction', async (event) => {
        const { action, photoId, albumId } = event.detail;

        switch (action) {
            case 'setCover':
                try {
                    const response = await fetch(`${config.baseUrl}/albums/set-cover/${albumId}/${photoId}`, {
                        method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrfToken }
                    });
                    const result = await response.json();
                    if (result.success) {
                        config.showFlashMessage('success', 'Обкладинку оновлено.');
                        window.location.reload();
                    } else { showErrorMessage(result.message || 'Помилка.'); }
                } catch (e) { console.error(e); showErrorMessage('Сталася помилка.'); }
                break;

            case 'delete':
                deletePhotos([photoId]);
                break;
                
            case 'move':
                showMovePhotoModal([photoId], config, showErrorMessage);
                break;
        }
    });
});