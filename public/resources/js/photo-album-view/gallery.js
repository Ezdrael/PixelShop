// public/resources/js/photo-album-view/gallery.js

/**
 * Ініціалізує LightGallery з кастомними кнопками "Зробити обкладинкою" та "Видалити".
 */
export function initLightGallery(config, deletePhotos) {
    // Перевіряємо, чи бібліотека LightGallery завантажилась
    if (typeof lightGallery === 'undefined') {
        console.error('LightGallery library is not loaded.');
        return;
    }

    // Ініціалізуємо галерею
    const lg = lightGallery(config.galleryContainer, {
        selector: '.gallery-item',
        speed: 500,
        download: false, // Вимикаємо стандартну кнопку завантаження
        getCaptionFromTitleOrAlt: false
    });

    // === ГОЛОВНЕ ВИПРАВЛЕННЯ: Додаємо кнопки ПІСЛЯ ініціалізації галереї ===
    // Слухаємо подію 'lgAfterOpen', яка спрацьовує, коли слайдер відкрито
    config.galleryContainer.addEventListener('lgAfterOpen', (event) => {
        const lightGalleryInstance = event.detail.instance;
        const currentSlide = lightGalleryInstance.getSlideItem(lightGalleryInstance.index);
        
        // Знаходимо панель інструментів у відкритому слайдері
        const toolbar = lightGalleryInstance.outer.querySelector('.lg-toolbar');
        if (!toolbar) return;

        // --- Кнопка "Зробити обкладинкою" ---
        const setCoverBtn = document.createElement('button');
        setCoverBtn.className = 'lg-icon';
        setCoverBtn.id = 'lg-set-cover';
        setCoverBtn.setAttribute('aria-label', 'Зробити обкладинкою');
        setCoverBtn.innerHTML = '<i class="fas fa-bookmark"></i>';
        
        setCoverBtn.onclick = async () => {
            const photoId = currentSlide.dataset.photoId;
            const albumId = currentSlide.dataset.albumId;
            const response = await fetch(`${config.baseUrl}/albums/set-cover/${albumId}/${photoId}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken }
            });
            const result = await response.json();
            if (result.success) {
                config.showFlashMessage('success', 'Обкладинку альбому оновлено.');
                lightGalleryInstance.closeGallery();
            } else {
                alert(result.message || 'Не вдалося встановити обкладинку.');
            }
        };
        toolbar.appendChild(setCoverBtn);

        // --- Кнопка "Видалити" ---
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'lg-icon';
        deleteBtn.id = 'lg-delete-photo';
        deleteBtn.setAttribute('aria-label', 'Видалити фото');
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        
        deleteBtn.onclick = () => {
            const photoId = currentSlide.dataset.photoId;
            deletePhotos([photoId]); // Використовуємо функцію видалення з головного скрипта
            lightGalleryInstance.closeGallery();
        };
        toolbar.appendChild(deleteBtn);
    });
}