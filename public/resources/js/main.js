// public/resources/js/main.js
import { initModalHandlers } from './_main_modal-handlers.js';
import { initUiHandlers } from './_main_ui-handlers.js';
import { initBreadcrumbs } from './_main_breadcrumbs.js';

/**
 * Ініціалізує логіку для ОДНОГО flash-повідомлення (таймер, закриття).
 * @param {HTMLElement} flashElement - Елемент повідомлення.
 */
function initializeFlashMessage(flashElement) {
    if (!flashElement) return;

    const oldInterval = parseInt(flashElement.dataset.intervalId, 10);
    if (oldInterval) {
        clearInterval(oldInterval);
    }

    const duration = parseInt(flashElement.dataset.duration, 10) || 10;
    const timerSpan = flashElement.querySelector('#flashTimer');
    const closeBtn = flashElement.querySelector('#flashCloseBtn');
    let timer = duration;

    if(timerSpan) timerSpan.textContent = timer;

    const countdown = setInterval(() => {
        timer--;
        if(timerSpan) timerSpan.textContent = timer;
        if (timer <= 0) {
            clearInterval(countdown);
            flashElement.style.opacity = '0';
            setTimeout(() => flashElement.remove(), 500);
        }
    }, 1000);

    flashElement.dataset.intervalId = countdown;

    if(closeBtn) {
        closeBtn.addEventListener('click', () => {
            clearInterval(countdown);
            flashElement.style.opacity = '0';
            setTimeout(() => flashElement.remove(), 500);
        }, { once: true });
    }
}

/**
 * Створює та показує flash-повідомлення динамічно.
 */
function showFlashMessage(type, text) {
    const duration = document.body.dataset.flashDuration || 10;
    
    const existingFlash = document.getElementById('flashMessage');
    if (existingFlash) {
        existingFlash.remove();
    }

    const flashMessage = document.createElement('div');
    flashMessage.id = 'flashMessage';
    flashMessage.className = `flash-message ${type}`;
    flashMessage.setAttribute('data-duration', duration);
    flashMessage.innerHTML = `
        <span class="flash-text">${text}</span>
        <div class="flash-controls">
            <span class="flash-timer" id="flashTimer">${duration}</span>
            <button class="flash-close-btn" id="flashCloseBtn">&times;</button>
        </div>
    `;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(flashMessage, mainContent.firstChild);
        initializeFlashMessage(flashMessage);
    }
}

// Робимо функцію глобально доступною
window.showFlashMessage = showFlashMessage;

document.addEventListener('DOMContentLoaded', () => {
    initUiHandlers();
    initModalHandlers();
    initBreadcrumbs();

    // Ініціалізуємо повідомлення, що прийшло від сервера при завантаженні сторінки
    const flashMessageOnLoad = document.getElementById('flashMessage');
    if(flashMessageOnLoad) {
        initializeFlashMessage(flashMessageOnLoad);
    }

    // Перевіряємо, чи є повідомлення в sessionStorage (після перезавантаження)
    const storedMessage = sessionStorage.getItem('flashMessage');
    if (storedMessage) {
        const message = JSON.parse(storedMessage);
        showFlashMessage(message.type, message.text);
        sessionStorage.removeItem('flashMessage');
    }
});