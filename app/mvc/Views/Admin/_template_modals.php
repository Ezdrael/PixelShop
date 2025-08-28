<?php
// app/mvc/_template_modals.php
?>

<div class="modal-overlay" id="deleteModalOverlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Підтвердження дії</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            </div>
        <div class="modal-footer">
            <button class="modal-btn cancel">Ні, скасувати</button>
            <button class="modal-btn confirm" id="modalConfirmBtn">Так, видалити</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="groupManagementModalOverlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="groupModalTitle">Створення нової групи</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="groupModalBody">
            </div>
    </div>
</div>

<div class="modal-overlay" id="deleteAlbumModalOverlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Видалення альбому</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p id="deleteAlbumModalText">Альбом не порожній. Що зробити з його вмістом?</p>
            <div class="delete-options">
                <label>
                    <input type="radio" name="delete_action" value="delete_content" checked>
                    <span>Видалити весь вміст разом з альбомом</span>
                </label>
                <label>
                    <input type="radio" name="delete_action" value="move_content">
                    <span>Перемістити в інший альбом</span>
                </label>
                <div id="move-target-container">
                    <select name="target_album_id" class="form-control" style="margin-top: 0.5rem;"></select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel">Скасувати</button>
            <button class="modal-btn confirm" id="confirmAlbumDeleteBtn">Виконати</button>
        </div>
    </div>
</div>