// public/resources/js/photo-album-view/gallery.js

/**
 * ✅ ФІНАЛЬНА ВЕРСІЯ, ЩО ВИКОРИСТОВУЄ ПОДІЇ
 */
export function initLightGallery(config) { // Більше не приймає колбеки
    const galleryContainer = config.galleryContainer;
    if (typeof lightGallery === 'undefined' || !galleryContainer) return;

    const lg = lightGallery(galleryContainer, {
        selector: '.gallery-item',
        speed: 500,
        download: false,
    });

    galleryContainer.addEventListener('lgAfterOpen', () => {
        const lightGalleryInstance = lg;
        const toolbar = document.querySelector('.lg-toolbar');

        if (!toolbar || toolbar.querySelector('#lg-custom-buttons')) return;

        const canEdit = galleryContainer.dataset.canEdit === 'true';
        const canDelete = galleryContainer.dataset.canDelete === 'true';

        const buttonContainer = document.createElement('div');
        buttonContainer.id = 'lg-custom-buttons';
        buttonContainer.style.display = 'flex';
        buttonContainer.style.gap = '10px';

        // Функція для відправки кастомної події
        const dispatchActionEvent = (action, detail) => {
            galleryContainer.dispatchEvent(new CustomEvent('galleryAction', { detail: { action, ...detail } }));
        };

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
            const currentItem = lightGalleryInstance.galleryItems[lightGalleryInstance.index];
            return {
                photoId: currentItem.dataset.photoId,
                albumId: currentItem.dataset.albumId,
            };
        };
        
        if (canEdit) {
            const setCoverBtn = createButton('fas fa-image', 'Встановити як обкладинку', () => {
                dispatchActionEvent('setCover', getCurrentSlideData());
            });

            const moveBtn = createButton('fas fa-arrows-alt', 'Перемістити фото', () => {
                lightGalleryInstance.closeGallery();
                dispatchActionEvent('move', getCurrentSlideData());
            });

            buttonContainer.appendChild(setCoverBtn);
            buttonContainer.appendChild(moveBtn);
        }

        if (canDelete) {
            const deleteBtn = createButton('fas fa-trash', 'Видалити фото', () => {
                lightGalleryInstance.closeGallery();
                dispatchActionEvent('delete', getCurrentSlideData());
            }, 'var(--danger-color)');
            
            buttonContainer.appendChild(deleteBtn);
        }
        
        if (buttonContainer.hasChildNodes()) {
            toolbar.appendChild(buttonContainer);
        }
    });
}