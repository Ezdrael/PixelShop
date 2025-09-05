// public/resources/js/product-edit/options-handler.js
export function initOptionsHandler() {
    const optionsTab = document.getElementById('options');
    if (optionsTab) {
        const addOptionBtn = document.getElementById('add-option-btn');
        const navList = document.getElementById('options-nav-list');
        const contentContainer = document.getElementById('options-content-container');
        const basePriceInput = document.getElementById('good-price');
        let optionCounter = 0;
        //====================================
        const createNewOption = () => {
            optionCounter++;
            const optionId = `new_${optionCounter}`;
            const navItem = document.getElementById('option-nav-item-template').content.cloneNode(true).firstElementChild;
            
            navItem.dataset.optionId = optionId;
            navItem.textContent = `Нова опція ${optionCounter}`;
            navList.appendChild(navItem);

            const contentFragment = document.getElementById('option-content-template').content.cloneNode(true);
            const contentElement = contentFragment.querySelector('.option-content');
            
            contentElement.dataset.optionId = optionId;
            contentElement.querySelectorAll('[name*="ID_ЗАМІНИ"]').forEach(input => {
                input.name = input.name.replace(/ID_ЗАМІНИ/g, optionId);
            });
            contentContainer.appendChild(contentElement);
            
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
}