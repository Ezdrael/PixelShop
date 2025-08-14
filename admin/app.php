<?php
// ===================================================================
// Файл: app.php 🕰️
// Розміщення: / (коренева папка сайту)
// Призначення: Ініціалізація роутера та визначення маршрутів
// ===================================================================

// 1. ПІДКЛЮЧЕННЯ ОСНОВНИХ ФАЙЛІВ СИСТЕМИ
require_once ROOT . '/core/Router.php';
require_once ROOT . '/mvc/c_base.php';
require_once ROOT . '/mvc/m_roles.php';
require_once ROOT . '/mvc/m_categories.php';
require_once ROOT . '/mvc/m_goods.php';
require_once ROOT . '/mvc/m_warehouses.php';
require_once ROOT . '/mvc/m_arrivals.php';
require_once ROOT . '/mvc/m_messages.php';
require_once ROOT . '/mvc/m_transfers.php';
require_once ROOT . '/mvc/m_photoalbums.php';
require_once ROOT . '/mvc/m_photos.php';

// 2. ІНІЦІАЛІЗАЦІЯ РОУТЕРА
$router = new Router();

// 3. ДОДАВАННЯ МАРШРУТІВ
$router->add('', ['controller' => 'main', 'action' => 'index']);
$router->add('about', ['controller' => 'main', 'action' => 'about']);

$router->add('users', ['controller' => 'users', 'action' => 'index']);
$router->add('users/index', ['controller' => 'users', 'action' => 'index']);
$router->add('users/watch/(?P<id>\d+)', ['controller' => 'users', 'action' => 'watch']);
$router->add('users/edit/(?P<id>\d+)', ['controller' => 'users', 'action' => 'edit']); 
$router->add('users/add', ['controller' => 'users', 'action' => 'add']); 
$router->add('users/delete/(?P<id>\d+)', ['controller' => 'users', 'action' => 'delete']);

$router->add('roles', ['controller' => 'roles', 'action' => 'index']);
$router->add('roles/index', ['controller' => 'roles', 'action' => 'index']);
$router->add('roles/watch/(?P<id>\d+)', ['controller' => 'roles', 'action' => 'watch']);
$router->add('roles/edit/(?P<id>\d+)', ['controller' => 'roles', 'action' => 'edit']);
$router->add('roles/add', ['controller' => 'roles', 'action' => 'add']);
$router->add('roles/delete/(?P<id>\d+)', ['controller' => 'roles', 'action' => 'delete']);

$router->add('categories', ['controller' => 'categories', 'action' => 'index']);
$router->add('categories/index', ['controller' => 'categories', 'action' => 'index']);
$router->add('categories/watch/(?P<id>\d+)', ['controller' => 'categories', 'action' => 'watch']);
$router->add('categories/edit/(?P<id>\d+)', ['controller' => 'categories', 'action' => 'edit']);
$router->add('categories/add', ['controller' => 'categories', 'action' => 'add']);
$router->add('categories/delete/(?P<id>\d+)', ['controller' => 'categories', 'action' => 'delete']);

$router->add('goods', ['controller' => 'goods', 'action' => 'index']);
$router->add('goods/index', ['controller' => 'goods', 'action' => 'index']);
$router->add('goods/add', ['controller' => 'goods', 'action' => 'add']);
$router->add('goods/watch/(?P<id>\d+)', ['controller' => 'goods', 'action' => 'watch']);
$router->add('goods/edit/(?P<id>\d+)', ['controller' => 'goods', 'action' => 'edit']);
$router->add('goods/delete/(?P<id>\d+)', ['controller' => 'goods', 'action' => 'delete']);

$router->add('warehouses', ['controller' => 'warehouses', 'action' => 'index']);
$router->add('warehouses/add', ['controller' => 'warehouses', 'action' => 'add']);
$router->add('warehouses/watch/(?P<id>\d+)', ['controller' => 'warehouses', 'action' => 'watch']);
$router->add('warehouses/edit/(?P<id>\d+)', ['controller' => 'warehouses', 'action' => 'edit']);
$router->add('warehouses/delete/(?P<id>\d+)', ['controller' => 'warehouses', 'action' => 'delete']);

$dateTimePattern = '(?P<datetime>\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})';
$userIdPattern = '(?P<userid>\d+)';
$router->add('arrivals', ['controller' => 'arrivals', 'action' => 'index']);
$router->add('arrivals/add', ['controller' => 'arrivals', 'action' => 'add']);
$router->add('arrivals/watch/' . $dateTimePattern . '/' . $userIdPattern, ['controller' => 'arrivals', 'action' => 'watch']);

$router->add('messages', ['controller' => 'messages', 'action' => 'index']);
$router->add('messages/fetch', ['controller' => 'messages', 'action' => 'fetchMessages']);
$router->add('messages/send', ['controller' => 'messages', 'action' => 'sendMessage']);
$router->add('messages/auth', ['controller' => 'messages', 'action' => 'authPusher']);
$router->add('messages/unread', ['controller' => 'messages', 'action' => 'getUnread']);
$router->add('messages/markread', ['controller' => 'messages', 'action' => 'markAsRead']);

$router->add('transfers', ['controller' => 'transfers', 'action' => 'index']);
$router->add('transfers/add', ['controller' => 'transfers', 'action' => 'add']);
$router->add('transfers/edit/(?P<ids>[\d,]+)', ['controller' => 'transfers', 'action' => 'edit']);
$router->add('transfers/watch/(?P<ids>[\d,]+)', ['controller' => 'transfers', 'action' => 'watch']);
$router->add('transfers/cancel/(?P<ids>[\d,]+)', ['controller' => 'transfers', 'action' => 'cancel']);

$router->add('albums', ['controller' => 'photoalbums', 'action' => 'index']);
$router->add('albums/add', ['controller' => 'photoalbums', 'action' => 'add']);
$router->add('albums/view/(?P<id>\d+)', ['controller' => 'photoalbums', 'action' => 'viewAlbum']);
$router->add('albums/edit/(?P<id>\d+)', ['controller' => 'photoalbums', 'action' => 'edit']);
$router->add('albums/upload/(?P<id>\d+)', ['controller' => 'photoalbums', 'action' => 'upload']);
$router->add('albums/delete-photo/(?P<id>\d+)', ['controller' => 'photoalbums', 'action' => 'deletePhoto']);
$router->add('albums/delete/(?P<id>\d+)', ['controller' => 'photoalbums', 'action' => 'delete']);
$router->add('albums/get-for-move', ['controller' => 'photoalbums', 'action' => 'getAlbumsForMove']);
$router->add('albums/set-cover/(?P<albumId>\d+)/(?P<photoId>\d+)', ['controller' => 'photoalbums', 'action' => 'setCover']);

$router->add('posts/show/(?P<id>\d+)', ['controller' => 'post', 'action' => 'show']);

// 4. ЗАПУСК РОУТЕРА
$router->dispatch($_GET['route'] ?? '');