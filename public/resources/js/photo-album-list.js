// resources/js/photo-album-list.js

// ВИПРАВЛЕНО ШЛЯХ: тепер він вказує на правильний файл
import { initAlbumDeleteHandler } from './photo-album-view/album-deleter.js';

// Ініціалізуємо його при завантаженні сторінки
document.addEventListener('DOMContentLoaded', () => {
    initAlbumDeleteHandler();
});