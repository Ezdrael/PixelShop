/* ===================================================================
   Файл:      _main_breadcrumbs.js
   Призначення: Керування "липкими" хлібними крихтами.
   =================================================================== */

/**
 * Допоміжна функція для перевірки, чи є один шлях префіксом іншого.
 * @param {Array} shortPath - Короткий шлях
 * @param {Array} longPath - Довгий шлях
 * @returns {boolean}
 */
function isPathPrefix(shortPath, longPath) {
    if (shortPath.length > longPath.length) {
        return false;
    }
    for (let i = 0; i < shortPath.length; i++) {
        if (shortPath[i].url !== longPath[i].url) {
            return false;
        }
    }
    return true;
}

/**
 * Рендерить хлібні крихти на сторінці.
 * @param {Array} path - Масив об'єктів для побудови шляху.
 * @param {string} currentPageUrl - URL поточної сторінки для виділення активного елемента.
 */
function renderBreadcrumbs(path, currentPageUrl) {
    const container = document.getElementById('breadcrumbs-container');
    if (!container || !Array.isArray(path)) return;
    
    container.innerHTML = '';
    
    path.forEach((crumb, index) => {
        if (index > 0) {
            const separator = document.createElement('span');
            separator.className = 'separator';
            separator.textContent = '/';
            container.appendChild(separator);
        }

        const crumbUrl = crumb.url ? crumb.url.replace(/\/$/, "") : null;
        const isActive = crumbUrl === currentPageUrl;
        
        let element;
        if (crumb.url && !isActive) { // Робимо активний елемент неклікабельним
            element = document.createElement('a');
            element.href = crumb.url;
        } else {
            element = document.createElement('span');
        }

        element.textContent = crumb.name;
        if (isActive) {
            element.classList.add('active');
        }
        
        container.appendChild(element);
    });
}

/**
 * Ініціалізує логіку хлібних крихт: зчитує дані, обробляє "липкість" та рендерить.
 */
export function initBreadcrumbs() {
    const dataElement = document.getElementById('breadcrumbs-data');
    if (!dataElement) return;

    try {
        const currentPath = JSON.parse(dataElement.textContent);
        if (!Array.isArray(currentPath)) return;

        const savedPathStr = sessionStorage.getItem('stickyBreadcrumbs');
        const savedPath = savedPathStr ? JSON.parse(savedPathStr) : [];
        
        let finalPath = currentPath;

        // --- НОВА, ВИПРАВЛЕНА ЛОГІКА ---
        // Перевіряємо, чи ми знаходимось в тій самій секції (наприклад, "Фотоальбоми")
        const isSameBranch = savedPath.length > 0 && currentPath.length > 0 &&
                             savedPath[0].url === currentPath[0].url;

        if (isSameBranch) {
            // Якщо поточний шлях - це частина збереженого (тобто, ми піднялися вгору),
            // то продовжуємо показувати довший, збережений шлях.
            if (isPathPrefix(currentPath, savedPath)) {
                finalPath = savedPath;
            } else {
                // В іншому випадку (перехід в іншу гілку або глибше),
                // поточний шлях стає новим основним.
                finalPath = currentPath;
            }
        } else {
            // Якщо ми перейшли в зовсім іншу секцію (напр., з Альбомів в Користувачі),
            // поточний шлях стає основним.
            finalPath = currentPath;
        }
        
        // Зберігаємо остаточний шлях у sessionStorage для наступних переходів
        sessionStorage.setItem('stickyBreadcrumbs', JSON.stringify(finalPath));

        // Рендеримо крихти
        const cleanCurrentUrl = (window.location.origin + window.location.pathname).replace(/\/$/, "");
        renderBreadcrumbs(finalPath, cleanCurrentUrl);

    } catch (e) {
        console.error("Помилка обробки хлібних крихт:", e);
        // У випадку помилки, просто рендеримо поточний шлях
        const currentPath = JSON.parse(dataElement.textContent);
        const cleanCurrentUrl = (window.location.origin + window.location.pathname).replace(/\/$/, "");
        renderBreadcrumbs(currentPath, cleanCurrentUrl);
    }
}