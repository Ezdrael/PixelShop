// public/js/product_list.js

// Глобальний масив для зберігання даних товарів
// Він отримується з PHP-змінної allProductsData, яка визначена в list.php
// Додаємо перевірку на undefined, щоб уникнути помилок, якщо allProductsData ще не визначено
const allProducts = typeof allProductsData !== 'undefined' ? allProductsData : [];

// BASE_URL тепер доступний з PHP
const BASE_URL = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/pixelshop/public';


let currentPage = 1;
let productsPerPage = parseInt(localStorage.getItem('productsPerPage')) || 12; // Завантажуємо з localStorage або 12 за замовчуванням
let currentSort = localStorage.getItem('currentSort') || 'default'; // Завантажуємо з localStorage або 'default' за замовчуванням

const productListDiv = document.getElementById('product-list');
const sortBySelect = document.getElementById('sort-by');
const productsPerPageSelect = document.getElementById('products-per-page');
const paginationUl = document.getElementById('pagination-links');

// Встановлюємо вибране значення для productsPerPageSelect при завантаженні
if (productsPerPageSelect) {
    productsPerPageSelect.value = productsPerPage;
}
// Встановлюємо вибране значення для sortBySelect при завантаженні
if (sortBySelect) {
    sortBySelect.value = currentSort;
}


// Функція для рендерингу товарів на сторінці
function renderProducts(productsToRender) {
    if (!productListDiv) {
        console.error("Елемент #product-list не знайдено.");
        return;
    }

    productListDiv.innerHTML = ''; // Очищаємо поточні товари
    const startIndex = (currentPage - 1) * productsPerPage;
    const endIndex = startIndex + productsPerPage;
    const paginatedProducts = productsToRender.slice(startIndex, endIndex);

    if (paginatedProducts.length === 0) {
        const noProductsMessage = document.createElement('div');
        noProductsMessage.className = 'col-12 text-center text-xl text-muted py-5';
        noProductsMessage.textContent = 'Наразі товарів за вибраними критеріями не знайдено.';
        productListDiv.appendChild(noProductsMessage);
        return;
    }

    paginatedProducts.forEach(product => {
        const productCardHtml = `
            <div class="col-12 col-sm-6 col-md-4 mb-4 animate__animated animate__fadeInUp">
                <div class="card product-card h-100">
                    <img src="${product.image_url}"
                         class="card-img-top"
                         alt="${product.name}"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='https://placehold.co/600x400/e0e0e0/ffffff?text=No+Image';">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text flex-grow-1">${product.description}</p>
                        <p class="product-price">${product.displayPrice}</p>
                        <a href="${BASE_URL}/product/detail/${product.id}" class="btn btn-success mt-auto"><i class="fas fa-info-circle me-2"></i>Детальніше</a>
                    </div>
                </div>
            </div>
        `;
        productListDiv.innerHTML += productCardHtml;
    });
}

// Функція для налаштування пагінації
function setupPagination(products) {
    if (!paginationUl) {
        console.error("Елемент #pagination-links не знайдено.");
        return;
    }

    paginationUl.innerHTML = ''; // Очищаємо поточні посилання пагінації
    const totalPages = Math.ceil(products.length / productsPerPage);

    if (totalPages <= 1) {
        return;
    }

    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
        paginationUl.appendChild(li);

        li.querySelector('a').addEventListener('click', (e) => {
            e.preventDefault();
            currentPage = parseInt(e.target.dataset.page);
            applyFiltersAndSort();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
}

// Функція для сортування товарів
function sortProducts(products, sortBy) {
    switch (sortBy) {
        case 'price-asc':
            return [...products].sort((a, b) => a.price - b.price);
        case 'price-desc':
            return [...products].sort((a, b) => b.price - a.price);
        case 'name-asc':
            return [...products].sort((a, b) => a.name.localeCompare(b.name));
        case 'name-desc':
            return [...products].sort((a, b) => b.name.localeCompare(a.name));
        default:
            // Для "За замовчуванням" повертаємо початковий порядок
            // allProducts вже є відфільтрованим за категорією з PHP
            return allProducts.slice();
    }
}

// Функція для застосування фільтрів та сортування
function applyFiltersAndSort() {
    console.log("Applying filters and sort. Current products count:", allProducts.length);
    if (allProducts.length === 0) {
        console.warn("allProducts порожній. Перевірте, чи дані передаються з PHP.");
        // Відображаємо повідомлення про відсутність товарів
        if (productListDiv) {
            productListDiv.innerHTML = '<div class="col-12 text-center text-xl text-muted py-5">Наразі товарів за вибраними критеріями не знайдено. Будь ласка, перевірте налаштування бази даних та контролера.</div>';
        }
        if (paginationUl) {
            paginationUl.innerHTML = ''; // Очищаємо пагінацію
        }
        return;
    }

    let filteredProducts = allProducts.slice(); // Починаємо з копії всіх товарів (вже відфільтрованих PHP за категорією)

    // Застосовуємо сортування
    filteredProducts = sortProducts(filteredProducts, currentSort);

    renderProducts(filteredProducts);
    setupPagination(filteredProducts);
}

// Обробники подій
if (sortBySelect) {
    sortBySelect.addEventListener('change', (e) => {
        currentSort = e.target.value;
        localStorage.setItem('currentSort', currentSort); // Зберігаємо в localStorage
        currentPage = 1; // Скидаємо на першу сторінку при зміні сортування
        applyFiltersAndSort();
    });
}

if (productsPerPageSelect) {
    productsPerPageSelect.addEventListener('change', (e) => {
        productsPerPage = parseInt(e.target.value);
        localStorage.setItem('productsPerPage', productsPerPage); // Зберігаємо в localStorage
        currentPage = 1; // Скидаємо на першу сторінку при зміні кількості товарів на сторінці
        applyFiltersAndSort();
    });
}

// Ініціалізація при завантаженні сторінки
document.addEventListener('DOMContentLoaded', function() {
    // Перевіряємо, чи allProductsData визначено перед викликом applyFiltersAndSort
    if (typeof allProductsData !== 'undefined' && allProductsData.length > 0) {
        console.log("Дані товарів завантажено:", allProductsData.length);
        applyFiltersAndSort(); // Застосовуємо початкове сортування та пагінацію
    } else {
        console.warn("allProductsData не визначено або порожнє при завантаженні DOM.");
        // Можливо, відобразити повідомлення про відсутність товарів одразу
        if (productListDiv) {
            productListDiv.innerHTML = '<div class="col-12 text-center text-xl text-muted py-5">Наразі товарів за вибраними критеріями не знайдено. Будь ласка, перевірте налаштування бази даних та контролера.</div>';
        }
    }
});
