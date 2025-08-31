// public/resources/js/photo-album-view.js

document.addEventListener('DOMContentLoaded', () => {
    const galleryContainer = document.getElementById('lightgallery-container');
    if (!galleryContainer) return;

    // --- 1. Конфігурація ---
    const config = {
        galleryContainer,
        albumId: galleryContainer.dataset.albumId,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        baseUrl: document.body.dataset.baseUrl || '',
        showFlashMessage: (type, text) => {
            sessionStorage.setItem('flashMessage', JSON.stringify({ type, text }));
        }
    };

    // --- 2. Елементи керування вибором ---
    const toggleBtn = document.getElementById('toggle-selection-mode');
    const actionsBar = document.getElementById('dynamic-selection-actions');
    const counterBadge = document.getElementById('selection-counter-badge');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    const selectAllBtn = document.getElementById('select-all-btn');

    let selectionMode = false;

    // --- 3. Функція видалення фото ---
    const deletePhotos = async (photoIds) => {
        if (!Array.isArray(photoIds) || photoIds.length === 0) return;
        if (!confirm(`Ви впевнені, що хочете видалити ${photoIds.length} фото?`)) return;

        let successCount = 0;
        for (const id of photoIds) {
            try {
                const response = await fetch(`${config.baseUrl}/albums/delete-photo/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': config.csrfToken }
                });
                const result = await response.json();
                if (result.success) successCount++;
            } catch (error) {
                console.error('Помилка видалення фото:', error);
            }
        }

        if (successCount > 0) {
            config.showFlashMessage('success', `Успішно видалено ${successCount} фото.`);
            window.location.reload();
        } else {
            alert('Не вдалося видалити фото.');
        }
    };

    // --- 4. Ініціалізація LightGallery ---
    const lg = lightGallery(galleryContainer, {
        selector: '.gallery-item',
        speed: 500,
        download: false,
    });

    // --- 5. Логіка режиму вибору ---
    const updateSelectionCounter = () => {
        const selectedCount = galleryContainer.querySelectorAll('.photo-selection-checkbox:checked').length;
        if (counterBadge) counterBadge.textContent = selectedCount;
        if (deleteSelectedBtn) deleteSelectedBtn.disabled = selectedCount === 0;
    };

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            selectionMode = !selectionMode;
            galleryContainer.classList.toggle('selection-mode-active', selectionMode);
            if(actionsBar) actionsBar.style.display = selectionMode ? 'flex' : 'none';
            toggleBtn.innerHTML = selectionMode ? '<i class="fas fa-times"></i> Скасувати' : '<i class="fas fa-check-square"></i> Вибрати';
            
            if (!selectionMode) {
                galleryContainer.querySelectorAll('.photo-selection-checkbox:checked').forEach(cb => {
                    cb.checked = false;
                    cb.closest('.gallery-item-wrapper').classList.remove('is-selected');
                });
                updateSelectionCounter();
            }
        });
    }

    galleryContainer.addEventListener('change', (e) => {
        if (e.target.classList.contains('photo-selection-checkbox')) {
            const wrapper = e.target.closest('.gallery-item-wrapper');
            wrapper?.classList.toggle('is-selected', e.target.checked);
            updateSelectionCounter();
        }
    });

    deleteSelectedBtn?.addEventListener('click', () => {
        const selectedIds = Array.from(galleryContainer.querySelectorAll('.photo-selection-checkbox:checked'))
                                 .map(cb => cb.dataset.photoId);
        if (selectedIds.length > 0) {
            deletePhotos(selectedIds);
        }
    });

    selectAllBtn?.addEventListener('click', () => {
        const checkboxes = galleryContainer.querySelectorAll('.photo-selection-checkbox');
        const shouldSelectAll = galleryContainer.querySelectorAll('.photo-selection-checkbox:checked').length < checkboxes.length;
        checkboxes.forEach(cb => {
            cb.checked = shouldSelectAll;
            cb.closest('.gallery-item-wrapper')?.classList.toggle('is-selected', shouldSelectAll);
        });
        updateSelectionCounter();
    });

    // --- 6. Перехоплення кліків по фото в режимі вибору ---
    galleryContainer.addEventListener('click', (event) => {
        if (!selectionMode) {
            return;
        }

        const wrapper = event.target.closest('.gallery-item-wrapper');
        if (!wrapper) {
            return;
        }
        
        // Запобігаємо відкриттю слайдера
        event.preventDefault();
        event.stopPropagation();

        // Імітуємо клік по чекбоксу всередині
        const checkbox = wrapper.querySelector('.photo-selection-checkbox');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }, true); // `true` гарантує, що цей обробник спрацює раніше за LightGallery
});