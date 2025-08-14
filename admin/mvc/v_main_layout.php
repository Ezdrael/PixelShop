<?php
// ===================================================================
// Файл: mvc/v_main_layout.php 🕰️
// Розміщення: /mvc/v_main_layout.php
// ===================================================================
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/resources/css/main.css">
</head>

<body data-base-url="<?php echo BASE_URL; ?>">
    <div class="dashboard-container">
        <!-- Бічна панель -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-store"></i>
                <span>PixelShop</span>
            </div>
            <nav class="sidebar-nav">
                <a href="<?php echo BASE_URL; ?>/" class="<?php echo ($current_route == '') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i><span>Інформаційна панель</span></a>
                
                <?php // Перевіряємо, чи є у користувача дозвіл на перегляд ('v') чату
                if (isset($currentUser['perm_chat']) && strpos($currentUser['perm_chat'], 'v') !== false): ?>
                    <a href="<?php echo BASE_URL; ?>/messages" class="<?php echo (strpos($current_route, 'messages') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        <span>Повідомлення <span id="unread-counter" class="unread-badge" style="display: none;">0</span></span>
                    </a>
                <?php endif; ?>

                <?php // Показуємо пункт "Ролі", якщо є хоч якісь права на цей розділ
                if (!empty($currentUser['perm_roles'])): ?>
                    <a href="<?php echo BASE_URL; ?>/roles" class="<?php echo (strpos($current_route, 'roles') !== false) ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i><span>Ролі</span></a>
                <?php endif; ?>

                <?php // Показуємо пункт "Користувачі", якщо є хоч якісь права на цей розділ
                if (!empty($currentUser['perm_users'])): ?>
                    <a href="<?php echo BASE_URL; ?>/users" class="<?php echo (strpos($current_route, 'users') !== false) ? 'active' : ''; ?>"><i class="fas fa-users"></i><span>Користувачі</span></a>
                <?php endif; ?>

                <?php if (!empty($currentUser['perm_categories'])): ?>
                    <a href="<?php echo BASE_URL; ?>/categories" class="<?php echo (strpos($current_route, 'categories') !== false) ? 'active' : ''; ?>"><i class="fas fa-sitemap"></i><span>Категорії</span></a>
                <?php endif; ?>
                
                <?php if (!empty($currentUser['perm_goods'])): ?>
                    <a href="<?php echo BASE_URL; ?>/goods" class="<?php echo (strpos($current_route, 'goods') !== false) ? 'active' : ''; ?>"><i class="fas fa-box-open"></i><span>Товари</span></a>
                <?php endif; ?>

                <?php if (!empty($currentUser['perm_warehouses'])): ?>
                    <a href="<?php echo BASE_URL; ?>/warehouses" class="<?php echo (strpos($current_route, 'warehouses') !== false) ? 'active' : ''; ?>"><i class="fas fa-warehouse"></i><span>Склади</span></a>
                <?php endif; ?>

                <?php if (!empty($currentUser['perm_arrivals'])): ?>
                    <a href="<?php echo BASE_URL; ?>/arrivals" class="<?php echo (strpos($current_route, 'arrivals') !== false) ? 'active' : ''; ?>"><i class="fas fa-truck-loading"></i><span>Надходження</span></a>
                <?php endif; ?>

                <?php if (strpos($currentUser['perm_transfers'] ?? '', 'v') !== false): ?>
                    <a href="<?php echo BASE_URL; ?>/transfers" class="<?php echo (strpos($current_route, 'transfers') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-retweet"></i><span>Переміщення</span>
                    </a>
                <?php endif; ?>

                <?php if ($this->hasPermission('albums', 'v')): ?>
                    <a href="<?php echo BASE_URL; ?>/albums" class="<?php echo (strpos($current_route, 'albums') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i><span>Фотоальбоми</span>
                    </a>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/about" class="<?php echo ($current_route == 'about') ? 'active' : ''; ?>"><i class="fas fa-info-circle"></i><span>Про систему</span></a>
            </nav>
        </aside>

        <!-- Основна частина (контент + хедер) -->
        <div class="content-wrapper">
            <!-- Верхній хедер -->
            <header class="top-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobile-menu-btn"><i class="fas fa-bars"></i></button>
                </div>
                <div class="header-right">
                    <div class="user-profile" id="user-profile-toggle">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['name'] ?? 'Гість'); ?></span>
                        <i class="fas fa-user-circle user-avatar"></i>
                        <div class="user-dropdown" id="user-dropdown">
                            <a href="<?php echo BASE_URL; ?>/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Вийти</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Основний контент сторінки -->
            <main class="main-content">
                <?php if (isset($flashMessage)): ?>
                    <div class="flash-message <?php echo $flashMessage['type']; ?>" id="flashMessage">
                        <span class="flash-text"><?php echo htmlspecialchars($flashMessage['text']); ?></span>
                        <div class="flash-controls">
                            <span class="flash-timer" id="flashTimer">10</span>
                            <button class="flash-close-btn" id="flashCloseBtn">&times;</button>
                        </div>
                    </div>
                <?php endif; ?>
                <?php echo $content; ?>
            </main>
        </div>
    </div>

    <!-- Модальне вікно для підтвердження видалення -->
    <div class="modal-overlay" id="deleteModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalTitle">Підтвердження дії</h3>
                <button class="modal-close" id="modalCloseBtn">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Контент буде вставлятися динамічно -->
            </div>
            <div class="modal-footer" id="modalFooter">
                <button class="modal-btn cancel" id="modalCancelBtn">Ні</button>
                <button class="modal-btn confirm" id="modalConfirmBtn">Так</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="deleteAlbumModalOverlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="deleteAlbumModalTitle">Видалення альбому</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p id="deleteAlbumModalText">Альбом, який ви намагаєтеся видалити, не порожній. Будь ласка, оберіть, що зробити з його вмістом:</p>
                <div class="delete-options">
                    <label><input type="radio" name="delete_action" value="delete_content" checked> Видалити весь вміст разом з альбомом</label>
                    <label><input type="radio" name="delete_action" value="move_content"> Перемістити в інший альбом</label>
                    <div id="move-target-container ">
                        <select name="target_album_id" class="form-control">
                            </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel">Скасувати</button>
                <button class="modal-btn confirm" id="confirmAlbumDeleteBtn">Виконати</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    <script type="module" src="<?php echo BASE_URL; ?>/resources/js/main.js"></script>
</body>
</html>