<div class="content-card">
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="form-header">
            <h2>Налаштування сайту</h2>
            <div class="actions-cell">
                <button type="submit" class="action-btn save" title="Зберегти"><i class="fas fa-save"></i></button>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tab-nav">
                <a href="#general" class="tab-link active" data-tab="general"><i class="fas fa-info-circle"></i> Основні</a>
                <a href="#localization" class="tab-link" data-tab="localization"><i class="fas fa-globe"></i> Локалізація</a>
                <a href="#appearance" class="tab-link" data-tab="appearance"><i class="fas fa-paint-brush"></i> Вигляд</a>
            </div>

            <div class="tab-content-wrapper">
                <div id="general" class="tab-content active">
                    <div class="form-body">
                        <div class="form-group-inline">
                            <label for="site_name">Назва сайту</label>
                            <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group-inline">
                            <label for="site_description">Опис (Description)</label>
                            <textarea id="site_description" name="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group-inline">
                            <label for="site_keywords">Ключові слова (Keywords)</label>
                            <input type="text" id="site_keywords" name="site_keywords" class="form-control" value="<?php echo htmlspecialchars($settings['site_keywords'] ?? ''); ?>">
                        </div>
                        <div class="form-group-inline">
                            <label for="flash_message_duration">Тривалість flash-повідомлення, сек.</label>
                            <input type="number" id="flash_message_duration" name="flash_message_duration" class="form-control" value="<?php echo htmlspecialchars($settings['flash_message_duration'] ?? 10); ?>" min="1" step="1">
                        </div>
                    </div>
                </div>

                <div id="localization" class="tab-content">
                    <div class="form-body">
                        <div class="form-group-inline">
                            <label for="site_timezone">Часовий пояс</label>
                            <select id="site_timezone" name="site_timezone" class="form-control">
                                <?php foreach ($timezones as $timezone): ?>
                                <option value="<?php echo $timezone; ?>" <?php if (($settings['site_timezone'] ?? 'Europe/Kyiv') == $timezone) echo 'selected'; ?>>
                                    <?php echo $timezone; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group-inline">
                            <label for="site_currency">Валюта</label>
                            <input type="text" id="site_currency" name="site_currency" class="form-control" value="<?php echo htmlspecialchars($settings['site_currency'] ?? 'UAH'); ?>">
                        </div>
                        <div class="form-group-inline">
                            <label for="site_weight_unit">Міра ваги</label>
                            <input type="text" id="site_weight_unit" name="site_weight_unit" class="form-control" value="<?php echo htmlspecialchars($settings['site_weight_unit'] ?? 'kg'); ?>">
                        </div>
                        <div class="form-group-inline">
                            <label for="site_length_unit">Міра довжини</label>
                            <input type="text" id="site_length_unit" name="site_length_unit" class="form-control" value="<?php echo htmlspecialchars($settings['site_length_unit'] ?? 'cm'); ?>">
                        </div>
                    </div>
                </div>
                
                <div id="appearance" class="tab-content">
                    <div class="form-body">
                        
                        <div class="form-group-inline">
                            <label for="favicon">Favicon (.ico)</label>
                            <div class="form-control-wrapper">
                                <div id="favicon-upload-zone" class="file-upload-zone">
                                    <i class="fas fa-image"></i>
                                    <p><strong id="favicon-text">Перетягніть favicon.ico сюди</strong> або натисніть, щоб обрати</p>
                                    <input type="file" id="favicon" name="favicon" accept=".ico">
                                </div>
                                <?php if (file_exists(BASE_PATH . '/public/favicon.ico')): ?>
                                    <small>Поточний: <img src="<?php echo PROJECT_URL; ?>/favicon.ico?v=<?php echo time(); ?>" alt="favicon" style="vertical-align: middle; width: 16px; height: 16px;"></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group-inline">
                            <label for="site_template">Шаблон сайту</label>
                            <div class="form-control-wrapper">
                                <select id="site_template" name="site_template" class="form-control">
                                    <?php 
                                        $currentTemplate = $settings['site_template'] ?? 'default';
                                        foreach($themes as $theme):
                                    ?>
                                    <option value="<?php echo $theme; ?>" <?php if ($currentTemplate == $theme) echo 'selected'; ?>><?php echo ucfirst($theme); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .file-upload-zone { border: 2px dashed var(--border-color); border-radius: 8px; padding: 1rem; text-align: center; cursor: pointer; position: relative; transition: background-color 0.2s; }
    .file-upload-zone:hover { background-color: var(--sidebar-hover-bg); }
    .file-upload-zone i { font-size: 2rem; color: var(--accent-color); }
    .file-upload-zone p { margin-top: 0.5rem; color: var(--secondary-text); }
    .file-upload-zone input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Логіка для вкладок ---
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    tabLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const tabId = link.getAttribute('data-tab');
            tabLinks.forEach(item => item.classList.remove('active'));
            tabContents.forEach(item => item.classList.remove('active'));
            link.classList.add('active');
            document.getElementById(tabId)?.classList.add('active');
        });
    });

    // --- Логіка для стилізованого поля завантаження favicon ---
    const faviconInput = document.getElementById('favicon');
    const faviconText = document.getElementById('favicon-text');
    if (faviconInput && faviconText) {
        faviconInput.addEventListener('change', () => {
            if (faviconInput.files.length > 0) {
                faviconText.textContent = faviconInput.files[0].name;
                faviconText.style.fontWeight = 'bold';
            } else {
                faviconText.textContent = 'Перетягніть favicon.ico сюди';
                faviconText.style.fontWeight = 'normal';
            }
        });
    }
});
</script>