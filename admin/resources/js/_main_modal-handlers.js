/* ===================================================================
   Файл:      __main_modal-handlers.js
   Призначення: Керування всіма модальними вікнами в проєкті.
   =================================================================== */

export function initModalHandlers() {
    const simpleDeleteModal = document.getElementById('deleteModalOverlay');
    const albumDeleteModal = document.getElementById('deleteAlbumModalOverlay');

    if (!simpleDeleteModal || !albumDeleteModal) return;

    const baseUrl = document.body.dataset.baseUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- Універсальний обробник для всіх кнопок видалення ---
    document.body.addEventListener('click', async (event) => {
        const button = event.target.closest('.delete-btn');
        if (!button) return;

        event.preventDefault(); // Завжди зупиняємо стандартну дію

        const entity = button.dataset.entity;
        const id = button.dataset.userId;
        const name = button.dataset.userName;
        
        // --- Логіка для АЛЬБОМІВ ---
        if (entity === 'albums') {
            const checkResponse = await fetch(`${baseUrl}/albums/delete/${id}`, {
                method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
            });
            const checkData = await checkResponse.json();

            if (checkData.success) {
                window.location.reload();
                return;
            }
            
            if (checkData.reason === 'has_content') {
                const modalText = albumDeleteModal.querySelector('#deleteAlbumModalText');
                const targetSelect = albumDeleteModal.querySelector('select[name="target_album_id"]');
                const confirmBtn = albumDeleteModal.querySelector('#confirmAlbumDeleteBtn');
                
                modalText.innerHTML = `Альбом <strong>"${name}"</strong> не порожній. Що зробити з його вмістом?`;
                
                const albumsResponse = await fetch(`${baseUrl}/albums/get-for-move?exclude_id=${id}`);
                const albumsData = await albumsResponse.json();
                if (albumsData.success) {
                    targetSelect.innerHTML = albumsData.albums.map(album => `<option value="${album.id}">${album.name}</option>`).join('');
                }
                
                albumDeleteModal.style.display = 'flex';
                
                let newConfirmBtn = confirmBtn.cloneNode(true);
                confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                newConfirmBtn.onclick = async () => {
                    const action = albumDeleteModal.querySelector('input[name="delete_action"]:checked').value;
                    const targetAlbumId = targetSelect.value;
                    const formData = new FormData();
                    formData.append('action', action);
                    formData.append('target_album_id', targetAlbumId);
                    
                    const finalResponse = await fetch(`${baseUrl}/albums/delete/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData });
                    if ((await finalResponse.json()).success) window.location.reload();
                };
            }
        // --- Логіка для ІНШИХ сутностей ---
        } else {
            const modalTitle = simpleDeleteModal.querySelector('#modalTitle');
            const modalBody = simpleDeleteModal.querySelector('#modalBody');
            const confirmBtn = simpleDeleteModal.querySelector('#modalConfirmBtn');
            const elementToRemove = button.closest('.photo-thumbnail');
            
            modalTitle.textContent = 'Підтвердження видалення';
            modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити <strong>${name}</strong>?</p>`;
            
            let newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            newConfirmBtn.onclick = async () => {
                let deleteUrl = (entity === 'photos') ? `${baseUrl}/albums/delete-photo/${id}` : `${baseUrl}/${entity}/delete/${id}`;
                const response = await fetch(deleteUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                const data = await response.json();
                if(data.success) {
                    if (entity === 'photos' && elementToRemove) {
                        elementToRemove.remove();
                        simpleDeleteModal.style.display = 'none';
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Помилка');
                }
            };
            
            simpleDeleteModal.style.display = 'flex';
        }
    });

    // Обробники закриття для обох вікон
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        const closeModal = () => modal.style.display = 'none';
        modal.querySelectorAll('.modal-close, .cancel').forEach(btn => btn.addEventListener('click', closeModal));
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    });
}