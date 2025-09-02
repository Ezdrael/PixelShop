// public/resources/js/main/breadcrumbs.js

function findNodeById(tree, nodeId) {
    if (nodeId == 0 || nodeId === null) return { children: tree };
    for (const node of tree) {
        if (node.id == nodeId) return node;
        if (node.children) {
            const found = findNodeById(node.children, nodeId);
            if (found) return found;
        }
    }
    return null;
}

function buildAlbumTreeHTML(albums, baseUrl, isSubmenu = false) {
    const ulClass = isSubmenu ? 'album-tree-level submenu' : 'album-tree-level';
    let html = `<ul class="${ulClass}">`;
    for (const album of albums) {
        const hasChildren = album.children && album.children.length > 0;
        html += `<li class="${hasChildren ? 'has-submenu' : ''}">`;
        html += `<a href="${baseUrl}/albums/view/${album.id}">${album.name}</a>`;
        if (hasChildren) {
            html += buildAlbumTreeHTML(album.children, baseUrl, true);
        }
        html += '</li>';
    }
    html += '</ul>';
    return html;
}

/**
 * НОВА ФУНКЦІЯ: Обробляє позиціонування меню
 */
function handleMenuPositioning() {
    const menuItems = document.querySelectorAll('.breadcrumb-dropdown .has-submenu');

    menuItems.forEach(item => {
        const submenu = item.querySelector(':scope > .submenu');
        if (!submenu) return;

        item.addEventListener('mouseenter', () => {
            // 1. Показуємо меню, щоб отримати його розміри
            submenu.classList.add('is-visible');
            
            // 2. Вимірюємо положення меню та ширину вікна
            const rect = submenu.getBoundingClientRect();
            const viewportWidth = window.innerWidth;

            // 3. Перевіряємо, чи виходить меню за правий край екрану
            if (rect.right > viewportWidth) {
                // Якщо так, додаємо клас для відкриття вліво
                item.classList.add('opens-left');
                submenu.classList.add('opens-left');
            } else {
                // Інакше, прибираємо клас (на випадок зміни розміру вікна)
                item.classList.remove('opens-left');
                submenu.classList.remove('opens-left');
            }
        });

        item.addEventListener('mouseleave', () => {
            // Ховаємо меню, коли курсор залишає батьківський елемент
            submenu.classList.remove('is-visible');
        });
    });
}


function renderBreadcrumbs(path, currentPageUrl, albumTree) {
    const container = document.getElementById('breadcrumbs-container');
    if (!container || !Array.isArray(path)) return;
    container.innerHTML = '';
    const baseUrl = document.body.dataset.baseUrl || '';

    path.forEach((crumb, index) => {
        if (index > 0) {
            const separator = document.createElement('span');
            separator.className = 'separator';
            separator.textContent = '/';
            container.appendChild(separator);
        }
        const crumbWrapper = document.createElement('div');
        crumbWrapper.className = 'breadcrumb-item';
        const crumbUrl = crumb.url ? crumb.url.replace(/\/$/, "") : null;
        const isActive = crumbUrl === currentPageUrl;
        let element;
        if (crumb.url && !isActive) {
            element = document.createElement('a');
            element.href = crumb.url;
        } else {
            element = document.createElement('span');
        }
        element.textContent = crumb.name;
        if (isActive) element.classList.add('active');
        crumbWrapper.appendChild(element);

        const parentNode = findNodeById(albumTree, crumb.id);
        const children = parentNode ? parentNode.children || [] : [];
        if (children.length > 0) {
            const dropdown = document.createElement('div');
            dropdown.className = 'breadcrumb-dropdown';
            dropdown.innerHTML = buildAlbumTreeHTML(children, baseUrl);
            crumbWrapper.appendChild(dropdown);
        }
        container.appendChild(crumbWrapper);
    });

    // ВИКЛИКАЄМО НАШУ НОВУ ФУНКЦІЮ ПІСЛЯ СТВОРЕННЯ МЕНЮ
    handleMenuPositioning();
}

export function initBreadcrumbs() {
    const dataElement = document.getElementById('breadcrumbs-data');
    const treeElement = document.getElementById('album-tree-data');
    if (!dataElement || !treeElement) return;

    try {
        const currentPath = JSON.parse(dataElement.textContent);
        const albumTree = JSON.parse(treeElement.textContent);
        const cleanCurrentUrl = (window.location.origin + window.location.pathname).replace(/\/$/, "");
        renderBreadcrumbs(currentPath, cleanCurrentUrl, albumTree);
    } catch (e) {
        console.error("Помилка обробки хлібних крихт:", e);
    }
}