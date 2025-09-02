// public/resources/js/photo-album-view/photo-selection.js

/**
 * Ініціалізує логіку вибору фотографій у галереї.
 * @param {object} config - Спільний об'єкт конфігурації.
 * @param {function} deletePhotosCallback - Функція для видалення фото.
 */
export function initPhotoSelection(config, deletePhotosCallback) {
    const { galleryContainer } = config;

    const toggleBtn = document.getElementById('toggle-selection-mode');
    const actionsBar = document.getElementById('dynamic-selection-actions');
    const counterBadge = document.getElementById('selection-counter-badge');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    const selectAllBtn = document.getElementById('select-all-btn');

    if (!toggleBtn) return;

    let selectionMode = false;

    const updateSelectionCounter = () => {
        const selectedCount = galleryContainer.querySelectorAll('.photo-selection-checkbox:checked').length;
        if (counterBadge) counterBadge.textContent = selectedCount;
        if (deleteSelectedBtn) deleteSelectedBtn.disabled = selectedCount === 0;
    };

    toggleBtn.addEventListener('click', () => {
        selectionMode = !selectionMode;
        galleryContainer.classList.toggle('selection-mode-active', selectionMode);
        if (actionsBar) actionsBar.style.display = selectionMode ? 'flex' : 'none';
        if (selectionMode) {
            toggleBtn.innerHTML = '<i class="fas fa-times"></i> Скасувати';
            toggleBtn.title = 'Вийти з режиму вибору (Esc)';
        } else {
            toggleBtn.innerHTML = '<i class="fas fa-check-square"></i> Вибрати';
            toggleBtn.title = 'Увімкнути режим вибору (Alt+S)';
        }
        if (!selectionMode) {
            galleryContainer.querySelectorAll('.photo-selection-checkbox:checked').forEach(cb => {
                cb.checked = false;
                cb.closest('.gallery-item-wrapper').classList.remove('is-selected');
            });
        }
        updateSelectionCounter();
    });

    galleryContainer.addEventListener('change', (e) => {
        if (e.target.classList.contains('photo-selection-checkbox')) {
            e.target.closest('.gallery-item-wrapper')?.classList.toggle('is-selected', e.target.checked);
            updateSelectionCounter();
        }
    });

    galleryContainer.addEventListener('click', (event) => {
        if (!selectionMode) return;
        const wrapper = event.target.closest('.gallery-item-wrapper');
        if (!wrapper) return;
        event.preventDefault();
        event.stopPropagation();
        const checkbox = wrapper.querySelector('.photo-selection-checkbox');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }, true);

    deleteSelectedBtn?.addEventListener('click', () => {
        const selectedIds = Array.from(galleryContainer.querySelectorAll('.photo-selection-checkbox:checked')).map(cb => cb.dataset.photoId);
        if (selectedIds.length > 0) deletePhotosCallback(selectedIds);
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
}