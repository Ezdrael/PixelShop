// public/resources/js/product-edit.js

import { initAttributesHandler } from './product-edit/attributes-handler.js';
import { initOptionsHandler } from './product-edit/options-handler.js';

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

    // --- 2. ЛОГІКА ДЛЯ ЗБЕРЕЖЕННЯ ДАНИХ ФОРМИ У SESSIONSTORAGE ---
    const goodForm = document.getElementById('good-form');
    if (goodForm) {
        const formId = goodForm.dataset.formId;
        const formDataKey = `form-data_${formId}`;

        if (document.querySelector('.flash-message.success')) {
            sessionStorage.removeItem(formDataKey);
            sessionStorage.removeItem(`active-tab_${formId}`);
        }

        const loadFormData = () => {
            const savedData = sessionStorage.getItem(formDataKey);
            if (savedData) {
                const data = JSON.parse(savedData);
                for (const key in data) {
                    const field = goodForm.elements[key];
                    if (field) {
                        if (field.type === 'checkbox') {
                            field.checked = data[key];
                        } else {
                            field.value = data[key];
                        }
                    }
                }
            }
        };

        const saveFormData = () => {
            const data = {};
            for (const field of goodForm.elements) {
                if (field.name) {
                    if (field.type === 'checkbox') {
                        data[field.name] = field.checked;
                    } else if (field.type !== 'submit') {
                        data[field.name] = field.value;
                    }
                }
            }
            sessionStorage.setItem(formDataKey, JSON.stringify(data));
        };

        goodForm.addEventListener('input', saveFormData);
        loadFormData();
    }

    // --- 3. ІНІЦІАЛІЗАЦІЯ ОБРОБНИКІВ ДЛЯ ОКРЕМИХ ВКЛАДОК ---
    initAttributesHandler();
    initOptionsHandler();
});