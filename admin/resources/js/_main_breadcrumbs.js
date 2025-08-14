/* ===================================================================
   Файл:      _main_breadcrumbs.js
   Призначення: Керування "липкими" хлібними крихтами.
   =================================================================== */

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
        if (crumb.url) {
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

export function initBreadcrumbs() {
    const dataElement = document.getElementById('breadcrumbs-data');
    if (!dataElement) return;

    try {
        const currentPath = JSON.parse(dataElement.textContent);
        if (!Array.isArray(currentPath)) return;

        const savedPathStr = sessionStorage.getItem('stickyBreadcrumbs');
        const savedPath = savedPathStr ? JSON.parse(savedPathStr) : [];
        
        let finalPath = currentPath;

        // --- ВИПРАВЛЕНА ЛОГІКА ---
        // Якщо збережений шлях починається так само, як поточний,
        // це означає, що ми рухаємось по тій самій гілці.
        const isSameBranch = savedPath.length > 0 && currentPath.length > 0 &&
                             savedPath[0].url === currentPath[0].url;

        if (isSameBranch) {
            // Якщо новий шлях довший, він стає новим "найглибшим" шляхом.
            if (currentPath.length > savedPath.length) {
                finalPath = currentPath;
            } else {
                // Інакше (ми піднялися вгору), продовжуємо показувати старий "найглибший" шлях.
                finalPath = savedPath;
            }
        } else {
            // Якщо ми перейшли в зовсім іншу гілку, поточний шлях стає основним.
            finalPath = currentPath;
        }
        
        sessionStorage.setItem('stickyBreadcrumbs', JSON.stringify(finalPath));

        const cleanCurrentUrl = (window.location.origin + window.location.pathname).replace(/\/$/, "");
        renderBreadcrumbs(finalPath, cleanCurrentUrl);

    } catch (e) {
        console.error("Breadcrumbs Error:", e);
    }
}