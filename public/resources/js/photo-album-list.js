// public/resources/js/photo-album-list.js

import { initAlbumDeleteHandler } from './photo-album-view/album-deleter.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Ініціалізуємо логіку для кнопок видалення на цій сторінці
    initAlbumDeleteHandler();

    // 2. Додаємо локальні гарячі клавіші для сторінки списку альбомів
    document.addEventListener('keydown', (event) => {
        // Обробка комбінації Alt + = для створення нового альбому
        if (event.altKey && event.code === 'Equal') {
            const createAlbumBtn = document.getElementById('create-album-btn');
            
            // Перевіряємо, чи існує кнопка на сторінці, перш ніж "натиснути"
            if (createAlbumBtn) {
                event.preventDefault();
                createAlbumBtn.click();
            }
        }
    });
});