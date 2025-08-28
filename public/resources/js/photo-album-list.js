// resources/js/photo-album-list.js

// ВИПРАВЛЕНО ШЛЯХ: тепер він вказує на правильний файл
import { initAlbumDeleteHandler } from './_ph-al-vi_album-deleter.js';

// Ініціалізуємо його при завантаженні сторінки
document.addEventListener('DOMContentLoaded', () => {
    initAlbumDeleteHandler();
});