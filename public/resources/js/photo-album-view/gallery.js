// public/resources/js/photo-album-view/gallery.js

/**
 * ✅ ФІНАЛЬНА ВЕРСІЯ З ПЕРЕВІРКОЮ ПРАВ ДОСТУПУ
 */
export function initLightGallery(config, deletePhotosCallback, movePhotoCallback) {
    const galleryContainer = config.galleryContainer;
    if (typeof lightGallery === 'undefined' || !galleryContainer) {
        console.error('LightGallery не ініціалізовано або контейнер не знайдено.');
        return;
    }

    const lg = lightGallery(galleryContainer, {
        selector: '.gallery-item',
        speed: 500,
        download: false,
    });

    galleryContainer.addEventListener('lgAfterOpen', (event) => {
        const lightGalleryInstance = event.detail.instance;
        const toolbar = lightGalleryInstance.outer.querySelector('.lg-toolbar');

        if (!toolbar || toolbar.querySelector('#lg-custom-buttons')) return;

        // ✅ Отримуємо права доступу з data-атрибутів
        const canEdit = galleryContainer.dataset.canEdit === 'true';
        const canDelete = galleryContainer.dataset.canDelete === 'true';

        const buttonContainer = document.createElement('div');
        buttonContainer.id = 'lg-custom-buttons';
        buttonContainer.style.display = 'flex';
        buttonContainer.style.gap = '10px';

        const createButton = (iconClass, title, onClick, color = 'white') => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'lg-icon';
            btn.title = title;
            btn.setAttribute('aria-label', title);
            btn.innerHTML = `<i class="${iconClass}" style="font-size: 18px; color: ${color};"></i>`;
            btn.onclick = onClick;
            return btn;
        };

        const getCurrentSlideData = () => {
            const slide = lightGalleryInstance.getSlideItem(lightGalleryInstance.index);
            return {
                photoId: slide.dataset.photoId,
                albumId: slide.dataset.albumId,
            };
        };
        
        // ✅ Створюємо кнопки "Встановити обкладинку" та "Перемістити" ТІЛЬКИ ЯКЩО є права на редагування
        if (canEdit) {
            const setCoverBtn = createButton('fas fa-image', 'Встановити як обкладинку', async () => {
                const { photoId, albumId } = getCurrentSlideData();
                try {
                    const response = await fetch(`${config.baseUrl}/albums/set-cover/${albumId}/${photoId}`, {
                        method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrfToken }
                    });
                    const result = await response.json();
                    if (result.success) {
                        config.showFlashMessage('success', 'Обкладинку оновлено.');
                        window.location.reload();
                    } else { alert(result.message || 'Помилка.'); }
                } catch (e) { console.error(e); alert('Сталася помилка.'); }
            });

            const moveBtn = createButton('fas fa-arrows-alt', 'Перемістити фото', () => {
                const { photoId } = getCurrentSlideData();
                lightGalleryInstance.closeGallery();
                setTimeout(() => movePhotoCallback([photoId]), 150);
            });

            buttonContainer.appendChild(setCoverBtn);
            buttonContainer.appendChild(moveBtn);
        }

        // ✅ Створюємо кнопку "Видалити" ТІЛЬКИ ЯКЩО є права на видалення
        if (canDelete) {
            const deleteBtn = createButton('fas fa-trash', 'Видалити фото', () => {
                const { photoId } = getCurrentSlideData();
                lightGalleryInstance.closeGallery();
                setTimeout(() => deletePhotosCallback([photoId]), 150);
            }, 'var(--danger-color)');
            
            buttonContainer.appendChild(deleteBtn);
        }
        
        // Додаємо контейнер з кнопками на панель інструментів, лише якщо він не порожній
        if (buttonContainer.hasChildNodes()) {
            toolbar.appendChild(buttonContainer);
        }
    });
}