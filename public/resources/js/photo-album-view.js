/**
 * Головний скрипт для сторінки перегляду альбому.
 */
import { initPhotoSelection } from './photo-album-view/photo-selection.js';
import { initAlbumDeleteHandler } from './photo-album-view/album-deleter.js';
import { initLightGallery } from './photo-album-view/gallery.js';

document.addEventListener('DOMContentLoaded', () => {
    const galleryContainer = document.getElementById('lightgallery-container');
    if (!galleryContainer) return;

    // --- Створюємо єдиний об'єкт конфігурації ---
    const config = {
        galleryContainer: galleryContainer,
        albumId: galleryContainer.dataset.albumId,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        baseUrl: document.body.dataset.baseUrl || '',
        projectUrl: document.body.dataset.projectUrl || '',
        showFlashMessage: (type, text) => {
            sessionStorage.setItem('flashMessage', JSON.stringify({ type, text }));
        }
    };
    
    // --- Створюємо універсальну функцію видалення ---
    const deletePhotos = async (photoIds) => {
        if (!Array.isArray(photoIds) || photoIds.length === 0) return;
        if (!confirm(`Ви впевнені, що хочете видалити ${photoIds.length} фото?`)) return;

        try {
            let successCount = 0;
            for (const id of photoIds) {
                const response = await fetch(`${config.baseUrl}/albums/delete-photo/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': config.csrfToken }
                });
                const result = await response.json();
                if (result.success) {
                    successCount++;
                }
            }
            if (successCount > 0) {
                config.showFlashMessage('success', `Успішно видалено ${successCount} фото.`);
                window.location.reload();
            } else {
                alert('Не вдалося видалити фото.');
            }
        } catch (error) {
            console.error('Помилка видалення фото:', error);
            alert('Сталася критична помилка під час видалення.');
        }
    };

    // --- ✅ ВИПРАВЛЕНО: Ініціалізуємо модулі, передаючи їм config ---
    initPhotoSelection(config, deletePhotos);
    initAlbumDeleteHandler(config); // Цей модуль вже приймав config, все добре
    initLightGallery(config, deletePhotos);
});