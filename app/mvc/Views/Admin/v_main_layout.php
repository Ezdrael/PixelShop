<?php
// mvc/v_main_layout.php
use App\Core\TokenManager;
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

    <title><?php echo htmlspecialchars($title); ?> - PixelShop</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto+Mono&family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <link rel="stylesheet" href="<?php echo PROJECT_URL; ?>/resources/css/admin/ADMIN-MAIN.css">
    
    <?php
    if (!empty($pageCSS)) {
        foreach ($pageCSS as $css_file) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($css_file) . '">';
        }
    }
    ?>
</head>

<body data-base-url="<?php echo BASE_URL; ?>" 
      data-project-url="<?php echo PROJECT_URL; ?>" 
      data-current-user-id="<?php echo htmlspecialchars($currentUser['id'] ?? 0); ?>" 
      data-flash-duration="<?php echo htmlspecialchars($siteSettings['flash_message_duration'] ?? 10); ?>" 
      data-ws-token="<?php echo TokenManager::generateForUser($currentUser['id'] ?? 0); ?>">
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-store"></i>
                <span>PixelShop</span>
            </div>
            <nav class="sidebar-nav">
                <?php if ($this->hasPermission('dashboard', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="<?php echo (strpos($current_route, 'dashboard') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i><span>Дашборд</span>
                    </a>
                <?php endif; ?>    

                <?php if ($this->hasPermission('roles', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/roles" class="<?php echo (strpos($current_route, 'roles') !== false) ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i><span>Ролі</span></a>
                <?php endif; ?>

                <?php if ($this->hasPermission('users', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/users" class="<?php echo (strpos($current_route, 'users') !== false) ? 'active' : ''; ?>"><i class="fas fa-users"></i><span>Користувачі</span></a>
                <?php endif; ?>

                <?php if ($this->hasPermission('categories', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/categories" class="<?php echo (strpos($current_route, 'categories') !== false) ? 'active' : ''; ?>"><i class="fas fa-sitemap"></i><span>Категорії</span></a>
                <?php endif; ?>
                
                <?php if ($this->hasPermission('goods', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/goods" class="<?php echo (strpos($current_route, 'goods') !== false) ? 'active' : ''; ?>"><i class="fas fa-box-open"></i><span>Товари</span></a>
                <?php endif; ?>

                <?php if ($this->hasPermission('warehouses', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/warehouses" class="<?php echo (strpos($current_route, 'warehouses') !== false) ? 'active' : ''; ?>"><i class="fas fa-warehouse"></i><span>Склади</span></a>
                <?php endif; ?>

                <?php if ($this->hasPermission('arrivals', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/arrivals" class="<?php echo (strpos($current_route, 'arrivals') !== false) ? 'active' : ''; ?>"><i class="fas fa-truck-loading"></i><span>Надходження</span></a>
                <?php endif; ?>

                <?php if ($this->hasPermission('transfers', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/transfers" class="<?php echo (strpos($current_route, 'transfers') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-retweet"></i><span>Переміщення</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('albums', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/albums" class="<?php echo (strpos($current_route, 'albums') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i><span>Фотоальбоми</span>
                    </a>
                <?php endif; ?>

                 <?php if ($this->hasPermission('currencies', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/currencies" class="<?php echo (strpos($current_route, 'currencies') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-money-bill-wave"></i><span>Валюти</span>
                    </a>
                <?php endif; ?>
                
                <?php if ($this->hasPermission('writeoffs', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/writeoffs" class="<?php echo (strpos($current_route, 'writeoffs') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-file-export"></i><span>Списання</span>
                    </a>
                <?php endif; ?>
                
                <?php if ($this->hasPermission('settings', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/settings" class="<?php echo (strpos($current_route, 'settings') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i><span>Налаштування</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('calendar', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/calendar" class="<?php echo (strpos($current_route, 'calendar') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i><span>Календар</span>
                    </a>
                <?php endif; ?>
                
                <?php if ($this->hasPermission('sales', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/sales" class="<?php echo (strpos($current_route, 'sales') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i><span>Акції</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('discounts', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/discounts" class="<?php echo (strpos($current_route, 'discounts') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-percent"></i><span>Знижки</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('coupons', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/coupons" class="<?php echo (strpos($current_route, 'coupons') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-ticket-alt"></i><span>Промокоди</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('bonus_points', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/bonuspoints" class="<?php echo (strpos($current_route, 'bonuspoints') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-coins"></i><span>Бонусні бали</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('attributes', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/attributes" class="<?php echo (strpos($current_route, 'attributes') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-tasks"></i><span>Атрибути</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('options', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/options" class="<?php echo (strpos($current_route, 'options') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-swatchbook"></i><span>Опції товарів</span>
                    </a>
                <?php endif; ?>
            </nav>
        </aside>

        <div class="content-wrapper">
            <header class="top-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobile-menu-btn"><i class="fas fa-bars"></i></button>

                    <?php if ($this->hasPermission('notes', 'v')): ?>
                        <button class="action-btn" id="notes-toggle-btn" title="Нотатки [Alt+N]">
                            <i class="fas fa-sticky-note"></i>
                        </button>
                    <?php endif; ?>
                    <?php if ($this->hasPermission('clipboard', 'v')): ?>
                        <button class="action-btn" id="clipboard-toggle-btn" title="Буфер обміну [Alt+C]">
                            <i class="fas fa-clipboard"></i>
                        </button>
                    <?php endif; ?>
                    <?php if ($this->hasPermission('chat', 'v')): ?>
                        <button class="action-btn" id="messages-toggle-btn" title="Повідомлення [Alt+M]">
                            <i class="fas fa-comments"></i>
                            <span id="unread-counter-widget" class="widget-badge"></span>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="header-right">
                    <div class="user-profile" id="user-profile-toggle">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['name'] ?? 'Гість'); ?></span>
                        <?php if (!empty($currentUser['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($currentUser['avatar_url']); ?>" alt="Avatar" class="user-avatar-img">
                        <?php else: ?>
                            <i class="fas fa-user-circle user-avatar"></i>
                        <?php endif; ?>
                        <div class="user-dropdown" id="user-dropdown">
                            <a href="<?php echo BASE_URL; ?>/account/settings" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Налаштування</span>
                            </a>
                            <a href="<?php echo PROJECT_URL; ?>/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Вийти</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <main class="main-content">
            <?php if (isset($flashMessage)): ?>
                <div class="flash-message <?php echo htmlspecialchars($flashMessage['type']); ?>" 
                     id="flashMessage" 
                     data-duration="<?php echo htmlspecialchars($siteSettings['flash_message_duration'] ?? 10); ?>">
                    
                    <span class="flash-text"><?php echo htmlspecialchars($flashMessage['text']); ?></span>
                    <div class="flash-controls">
                        <span class="flash-timer" id="flashTimer"><?php echo htmlspecialchars($siteSettings['flash_message_duration'] ?? 10); ?></span>
                        <button class="flash-close-btn" id="flashCloseBtn">&times;</button>
                    </div>
                </div>
            <?php endif; ?>
            <?php echo $content; ?>
            </main>
        </div>
    </div>

    <!-- НОТАТКИ -->
    <?php if ($this->hasPermission('notes', 'v')): ?>
    <div id="notes-widget" class="notes-widget">
        <div class="notes-header">
            <h3><i class="fas fa-sticky-note"></i> Мої нотатки</h3>
            <button class="notes-close-btn" title="Esc">&times;</button>
        </div>
        <div class="notes-body">
            <ul id="notes-list" class="notes-list">
                </ul>
        </div>
        <?php if ($this->hasPermission('notes', 'a')): ?>
        <div class="notes-footer">
            <textarea id="new-note-content" placeholder="Введіть текст нової нотатки..." rows="3"></textarea>
            <button id="add-note-btn" class="btn-primary">Додати</button>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- БУФЕР ОБМіНУ -->
    <?php if ($this->hasPermission('clipboard', 'v')): ?>
    <div id="clipboard-widget" class="clipboard-widget">
        <div class="clipboard-header">
            <div class="clipboard-header-left">
                <h3><i class="fas fa-clipboard"></i> Буфер обміну</h3>
                <?php if ($this->hasPermission('clipboard', 'd')): ?>
                <button id="clear-clipboard-btn" class="action-btn clear-btn" title="Очистити буфер">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <?php endif; ?>
            </div>
            <button class="clipboard-close-btn" title="Esc">&times;</button>
        </div>
        <div class="clipboard-body">
            <ul id="clipboard-list" class="clipboard-list"></ul>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ЧАТ -->
    <?php if ($this->hasPermission('chat', 'v')): ?>
    <div id="messages-widget" class="messages-widget">
        <div class="messages-header">
            <h3><i class="fas fa-comments"></i> Повідомлення</h3>
            <button class="messages-close-btn" title="Esc">&times;</button>
        </div>
        <div class="messages-body">
            <div class="conversations-sidebar">
                <div class="conv-sidebar-header">
                    <div class="chat-tabs">
                        <button class="tab-link active" data-tab="users"><i class="fas fa-user"></i> Користувачі</button>
                        <button class="tab-link" data-tab="groups"><i class="fas fa-users"></i> Групи</button>
                    </div>
                    <?php if ($this->hasPermission('chat', 'e')): ?>
                    <button id="chat-settings-btn" class="action-btn" title="Налаштування груп">
                        <i class="fas fa-cog"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="sidebar-content">
                    <div class="list-container">
                        <ul id="conversations-list-users" class="conversation-list tab-content active"></ul>
                        <ul id="conversations-list-groups" class="conversation-list tab-content"></ul>
                    </div>
                    <?php if ($this->hasPermission('chat', 'e')): ?>
                        <div id="chat-settings-panel" class="chat-settings-panel">
                            <div class="settings-header">
                                <h4>Керування групами</h4>
                                <button id="create-group-show-form-btn" class="btn-primary-small"><i class="fas fa-plus"></i> Створити</button>
                            </div>
                            <div id="group-list-container"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <main class="chat-main">
                <div id="chat-welcome" class="chat-window welcome-message"><p>Оберіть чат для перегляду</p></div>
                <div id="chat-window" class="chat-window" style="display: none;">
                    <div id="message-list" class="message-list"></div>
                    <form id="message-form" class="message-form">
                        <div class="message-input-wrapper">
                            <textarea id="message-body-input" placeholder="Напишіть повідомлення..." rows="1" required></textarea>
                            <button type="button" id="emoji-btn" class="input-action-btn"><i class="fas fa-smile"></i></button>
                        </div>
                        <button type="submit" id="send-message-btn" class="send-btn"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </main>
        </div>
        <emoji-picker class="light" id="emoji-picker"></emoji-picker>
    </div>
    <audio id="notification-sound" src="<?php echo PROJECT_URL; ?>/resources/audio/new_message.mp3" preload="auto"></audio>
    <audio id="send-sound" src="<?php echo PROJECT_URL; ?>/resources/audio/send_message.mp3" preload="auto"></audio>
    <?php endif; ?>

    <!-- МОДАЛЬНЕ ВІКНО -->
    <?php include '_template_modals.php'; ?>

    <script src="<?php echo PROJECT_URL; ?>/resources/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    
    <script type="module" src="<?php echo PROJECT_URL; ?>/resources/js/MAIN.js"></script>
    <?php if ($this->hasPermission('notes', 'v')): ?>
        <script type="module" src="<?php echo PROJECT_URL; ?>/resources/js/notes-widget.js"></script>
    <?php endif; ?>
    <?php if ($this->hasPermission('clipboard', 'v')): ?>
        <script type="module" src="<?php echo PROJECT_URL; ?>/resources/js/clipboard-widget.js"></script>
    <?php endif; ?>
    <?php if ($this->hasPermission('chat', 'v')): ?>
        <script type="module" src="<?php echo PROJECT_URL; ?>/resources/js/messages-widget.js"></script>
    <?php endif; ?>

    <?php
    // Універсальний цикл для всіх скриптів, що підключаються зі сторінок (включаючи tinymce-init.js)
    if (!empty($pageJS)) {
        foreach ($pageJS as $js_file) {
            echo '<script defer src="' . htmlspecialchars($js_file) . '"></script>';
        }
    }
    ?>
</body>
</html>