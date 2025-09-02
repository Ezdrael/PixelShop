<?php
// public/app/Mvc/Views/admin/_template_modals.php (Фінальна версія)
?>

<!-- Модальне вікно підтвердження видалення -->
<div class="modal-overlay" id="deleteModalOverlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Підтвердження дії</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn--cancel">Ні, скасувати</button>
            <button class="modal-btn modal-btn--confirm" id="modalConfirmBtn" title="Ctrl+Enter">Так, видалити</button>
        </div>
    </div>
</div>

<!-- Модальне вікно підтвердження видалення альбому -->
<div class="modal-overlay" id="deleteAlbumModalOverlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Видалення альбому</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p id="deleteAlbumModalText">Альбом не порожній. Що зробити з його вмістом?</p>
            <div class="delete-options">
                <label><input type="radio" name="delete_action" value="delete_content" checked><span>Видалити весь вміст</span></label>
                <label><input type="radio" name="delete_action" value="move_content"><span>Перемістити в інший альбом</span></label>
                <div id="move-target-container"><select name="target_album_id" class="form-control" style="margin-top: 0.5rem;"></select></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn--cancel">Скасувати</button>
            <button class="modal-btn modal-btn--confirm" id="confirmAlbumDeleteBtn" title="Ctrl+Enter">Виконати</button>
        </div>
    </div>
</div>

<!-- Модальне вікно помилки -->
<div id="errorModalOverlay" class="modal-overlay">
    <div class="modal-box modal-box--error">
        <div class="modal-header">
            <h3 id="errorModalTitle">Помилка</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div id="errorModalBody" class="modal-body"></div>
        <div class="modal-footer">
            <button id="errorModalOkBtn" class="modal-btn modal-btn--confirm" title="Ctrl+Enter">OK</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Знаходимо всі модальні вікна на сторінці
    const allModals = document.querySelectorAll('.modal-overlay');

    allModals.forEach(modal => {
        // Функція для закриття конкретного вікна
        const closeModal = () => {
            modal.style.display = 'none';
        };

        // 1. Клік по хрестику, кнопці "Скасувати" або кнопці "ОК" у вікні помилки
        modal.querySelectorAll('.modal-close, .modal-btn--cancel, .cancel, #errorModalOkBtn').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        // 2. Клік поза межами вікна (по темному фону)
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    });

    // 3. Обробка гарячих клавіш, коли будь-яке модальне вікно відкрито
    document.addEventListener('keydown', (event) => {
        // Знаходимо активне (видиме) модальне вікно
        const activeModal = document.querySelector('.modal-overlay[style*="display: flex"]');

        // Якщо жодне вікно не відкрито, нічого не робимо
        if (!activeModal) {
            return;
        }

        // Обробка Escape
        if (event.key === 'Escape') {
            event.preventDefault();
            // Імітуємо клік по кнопці закриття або скасування
            activeModal.querySelector('.modal-close, .modal-btn--cancel, .cancel, #errorModalOkBtn')?.click();
        }

        // Обробка Ctrl+Enter
        if (event.key === 'Enter' && event.ctrlKey) {
            event.preventDefault();
            // Імітуємо клік по основній кнопці підтвердження
            activeModal.querySelector('.modal-btn--confirm, .modal-btn--danger, #modalConfirmBtn')?.click();
        }
    });
});
</script>