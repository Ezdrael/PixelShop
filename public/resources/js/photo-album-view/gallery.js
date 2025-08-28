/**
 * Модуль для ініціалізації галереї LightGallery з динамічними кнопками.
 */

// Переконайтесь, що ви підключили плагіни lightGallery у вашому v_main_layout.php
// Наприклад: <script src=".../lg-zoom.min.js"></script> і т.д.

export function initLightGallery(config, deletePhotosCallback) {
    const { galleryContainer, baseUrl, csrfToken, showFlashMessage } = config;

    const lg = lightGallery(galleryContainer, {
        plugins: [lgZoom, lgThumbnail], // Додайте інші плагіни, якщо вони є: lgAutoplay, lgFullscreen
        licenseKey: 'your_license_key', // Вставте ваш ключ, якщо він є
        speed: 500,
        download: false,
    });

    galleryContainer.addEventListener('lgAfterOpen', (event) => {
        const { detail } = event;
        if (!detail || !detail.toolbar) return;

        const setCoverBtnHTML = `<button type="button" aria-label="Set as cover" class="lg-icon" id="lg-set-cover-btn"><i class="fas fa-star"></i></button>`;
        const deleteBtnHTML = `<button type="button" aria-label="Delete photo" class="lg-icon" id="lg-delete-photo-btn"><i class="fas fa-trash"></i></button>`;
        
        detail.toolbar.insertAdjacentHTML('beforeend', setCoverBtnHTML);
        detail.toolbar.insertAdjacentHTML('beforeend', deleteBtnHTML);

        document.getElementById('lg-set-cover-btn').addEventListener('click', async () => {
            const currentSlide = lg.galleryItems[detail.index];
            const photoId = currentSlide.dataset.photoId;
            const albumId = config.albumId;
            
            const response = await fetch(`${baseUrl}/albums/set-cover/${albumId}/${photoId}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const result = await response.json();
            if(result.success) {
                showFlashMessage('success', 'Обкладинку альбому оновлено.');
                lg.closeGallery();
                window.location.reload();
            } else {
                alert(result.message || 'Помилка');
            }
        });

        document.getElementById('lg-delete-photo-btn').addEventListener('click', () => {
             const currentSlide = lg.galleryItems[detail.index];
             const photoId = currentSlide.dataset.photoId;
             lg.closeGallery();
             deletePhotosCallback([photoId]);
        });
    });

    return lg; // Повертаємо екземпляр галереї
}