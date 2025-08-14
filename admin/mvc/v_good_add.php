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
                <a href="#" class="tab-link active" data-tab="general"><i class="fas fa-info-circle"></i>Загальні</a>
                <a href="#" class="tab-link" data-tab="data"><i class="fas fa-database"></i>Дані</a>
                <a href="#" class="tab-link" data-tab="relations"><i class="fas fa-link"></i>Зв'язки</a>
                <a href="#" class="tab-link" data-tab="options"><i class="fas fa-cogs"></i>Опції</a>
                <a href="#" class="tab-link" data-tab="attributes"><i class="fas fa-tags"></i>Характеристики</a>
                <a href="#" class="tab-link" data-tab="images"><i class="fas fa-image"></i>Зображення</a>
            </div>

            <div class="tab-content-wrapper">
                <div class="tab-content active" id="general">
                    <div class="form-body">
                        <div class="form-group-inline"><label for="good-active">Статус</label><div class="form-control-wrapper"><label class="toggle-switch"><input type="checkbox" id="good-active" name="is_active" value="1" <?php if (!isset($good) || !empty($good['is_active'])) echo 'checked'; ?>><span class="slider"></span></label></div></div>
                        <div class="form-group-inline"><label for="good-name">Назва товару<span class="required-field">*</span></label><div class="form-control-wrapper"><input type="text" id="good-name" name="name" class="form-control" value="<?php echo htmlspecialchars($good['name'] ?? ''); ?>" required></div></div>
                        <div class="form-group-inline"><label for="good-description">Опис</label><div class="form-control-wrapper"><textarea id="good-description" name="description" class="form-control" rows="8"><?php echo htmlspecialchars($good['description'] ?? ''); ?></textarea></div></div>
                        <div class="form-group-inline"><label for="good-keywords">Ключові слова</label><div class="form-control-wrapper"><input type="text" id="good-keywords" name="keywords" class="form-control" value="<?php echo htmlspecialchars($good['keywords'] ?? ''); ?>"></div></div>
                    </div>
                </div>

                <div class="tab-content" id="data">
                    <div class="form-body">
                        <div class="form-group-inline"><label for="good-price">Ціна, грн<span class="required-field">*</span></label><div class="form-control-wrapper"><input type="number" step="0.01" id="good-price" name="price" class="form-control" value="<?php echo htmlspecialchars($good['price'] ?? '0.00'); ?>" required></div></div>
                        <div class="form-group-inline"><label>Розміри (ДxШxВ), см</label><div class="form-control-wrapper dimensions-group"><input type="number" step="0.01" name="length" placeholder="Довжина" class="form-control" value="<?php echo htmlspecialchars($good['length'] ?? ''); ?>"><input type="number" step="0.01" name="width" placeholder="Ширина" class="form-control" value="<?php echo htmlspecialchars($good['width'] ?? ''); ?>"><input type="number" step="0.01" name="height" placeholder="Висота" class="form-control" value="<?php echo htmlspecialchars($good['height'] ?? ''); ?>"></div></div>
                        <div class="form-group-inline"><label for="good-weight">Вага, кг</label><div class="form-control-wrapper"><input type="number" step="0.001" id="good-weight" name="weight" class="form-control" value="<?php echo htmlspecialchars($good['weight'] ?? ''); ?>"></div></div>
                    </div>
                </div>

                <div class="tab-content" id="relations">
                     <div class="form-body">
                        <div class="form-group-inline">
                            <label for="good-category">Категорія<span class="required-field">*</span></label>
                            <div class="form-control-wrapper">
                                <select id="good-category" name="category_id" class="form-control" required>
                                    <option value="">-- Виберіть категорію --</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php if (isset($good) && $good['category_id'] == $cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="options"><p>Тут буде реалізовано керування опціями товару.</p></div>
                <div class="tab-content" id="attributes"><p>Тут буде реалізовано керування характеристиками товару.</p></div>
                <div class="tab-content" id="images"><p>Тут буде реалізовано завантаження зображень.</p></div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // === ЛОГІКА ДЛЯ ВКЛАДОК ===
    const tabContainer = document.querySelector('.tabs-container');
    if (tabContainer) {
        const tabLinks = tabContainer.querySelectorAll('.tab-link');
        const tabContents = tabContainer.querySelectorAll('.tab-content');
        const formId = document.getElementById('good-form')?.dataset.formId;
        const activeTabKey = formId ? `active-tab_${formId}` : null;

        // Функція для активації вкладки
        const activateTab = (tabId) => {
            const targetLink = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
            const targetContent = document.getElementById(tabId);
            
            if (!targetLink || !targetContent) return;

            tabLinks.forEach(item => item.classList.remove('active'));
            tabContents.forEach(item => item.classList.remove('active'));
            targetLink.classList.add('active');
            targetContent.classList.add('active');
        };

        // Обробник кліків по посиланнях вкладок
        tabLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const tabId = link.dataset.tab;
                activateTab(tabId);
                if (activeTabKey) {
                    sessionStorage.setItem(activeTabKey, tabId);
                }
            });
        });

        // Відновлення активної вкладки при завантаженні
        const savedTab = activeTabKey ? sessionStorage.getItem(activeTabKey) : null;
        if (savedTab) {
            activateTab(savedTab);
        }
    }

    // === ЛОГІКА ДЛЯ ЗБЕРЕЖЕННЯ ДАНИХ ФОРМИ ===
    const goodForm = document.getElementById('good-form');
    if (goodForm) {
        const formId = goodForm.dataset.formId;
        const formDataKey = `form-data_${formId}`;

        // Очищення сховища, якщо на сторінці є повідомлення про успіх
        if (document.querySelector('.flash-message.success')) {
            sessionStorage.removeItem(formDataKey);
            sessionStorage.removeItem(`active-tab_${formId}`);
        }

        // Функція для завантаження даних у форму
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

        // Функція для збереження даних
        const saveFormData = () => {
            const formData = new FormData(goodForm);
            const data = {};
            // Обробляємо всі поля, включаючи невідмічені чекбокси
            for(const field of goodForm.elements){
                if(field.name){
                    if(field.type === 'checkbox'){
                        data[field.name] = field.checked;
                    } else if(field.type !== 'submit'){
                         data[field.name] = field.value;
                    }
                }
            }
            sessionStorage.setItem(formDataKey, JSON.stringify(data));
        };

        // Зберігаємо дані при кожній зміні
        goodForm.addEventListener('input', saveFormData);

        // Завантажуємо дані при завантаженні сторінки
        loadFormData();
    }
});
</script>