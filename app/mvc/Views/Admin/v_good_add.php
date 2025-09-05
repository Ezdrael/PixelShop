<style>
    /* Стилі для двоколонкового макету вкладки "Опції" */
    .tab-content-grid { display: grid; grid-template-columns: 200px 1fr; gap: 1.5rem; }
    .options-nav { border-right: 1px solid var(--border-color); padding-right: 1.5rem; }
    .options-nav-list { list-style: none; padding: 0; margin: 0; }
    .options-nav-list li { padding: 0.75rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; margin-bottom: 0.5rem; border: 1px solid transparent; transition: all .2s; }
    .options-nav-list li.active { background-color: var(--accent-color); color: white; border-color: var(--accent-color); }
    .options-nav-list li:not(.active):hover { background-color: var(--sidebar-hover-bg); }
    #add-option-btn { width: 100%; margin-bottom: 1rem; }
    .option-content { display: none; }
    .option-content.active { display: block; }

    /* Стилі для рядка характеристик */
    .attribute-row { display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; }
</style>

<div class="content-card">
    <form action="" method="POST" id="good-form" data-form-id="good-<?php echo $good['id'] ?? 'new'; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2><?php echo $this->title; ?></h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти"><i class="fas fa-save"></i></button>
                <a href="<?php echo BASE_URL; ?>/goods" class="action-btn" title="До списку"><i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
        
        <div class="tabs-container">
            <div class="tab-nav">
                <a href="#" class="tab-link active" data-tab="general" title="Загальні"><i class="fas fa-info-circle"></i><span>Загальні</span></a>
                <a href="#" class="tab-link" data-tab="data" title="Дані"><i class="fas fa-database"></i><span>Дані</span></a>
                <a href="#" class="tab-link" data-tab="relations" title="Зв'язки"><i class="fas fa-link"></i><span>Зв'язки</span></a>
                <a href="#" class="tab-link" data-tab="options" title="Опції"><i class="fas fa-swatchbook"></i><span>Опції</span></a>
                <a href="#" class="tab-link" data-tab="attributes" title="Атрибути"><i class="fas fa-tasks"></i><span>Атрибути</span></a>
                <a href="#" class="tab-link" data-tab="sales" title="Акції"><i class="fas fa-tags"></i><span>Акції</span></a>
                <a href="#" class="tab-link" data-tab="discounts" title="Знижки"><i class="fas fa-percent"></i><span>Знижки</span></a>
                <a href="#" class="tab-link" data-tab="coupons" title="Промокоди"><i class="fas fa-ticket-alt"></i><span>Промокоди</span></a>
                <a href="#" class="tab-link" data-tab="bonuses" title="Бонусні бали"><i class="fas fa-coins"></i><span>Бонусні бали</span></a>
                <a href="#" class="tab-link" data-tab="images" title="Зображення"><i class="fas fa-image"></i><span>Зображення</span></a>
            </div>

            <div class="tab-content-wrapper">
                <?php
                    // Підключаємо всі частини вкладок
                    include __DIR__ . '/goods/_tabs/_tab_general.php';
                    include __DIR__ . '/goods/_tabs/_tab_data.php';
                    include __DIR__ . '/goods/_tabs/_tab_relations.php';
                    include __DIR__ . '/goods/_tabs/_tab_images.php';
                    include __DIR__ . '/goods/_tabs/_tab_options.php';
                    include __DIR__ . '/goods/_tabs/_tab_attributes.php';
                    include __DIR__ . '/goods/_tabs/_tab_sales.php';
                    include __DIR__ . '/goods/_tabs/_tab_discounts.php';
                    include __DIR__ . '/goods/_tabs/_tab_coupons.php';
                    include __DIR__ . '/goods/_tabs/_tab_bonuses.php';
                ?>
            </div>
        </div>
    </form>
</div>

<template id="option-nav-item-template">
    <li data-option-id=""></li>
</template>

<template id="option-content-template">
    <div class="option-content" data-option-id="">
        <div class="form-body">
            <div class="form-group-inline">
                <label>Назва опції</label>
                <input type="text" name="options[ID_ЗАМІНИ][name]" class="form-control option-name-input" value="Нова опція">
            </div>
            <h4 style="margin-top: 2em; margin-bottom: 1em;">Варіанти опції</h4>
            <table class="orders-table">
                <thead><tr><th>Назва варіанту</th><th>Операція</th><th>Значення</th><th>Тип</th><th style="text-align: right;">Підсумкова ціна</th><th></th></tr></thead>
                <tbody class="option-variants-tbody"></tbody>
            </table>
            <button type="button" class="btn-primary add-variant-btn" style="margin-top: 1rem;"><i class="fas fa-plus"></i> Додати варіант</button>
        </div>
    </div>
</template>

<template id="option-variant-row-template">
    <tr>
        <td><input type="text" name="options[ID_ОПЦІЇ][variants][][name]" class="form-control" placeholder="Напр., Червоний"></td>
        <td>
            <select name="options[ID_ОПЦІЇ][variants][][operation]" class="form-control price-modifier">
                <option value="none"> </option>
                <option value="+">+</option>
                <option value="-">-</option>
            </select>
        </td>
        <td><input type="number" step="0.01" name="options[ID_ОПЦІЇ][variants][][value]" class="form-control price-modifier" placeholder="Напр., 100"></td>
        <td>
            <select name="options[ID_ОПЦІЇ][variants][][type]" class="form-control price-modifier">
                <option value="fixed">грн</option>
                <option value="%">%</option>
            </select>
        </td>
        <td style="text-align: right; font-weight: bold;" class="final-price-cell"></td>
        <td class="actions-cell"><button type="button" class="action-btn delete remove-variant-btn"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- 1. ЛОГІКА ДЛЯ ПЕРЕМИКАННЯ ВКЛАДОК ---
    const tabContainer = document.querySelector('.tabs-container');
    if (tabContainer) {
        const allLinks = tabContainer.querySelectorAll('.tab-link');
        const tabContents = tabContainer.querySelectorAll('.tab-content');
        const formId = document.getElementById('good-form')?.dataset.formId;
        const activeTabKey = formId ? `active-tab_${formId}` : null;

        const activateTab = (tabId) => {
            const targetLink = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
            if (!targetLink) return;

            allLinks.forEach(link => link.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            targetLink.classList.add('active');
            const targetContent = document.getElementById(tabId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        };

        allLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const tabId = link.dataset.tab;
                activateTab(tabId);
                if (activeTabKey) {
                    sessionStorage.setItem(activeTabKey, tabId);
                }
            });
        });

        const savedTab = activeTabKey ? sessionStorage.getItem(activeTabKey) : 'general';
        activateTab(savedTab || 'general');
    }

    // --- 2. ЛОГІКА ДЛЯ ЗБЕРЕЖЕННЯ ДАНИХ ФОРМИ ---
    const goodForm = document.getElementById('good-form');
    if (goodForm) {
        const formId = goodForm.dataset.formId;
        const formDataKey = `form-data_${formId}`;

        if (document.querySelector('.flash-message.success')) {
            sessionStorage.removeItem(formDataKey);
            sessionStorage.removeItem(`active-tab_${formId}`);
        }

        const loadFormData = () => { /* ... (ваш код збереження/завантаження форми) ... */ };
        const saveFormData = () => { /* ... (ваш код збереження/завантаження форми) ... */ };
        goodForm.addEventListener('input', saveFormData);
        loadFormData();
    }

    // --- 3. ЛОГІКА ДЛЯ ВКЛАДКИ "ХАРАКТЕРИСТИКИ" ---
    const attributesContainer = document.getElementById('attributes-container');
    const addAttributeSelect = document.getElementById('add-attribute-select');

    if(addAttributeSelect && attributesContainer) {
        addAttributeSelect.addEventListener('change', function() {
            const attributeId = this.value;
            if (!attributeId) return;
            if (attributesContainer.querySelector(`input[name='attributes[][id]'][value='${attributeId}']`)) {
                alert('Ця характеристика вже додана.');
                this.value = '';
                return;
            }
            const attributeName = this.options[this.selectedIndex].text;
            const newRow = document.createElement('div');
            newRow.className = 'attribute-row';
            newRow.innerHTML = `
                <input type="hidden" name="attributes[][id]" value="${attributeId}">
                <label class="form-control" style="flex-basis: 200px; background: #f8fafc;">${attributeName}</label>
                <input type="text" name="attributes[][value]" class="form-control">
                <button type="button" class="action-btn delete remove-attribute-btn"><i class="fas fa-trash"></i></button>
            `;
            attributesContainer.appendChild(newRow);
            this.value = '';
        });
    }

    if(attributesContainer) {
        attributesContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-attribute-btn')) {
                e.target.closest('.attribute-row').remove();
            }
        });
    }

    // --- 4. ЛОГІКА ДЛЯ ВКЛАДКИ "ОПЦІЇ" ---
    const optionsTab = document.getElementById('options');
    if (optionsTab) {
        const addOptionBtn = document.getElementById('add-option-btn');
        const navList = document.getElementById('options-nav-list');
        const contentContainer = document.getElementById('options-content-container');
        const basePriceInput = document.getElementById('good-price');
        let optionCounter = 0;

        const createNewOption = () => {
            optionCounter++;
            const optionId = `new_${optionCounter}`;
            const navItem = document.getElementById('option-nav-item-template').content.cloneNode(true).firstElementChild;
            navItem.dataset.optionId = optionId;
            navItem.textContent = `Нова опція ${optionCounter}`;
            navList.appendChild(navItem);

            const contentTpl = document.getElementById('option-content-template').innerHTML.replace(/ID_ЗАМІНИ/g, optionId);
            const contentDiv = document.createElement('div');
            contentDiv.innerHTML = contentTpl;
            contentContainer.appendChild(contentDiv.firstElementChild);
            
            switchOption(optionId);
        };

        const switchOption = (optionId) => {
            navList.querySelectorAll('li').forEach(li => li.classList.toggle('active', li.dataset.optionId === optionId));
            contentContainer.querySelectorAll('.option-content').forEach(div => div.classList.toggle('active', div.dataset.optionId === optionId));
        };

        const updateAllPrices = () => {
            const basePrice = parseFloat(basePriceInput.value) || 0;
            document.querySelectorAll('.option-variants-tbody tr').forEach(row => {
                const [op, val, type] = Array.from(row.querySelectorAll('.price-modifier')).map(el => el.value);
                const value = parseFloat(val) || 0;
                let finalPrice = basePrice;

                if (op !== 'none' && value > 0) {
                    let modifier = (type === 'fixed') ? value : basePrice * (value / 100);
                    finalPrice += (op === '+') ? modifier : -modifier;
                }
                
                row.querySelector('.final-price-cell').textContent = `${finalPrice.toFixed(2)} грн`;
            });
        };

        addOptionBtn.addEventListener('click', createNewOption);
        basePriceInput.addEventListener('input', updateAllPrices);
        navList.addEventListener('click', e => {
            if (e.target.tagName === 'LI') switchOption(e.target.dataset.optionId);
        });

        contentContainer.addEventListener('input', e => {
            if (e.target.classList.contains('option-name-input')) {
                const optionId = e.target.closest('.option-content').dataset.optionId;
                const newName = e.target.value.trim() || `Нова опція`;
                navList.querySelector(`li[data-option-id="${optionId}"]`).textContent = newName;
            }
            if (e.target.classList.contains('price-modifier')) {
                updateAllPrices();
            }
        });

        contentContainer.addEventListener('click', e => {
            const addVariantBtn = e.target.closest('.add-variant-btn');
            if (addVariantBtn) {
                const tbody = addVariantBtn.closest('.option-content').querySelector('.option-variants-tbody');
                const optionId = addVariantBtn.closest('.option-content').dataset.optionId;
                const rowTpl = document.getElementById('option-variant-row-template').innerHTML.replace(/ID_ОПЦІЇ/g, optionId);
                tbody.insertAdjacentHTML('beforeend', rowTpl);
                updateAllPrices();
            }
            if (e.target.closest('.remove-variant-btn')) {
                e.target.closest('tr').remove();
            }
        });

        if (navList.children.length === 0) {
             createNewOption();
        }
        updateAllPrices();
    }
});
</script>