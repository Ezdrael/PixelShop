// public/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM повністю завантажено. Починаємо ініціалізацію main.js.");

    let cart = []; // Масив для зберігання товарів у кошику

    // Елементи DOM
    const cartCountBadge = document.querySelector('.navbar .nav-item .badge');
    const cartModalElement = document.getElementById('cartModal');
    const cartModalBody = document.getElementById('cartModalBody');
    const cartTotalElement = document.getElementById('cartTotal');
    const checkoutButton = document.getElementById('checkoutButton');
    const cartNavLink = document.querySelector('.navbar .nav-item a[data-bs-target="#cartModal"]');
    
    // Елементи для сторінки оформлення замовлення
    const checkoutCartItemsElement = document.getElementById('checkoutCartItems'); // Контейнер для товарів у кошику на сторінці оформлення
    const checkoutCartTotalElement = document.getElementById('checkoutCartTotal'); // Елемент для загальної суми на сторінці оформлення

    const checkoutForm = document.getElementById('checkoutForm'); // Форма оформлення замовлення
    const ordersListContainer = document.getElementById('ordersListContainer'); // Контейнер для списку замовлень

    // Елементи для модального вікна входу
    const loginModalElement = document.getElementById('loginModal');
    const googleLoginBtn = document.getElementById('googleLoginBtn');

    // НОВІ ЕЛЕМЕНТИ ДЛЯ АВТОРИЗАЦІЇ
    const authNavItem = document.getElementById('authNavItem'); // <li id="authNavItem">
    console.log("authNavItem:", authNavItem); // Log to check if authNavItem is found

    // Ініціалізуємо екземпляр Bootstrap Modal один раз
    const bsCartModal = new bootstrap.Modal(cartModalElement);
    const bsLoginModal = new bootstrap.Modal(loginModalElement); // Ініціалізуємо модальне вікно входу

    // Ініціалізуємо tooltip для кнопки кошика
    let cartTooltip;
    if (cartNavLink) {
        cartNavLink.setAttribute('data-bs-toggle', 'tooltip');
        cartNavLink.setAttribute('data-bs-placement', 'bottom');
        cartNavLink.setAttribute('data-bs-title', 'Загальна сума: 0 грн'); // Використовуємо data-bs-title
        if (cartNavLink.hasAttribute('title')) { // Видаляємо старий title, якщо він є
            cartNavLink.removeAttribute('title');
        }
        cartTooltip = new bootstrap.Tooltip(cartNavLink);
    }

    // Функції для localStorage
    function loadCartFromLocalStorage() {
        const storedCart = localStorage.getItem('shoppingCart');
        if (storedCart) {
            try {
                cart = JSON.parse(storedCart);
            } catch (e) {
                console.error("Помилка парсингу кошика з localStorage:", e);
                cart = [];
            }
        } else {
            cart = [];
        }
        updateCartCount();
    }

    function saveCartToLocalStorage() {
        localStorage.setItem('shoppingCart', JSON.stringify(cart));
        updateCartCount();
    }

    // Функції кошика
    function updateCartCount() {
        const totalPositions = cart.length;
        if (cartCountBadge) {
            cartCountBadge.textContent = totalPositions;
            cartCountBadge.style.display = totalPositions > 0 ? 'inline-block' : 'none';
        }

        // Оновлюємо текст підказки для кнопки кошика
        if (cartTooltip && cartNavLink) {
            const totalCartPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tooltipText = `Загальна сума: ${totalCartPrice.toLocaleString('uk-UA')} грн`;

            cartTooltip.dispose();
            cartNavLink.setAttribute('data-bs-title', tooltipText);
            cartTooltip = new bootstrap.Tooltip(cartNavLink);
        }
    }

    function addToCart(product, quantityToAdd = 1) { // Додано quantityToAdd
        const existingItem = cart.find(item => item.id === product.id);

        if (existingItem) {
            // Перевіряємо, щоб не перевищити доступну кількість при додаванні до існуючого товару
            const maxAllowed = product.availableQuantity !== undefined ? product.availableQuantity : Infinity;
            const newTotalQuantity = existingItem.quantity + quantityToAdd;

            if (newTotalQuantity > maxAllowed) {
                showCustomAlert(`Ви не можете додати більше ${maxAllowed - existingItem.quantity} шт. товару "${product.name}", оскільки доступно лише ${maxAllowed} шт.`, "warning");
                existingItem.quantity = maxAllowed; // Залишаємо кількість на максимально допустимій
            } else {
                existingItem.quantity = newTotalQuantity;
            }
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                displayPrice: product.displayPrice,
                imageUrl: product.imageUrl,
                quantity: quantityToAdd, // Використовуємо quantityToAdd
                availableQuantity: product.availableQuantity // Зберігаємо доступну кількість
            });
        }
        saveCartToLocalStorage();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        saveCartToLocalStorage();
        renderCartModal();
    }

    function changeQuantity(productId, amount) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            // Отримуємо доступну кількість безпосередньо з об'єкта товару в кошику
            const availableQuantity = item.availableQuantity !== undefined ? item.availableQuantity : Infinity;

            const newQuantity = item.quantity + amount;

            if (newQuantity <= 0) {
                removeFromCart(productId);
            } else if (newQuantity > availableQuantity) {
                // Виводимо попередження, якщо перевищено доступну кількість
                showCustomAlert(`Ви не можете замовити більшу кількість (${newQuantity} шт.), ніж є в наявності (${availableQuantity} шт.).`, "warning");
                item.quantity = availableQuantity; // Залишаємо кількість на максимальній доступній
                saveCartToLocalStorage();
                renderCartModal();
            } else {
                item.quantity = newQuantity;
                saveCartToLocalStorage();
                renderCartModal();
            }
        }
    }

    // Функція для відображення кастомного попередження (замість alert)
    function showCustomAlert(message, type = 'info') {
        const alertPlaceholder = document.getElementById('alertPlaceholder');
        if (!alertPlaceholder) {
            console.warn("Елемент #alertPlaceholder не знайдено. Повідомлення: " + message);
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertPlaceholder.append(wrapper);

        // Автоматичне зникнення через 5 секунд
        setTimeout(() => {
            const alertElement = wrapper.querySelector('.alert');
            if (alertElement) {
                const bsAlert = bootstrap.Alert.getInstance(alertElement) || new bootstrap.Alert(alertElement);
                bsAlert.close();
            }
        }, 5000);
    }

    // Рендеринг модального вікна кошика
    function renderCartModal() {
        cartModalBody.innerHTML = '';
        let totalCartPrice = 0;

        let tableHtml = ''; // Визначаємо тут, щоб використовувати для обох елементів (модального вікна та сторінки оформлення)

        if (cart.length === 0) {
            tableHtml = '<p class="text-center text-muted py-5">Ваш кошик порожній.</p>';
            if (checkoutButton) { // Перевірка на існування кнопки
                checkoutButton.disabled = true;
            }
        } else {
            if (checkoutButton) { // Перевірка на існування кнопки
                checkoutButton.disabled = false;
            }

            tableHtml = `
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm cart-table">
                        <thead>
                            <tr>
                                <th scope="col" class="text-start">Товар</th>
                                <th scope="col" class="text-center">Ціна</th>
                                <th scope="col" class="text-center">Кількість</th>
                                <th scope="col" class="text-end">Сума</th>
                                <th scope="col" class="text-center">Дія</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            cart.forEach(item => {
                const itemTotalPrice = item.price * item.quantity;
                totalCartPrice += itemTotalPrice;

                tableHtml += `
                    <tr>
                        <td class="align-middle text-start">
                            <div class="d-flex align-items-center">
                                <img src="${item.imageUrl}"
                                     alt="${item.name}"
                                     class="cart-item-image me-2"
                                     onerror="this.onerror=null;this.src='https://placehold.co/50x50/e0e0e0/ffffff?text=No+Image';">
                                <a href="${BASE_URL}/product/detail/${item.id}" class="text-primary fw-medium">${item.name}</a>
                            </div>
                        </td>
                        <td class="align-middle text-center">${item.displayPrice}</td>
                        <td class="align-middle text-center">
                            <div class="input-group input-group-sm quantity-control mx-auto">
                                <button class="btn btn-outline-secondary decrease-quantity" type="button" data-id="${item.id}">-</button>
                                <input type="text" class="form-control text-center quantity-display" value="${item.quantity}" readonly>
                                <button class="btn btn-outline-secondary increase-quantity" type="button" data-id="${item.id}">+</button>
                            </div>
                        </td>
                        <td class="align-middle text-end fw-bold text-primary">${(itemTotalPrice).toLocaleString('uk-UA')} грн</td>
                        <td class="align-middle text-center">
                            <button class="btn btn-outline-danger btn-sm remove-item" data-id="${item.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tableHtml += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        if (cartModalBody) { // Оновлюємо модальне вікно кошика
            cartModalBody.innerHTML = tableHtml;
        }
        if (cartTotalElement) { // Оновлюємо загальну суму в модальному вікні
            cartTotalElement.textContent = `${totalCartPrice.toLocaleString('uk-UA')} грн`;
        }


        // Оновлюємо суму та товари на сторінці оформлення замовлення, якщо елементи існують
        if (checkoutCartTotalElement) {
            checkoutCartTotalElement.textContent = `${totalCartPrice.toLocaleString('uk-UA')} грн`;
        }
        if (checkoutCartItemsElement) { // Оновлюємо елемент на сторінці оформлення
            checkoutCartItemsElement.innerHTML = tableHtml;
            // Додаємо обробники подій для кнопок +/-/видалити на сторінці оформлення
            checkoutCartItemsElement.querySelectorAll('.increase-quantity').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = parseInt(e.currentTarget.dataset.id);
                    changeQuantity(productId, 1);
                });
            });

            checkoutCartItemsElement.querySelectorAll('.decrease-quantity').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = parseInt(e.currentTarget.dataset.id);
                    changeQuantity(productId, -1);
                });
            });

            checkoutCartItemsElement.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = parseInt(e.currentTarget.dataset.id);
                    removeFromCart(productId);
                });
            });
        }


        // Обробники подій для кнопок всередині модального вікна (якщо модальне вікно існує)
        if (cartModalBody) {
            cartModalBody.querySelectorAll('.increase-quantity').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = parseInt(e.currentTarget.dataset.id);
                    changeQuantity(productId, 1);
                });
            });

            cartModalBody.querySelectorAll('.decrease-quantity').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = parseInt(e.currentTarget.dataset.id);
                    changeQuantity(productId, -1);
                });
            });

            cartModalBody.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = parseInt(e.currentTarget.dataset.id);
                    removeFromCart(productId);
                });
            });
        }
    }

    // Обробники подій

    // Обробник для кнопок "Додати в кошик"
    document.body.addEventListener('click', function(e) {
        const button = e.target.closest('.btn-add-to-cart');
        if (button) {
            e.preventDefault();

            let product = {};
            let quantityToAdd = 1; // Кількість за замовчуванням

            const productCard = button.closest('.product-card'); // Для сторінок списку товарів
            const productDetailSection = button.closest('.product-detail-section'); // Для сторінки деталей товару

            if (productCard) {
                // Дані з картки товару на сторінках списку
                product = {
                    id: parseInt(productCard.dataset.id),
                    name: productCard.dataset.name,
                    price: parseFloat(productCard.dataset.price),
                    displayPrice: productCard.dataset.displayPrice,
                    imageUrl: productCard.dataset.imageUrl,
                    availableQuantity: parseInt(productCard.dataset.availableQuantity) // Додано доступну кількість
                };
                // На сторінці списку товарів ми не маємо поля вводу кількості,
                // тому додаємо 1 шт.
                quantityToAdd = 1;

            } else if (productDetailSection) {
                // Дані зі сторінки деталей товару
                product = {
                    id: parseInt(button.dataset.id),
                    name: button.dataset.name,
                    price: parseFloat(button.dataset.price),
                    displayPrice: button.dataset.displayPrice,
                    imageUrl: button.dataset.imageUrl,
                    availableQuantity: parseInt(button.dataset.availableQuantity) // Додано доступну кількість
                };
                const quantityInput = productDetailSection.querySelector('#quantity');
                quantityToAdd = parseInt(quantityInput.value);

                const availableQuantity = parseInt(button.dataset.availableQuantity);

                if (isNaN(quantityToAdd) || quantityToAdd <= 0) {
                    showCustomAlert("Будь ласка, введіть дійсну кількість.", "danger");
                    return;
                }

                if (quantityToAdd > availableQuantity) {
                    showCustomAlert(`Ви не можете замовити більше ${availableQuantity} шт., ніж є в наявності.`, "warning");
                    return; // Зупиняємо додавання в кошик
                }
            } else {
                console.error("Не вдалося визначити контекст кнопки 'Додати в кошик'.");
                return;
            }

            // Перевіряємо, чи дані товару дійсні перед додаванням до кошика
            if (product.id && product.name && !isNaN(product.price) && product.imageUrl && !isNaN(product.availableQuantity)) { // Додано перевірку availableQuantity
                addToCart(product, quantityToAdd);
                showCustomAlert(`"${product.name}" додано до кошика у кількості ${quantityToAdd} шт.`, "success");
            } else {
                console.error("Не вдалося отримати повні дані товару для додавання в кошик. Перевірте data-атрибути на кнопці або батьківському елементі.", product);
            }
        }
    });

    // Обробник для кнопки "Кошик" у хедері
    if (cartNavLink) {
        cartNavLink.addEventListener('click', function(e) {
            e.preventDefault();
            renderCartModal(); // Рендеримо вміст кошика перед відкриттям модального вікна
            bsCartModal.show(); // Використовуємо існуючий екземпляр
        });
    }

    // Обробник для кнопки "Оформити замовлення" в модальному вікні кошика
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            bsCartModal.hide(); // Приховуємо модальне вікно кошика
            // Перенаправляємо на сторінку оформлення замовлення
            window.location.href = `${BASE_URL}/order/checkout`;
        });
    }

    // НОВИЙ ОБРОБНИК: Відправка форми оформлення замовлення
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async function(e) {
            e.preventDefault(); // Запобігаємо стандартній відправці форми

            const formData = new FormData(checkoutForm);
            const orderData = {
                fullName: formData.get('fullName'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                city: formData.get('city'),
                address: formData.get('address'),
                notes: formData.get('notes')
            };

            // Додаємо дані кошика
            orderData.cartItems = cart;

            // Валідація (можна додати більш детальну валідацію тут)
            if (cart.length === 0) {
                showCustomAlert('Ваш кошик порожній. Будь ласка, додайте товари.', 'danger');
                return;
            }
            if (!orderData.fullName || !orderData.email || !orderData.phone || !orderData.city || !orderData.address) {
                showCustomAlert('Будь ласка, заповніть усі обов\'язкові поля.', "danger");
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}/order/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (result.success) {
                    showCustomAlert(result.message, 'success');
                    // Зберігаємо tracking_id в localStorage
                    let myOrders = JSON.parse(localStorage.getItem('myOrders') || '[]');
                    if (!myOrders.includes(result.tracking_id)) {
                        myOrders.push(result.tracking_id);
                        localStorage.setItem('myOrders', JSON.stringify(myOrders));
                    }

                    localStorage.removeItem('shoppingCart'); // Очищаємо кошик після успішного замовлення
                    cart = []; // Очищаємо локальний масив кошика
                    updateCartCount(); // Оновлюємо лічильник кошика

                    // Перенаправляємо на сторінку відстеження замовлення, використовуючи tracking_id
                    window.location.href = `${BASE_URL}/order/track/${result.tracking_id}`;
                } else {
                    showCustomAlert(result.message, 'danger');
                }
            } catch (error) {
                console.error('Помилка при відправці замовлення:', error);
                showCustomAlert('Виникла помилка при оформленні замовлення. Спробуйте ще раз.', 'danger');
            }
        });
    }

    // НОВИЙ ОБРОБНИК: Авторизація через Google
    if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', function() {
            console.log("Кнопка Google Login натиснута.");
            bsLoginModal.hide(); // Приховуємо модальне вікно входу

            console.log("Імітація: Запускається процес авторизації через Google...");
            
            // Імітуємо успішну авторизацію
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('userName', 'Іван Петренко'); // Приклад імені
            console.log("Імітація: localStorage оновлено. isLoggedIn:", localStorage.getItem('isLoggedIn'), "userName:", localStorage.getItem('userName'));
            
            showCustomAlert("Імітація: Успішна авторизація через Google!", "success");
            updateAuthUI(); // Оновлюємо UI після входу
            console.log("Імітація: updateAuthUI() викликано.");
        });
    }


    // Логіка для пошуку (якщо вона увімкнена)
    const searchContainer = document.querySelector('.search-container');
    if (searchContainer) {
        const searchInputWrapper = document.querySelector('.search-input-wrapper');
        const searchInput = document.querySelector('.search-input');
        const searchResultsDropdown = document.querySelector('.search-results-dropdown');

        let searchTimeout;
        const SEARCH_DEBOUNCE_DELAY = 300;

        searchContainer.addEventListener('mouseenter', function() {
            searchInput.focus();
        });

        searchContainer.addEventListener('mouseleave', function() {
            if (searchInput.value === '' && document.activeElement !== searchInput) {
                searchResultsDropdown.innerHTML = '';
            }
        });

        searchContainer.querySelector('.search-icon-btn').addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.focus();
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                searchResultsDropdown.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, SEARCH_DEBOUNCE_DELAY);
        });

        async function performSearch(query) {
            searchResultsDropdown.innerHTML = '<div class="no-results">Завантаження...</div>';

            try {
                const response = await fetch(`${BASE_URL}/product/search?query=${encodeURIComponent(query)}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const products = await response.json();
                displaySearchResults(products);

            } catch (error) {
                console.error('Помилка пошуку:', error);
                searchResultsDropdown.innerHTML = '<div class="no-results">Помилка завантаження результатів.</div>';
            }
        }

        function displaySearchResults(products) {
            searchResultsDropdown.innerHTML = '';

            if (products.length === 0) {
                searchResultsDropdown.innerHTML = '<div class="no-results">Нічого не знайдено.</div>';
                return;
            }

            products.forEach(product => {
                const productLink = document.createElement('a');
                productLink.href = `${BASE_URL}/product/detail/${product.id}`;
                productLink.className = 'search-result-item';
                productLink.innerHTML = `
                    <img src="${product.image_url}"
                         alt="${product.name}"
                         onerror="this.onerror=null;this.src='https://placehold.co/50x50/e0e0e0/ffffff?text=No+Image';">
                    <span class="product-name">${product.name}</span>
                    <span class="product-price">${product.displayPrice}</span>
                `;
                searchResultsDropdown.appendChild(productLink);
            });
        }

        document.addEventListener('click', function(e) {
            if (!searchContainer.contains(e.target) && searchInput.value === '') {
                searchResultsDropdown.innerHTML = '';
                searchInput.value = '';
            }
        });

        searchInput.addEventListener('blur', function() {
            if (searchInput.value === '') {
                searchResultsDropdown.innerHTML = '';
            }
        });
    }

    // Додаємо контейнер для кастомних сповіщень (alert)
    const alertPlaceholder = document.createElement('div');
    alertPlaceholder.id = 'alertPlaceholder';
    alertPlaceholder.style.position = 'fixed';
    alertPlaceholder.style.top = '20px';
    alertPlaceholder.style.right = '20px';
    alertPlaceholder.style.zIndex = '1050'; // Вище, ніж модальні вікна
    document.body.appendChild(alertPlaceholder);

    // Функція для оновлення UI авторизації
    function updateAuthUI() {
        try {
            const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
            const userName = localStorage.getItem('userName');
            console.log("updateAuthUI викликано. isLoggedIn:", isLoggedIn, "userName:", userName);

            if (authNavItem) { // Перевірка на існування елемента authNavItem
                authNavItem.innerHTML = ''; // Очищаємо вміст
                if (isLoggedIn && userName) {
                    // Користувач авторизований
                    const dropdownHtml = `
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-2"></i>${userName}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="${BASE_URL}/order/track"><i class="fas fa-box-open me-2"></i>Мої Замовлення</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Налаштування</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-2"></i>Вихід</a></li>
                            </ul>
                        </div>
                    `;
                    authNavItem.innerHTML = dropdownHtml;
                    console.log("UI оновлено: Показано ім'я користувача та випадаюче меню.");

                    // *** ВАЖЛИВО: Ініціалізуємо випадаюче меню Bootstrap для нових елементів ***
                    const dropdownElement = authNavItem.querySelector('.dropdown-toggle');
                    if (dropdownElement) {
                        new bootstrap.Dropdown(dropdownElement);
                        console.log("Bootstrap Dropdown ініціалізовано для #navbarDropdown.");
                    }

                    const logoutBtn = document.getElementById('logoutBtn');
                    if (logoutBtn) {
                        logoutBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            localStorage.removeItem('isLoggedIn');
                            localStorage.removeItem('userName');
                            showCustomAlert("Ви успішно вийшли з облікового запису.", "info");
                            updateAuthUI(); // Оновлюємо UI після виходу
                            console.log("Вихід виконано. updateAuthUI() викликано.");
                        });
                    }
                } else {
                    // Користувач не авторизований
                    authNavItem.innerHTML = `
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-user me-2"></i>Вхід</a>
                    `;
                    console.log("UI оновлено: Показано кнопку 'Вхід'.");
                }
            } else {
                console.warn("Елемент #authNavItem не знайдено. UI авторизації не може бути оновлено.");
            }
        } catch (error) {
            console.error("Помилка в updateAuthUI:", error);
        }
    }

    loadCartFromLocalStorage();

    // Перевіряємо, чи ми на сторінці оформлення замовлення
    if (window.location.pathname.includes('/order/checkout')) {
        // Запускаємо рендеринг кошика з невеликою затримкою, щоб DOM встиг завантажитися
        // Це важливо, оскільки елементи checkoutCartItemsElement та checkoutCartTotalElement
        // можуть бути ще не доступні одразу після DOMContentLoaded
        setTimeout(() => {
            renderCartModal();
        }, 0);
    }

    updateAuthUI(); // Викликаємо при DOMContentLoaded, щоб встановити початковий стан
});
