/**
 * Модуль для керування режимом вибору фотографій.
 */
export function initPhotoSelection(config, deletePhotosCallback) {
    const { galleryContainer } = config;

    const toggleBtn = document.getElementById('toggle-selection-mode');
    const actionsBar = document.getElementById('dynamic-selection-actions');
    const counterBadge = document.getElementById('selection-counter-badge');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    const moveSelectedBtn = document.getElementById('move-selected-btn');
    const selectAllBtn = document.getElementById('select-all-btn');

    if (!toggleBtn || !actionsBar || !galleryContainer) return;

    let selectionMode = false;
    let selectedPhotos = new Set();

    const updateUI = () => {
        // ✅ ВИПРАВЛЕНО: Використовуємо правильний клас 'selection-mode-active'
        galleryContainer.classList.toggle('selection-mode-active', selectionMode);
        
        actionsBar.style.display = selectionMode ? 'flex' : 'none';
        toggleBtn.innerHTML = selectionMode ? '<i class="fas fa-times"></i> Скасувати' : '<i class="fas fa-check-square"></i> Вибрати';
        
        const selectionCount = selectedPhotos.size;
        if (counterBadge) {
            counterBadge.textContent = selectionCount;
        }

        const hasSelection = selectionCount > 0;
        if (deleteSelectedBtn) {
            deleteSelectedBtn.disabled = !hasSelection;
        }
        if (moveSelectedBtn) {
            moveSelectedBtn.disabled = !hasSelection;
        }

        if (selectAllBtn) {
            const allCheckboxes = galleryContainer.querySelectorAll('.photo-selection-checkbox');
            const allSelected = allCheckboxes.length > 0 && selectionCount === allCheckboxes.length;
            
            selectAllBtn.innerHTML = allSelected ? '<i class="far fa-square"></i>' : '<i class="fas fa-check-double"></i>';
            selectAllBtn.title = allSelected ? 'Зняти виділення з усіх' : 'Виділити всі';
        }
    };

    toggleBtn.addEventListener('click', () => {
        selectionMode = !selectionMode;
        if (!selectionMode) {
            selectedPhotos.clear();
            galleryContainer.querySelectorAll('.photo-selection-checkbox').forEach(cb => cb.checked = false);
        }
        updateUI();
    });

    galleryContainer.addEventListener('click', (event) => {
        if (!selectionMode) return;
        if (event.target.closest('.photo-selection-label')) return;
        
        const wrapper = event.target.closest('.gallery-item-wrapper');
        if (!wrapper) return;

        event.preventDefault();
        const checkbox = wrapper.querySelector('.photo-selection-checkbox');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    galleryContainer.addEventListener('change', (event) => {
        const checkbox = event.target;
        if (checkbox.classList.contains('photo-selection-checkbox')) {
            const photoId = checkbox.dataset.photoId;
            if (checkbox.checked) {
                selectedPhotos.add(photoId);
            } else {
                selectedPhotos.delete(photoId);
            }
            updateUI();
        }
    });

    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', () => {
            if (selectedPhotos.size > 0) {
                deletePhotosCallback(Array.from(selectedPhotos));
            }
        });
    }

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            if (!selectionMode) return;
            const allCheckboxes = galleryContainer.querySelectorAll('.photo-selection-checkbox');
            const shouldSelectAll = selectedPhotos.size < allCheckboxes.length;
            allCheckboxes.forEach(checkbox => {
                if (checkbox.checked !== shouldSelectAll) {
                    checkbox.checked = shouldSelectAll;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }

    // Ініціалізуємо початковий стан UI
    updateUI();
}