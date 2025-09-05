// public/resources/js/product-edit/attributes-handler.js
export function initAttributesHandler() {
    const attributesContainer = document.getElementById('attributes-container');
    const addAttributeSelect = document.getElementById('add-attribute-select');

    if (addAttributeSelect && attributesContainer) {
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

        attributesContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-attribute-btn')) {
                e.target.closest('.attribute-row').remove();
            }
        });
    }
}