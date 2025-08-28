/* ===================================================================
   Файл:      _main_modal-handlers.js
   Призначення: Керування модальними вікнами для ПРОСТИХ сутностей
               (користувачі, ролі, товари і т.д.).
   =================================================================== */

export function initModalHandlers() {
    const simpleDeleteModal = document.getElementById('deleteModalOverlay');
    if (!simpleDeleteModal) return;

    const baseUrl = document.body.dataset.baseUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const confirmBtn = simpleDeleteModal.querySelector('#modalConfirmBtn');

    // --- Універсальний обробник для кнопок .delete-btn ---
    document.body.addEventListener('click', async (event) => {
        const button = event.target.closest('.delete-btn');
        
        // Ігноруємо кнопки видалення альбомів, оскільки вони мають свій,
        // складніший обробник ('album-delete-handler.js').
        if (!button || button.classList.contains('delete-album-btn')) {
            return;
        }

        const entity = button.dataset.entity;
        const id = button.dataset.id || button.dataset.userId; // Підтримка обох атрибутів
        const name = button.dataset.userName;
        
        // Знаходимо елементи модального вікна
        const modalTitle = simpleDeleteModal.querySelector('#modalTitle');
        const modalBody = simpleDeleteModal.querySelector('#modalBody');
        
        // Заповнюємо модальне вікно даними
        modalTitle.textContent = 'Підтвердження видалення';
        modalBody.innerHTML = `<p>Ви впевнені, що хочете видалити <strong>"${name}"</strong>?</p>`;
        
        // Використовуємо addEventListener з опцією { once: true },
        // щоб обробник автоматично видалився після першого ж кліку.
        // Це чистіший спосіб, ніж клонування кнопки.
        confirmBtn.addEventListener('click', async function handleDelete() {
            // Формуємо URL для запиту
            const deleteUrl = (entity === 'arrivals')
                ? `${baseUrl}/arrivals/delete/${id}`
                : (entity === 'photos') 
                    ? `${baseUrl}/albums/delete-photo/${id}` 
                    : `${baseUrl}/${entity}/delete/${id}`;

            try {
                const response = await fetch(deleteUrl, { 
                    method: 'POST', 
                    headers: { 'X-CSRF-TOKEN': csrfToken } 
                });
                
                const data = await response.json();

                if (data.success) {
                    // Для окремих фото видаляємо елемент зі сторінки без перезавантаження
                    if (entity === 'photos') {
                        button.closest('.photo-thumbnail')?.remove();
                        simpleDeleteModal.style.display = 'none';
                    } else {
                        // Для інших сутностей просто перезавантажуємо сторінку,
                        // щоб побачити оновлений список та флеш-повідомлення.
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Сталася помилка під час видалення.');
                }
            } catch (error) {
                console.error("Помилка видалення:", error);
                alert('Не вдалося виконати запит.');
            }

        }, { once: true }); // <--- Ключове покращення
        
        // Показуємо модальне вікно
        simpleDeleteModal.style.display = 'flex';
    });

    // --- Обробники закриття для модального вікна ---
    const closeModal = () => {
        // Оскільки обробник на кнопці "Так" тепер самовидаляється,
        // нам не потрібно його чистити вручну при закритті.
        simpleDeleteModal.style.display = 'none';
    };

    simpleDeleteModal.querySelectorAll('.modal-close, .cancel').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    simpleDeleteModal.addEventListener('click', e => {
        if (e.target === simpleDeleteModal) {
            closeModal();
        }
    });
}