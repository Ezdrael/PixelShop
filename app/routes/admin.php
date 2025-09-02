<?php
// ===================================================================
// Файл: app.php 🕰️
// Розміщення: / (коренева папка сайту)
// Призначення: Ініціалізація роутера та визначення маршрутів
// ===================================================================

// Примітка: Всі оператори `require_once` було видалено.
// Передбачається, що налаштовано автозавантажувач класів (наприклад, Composer),
// який завантажує класи з простору імен 'App'.

use App\Core\Router;

// 2. ІНІЦІАЛІЗАЦІЯ РОУТЕРА
$router = new Router();

// 3. ДОДАВАННЯ МАРШРУТІВ
$router->add('', ['controller' => 'main', 'action' => 'index']);
$router->add('about', ['controller' => 'main', 'action' => 'about']);

$router->add('users', ['controller' => 'Users', 'action' => 'index']);
$router->add('users/index', ['controller' => 'Users', 'action' => 'index']);
$router->add('users/watch/(?P<id>\d+)', ['controller' => 'Users', 'action' => 'watch']);
$router->add('users/edit/(?P<id>\d+)', ['controller' => 'Users', 'action' => 'edit']);
$router->add('users/add', ['controller' => 'Users', 'action' => 'add']);
$router->add('users/delete/(?P<id>\d+)', ['controller' => 'Users', 'action' => 'delete']);

$router->add('roles', ['controller' => 'Roles', 'action' => 'index']);
$router->add('roles/index', ['controller' => 'Roles', 'action' => 'index']);
$router->add('roles/watch/(?P<id>\d+)', ['controller' => 'Roles', 'action' => 'watch']);
$router->add('roles/edit/(?P<id>\d+)', ['controller' => 'Roles', 'action' => 'edit']);
$router->add('roles/add', ['controller' => 'Roles', 'action' => 'add']);
$router->add('roles/delete/(?P<id>\d+)', ['controller' => 'Roles', 'action' => 'delete']);

$router->add('categories', ['controller' => 'Categories', 'action' => 'index']);
$router->add('categories/index', ['controller' => 'Categories', 'action' => 'index']);
$router->add('categories/watch/(?P<id>\d+)', ['controller' => 'Categories', 'action' => 'watch']);
$router->add('categories/edit/(?P<id>\d+)', ['controller' => 'Categories', 'action' => 'edit']);
$router->add('categories/add', ['controller' => 'Categories', 'action' => 'add']);
$router->add('categories/delete/(?P<id>\d+)', ['controller' => 'Categories', 'action' => 'delete']);

$router->add('goods', ['controller' => 'Goods', 'action' => 'index']);
$router->add('goods/index', ['controller' => 'Goods', 'action' => 'index']);
$router->add('goods/add', ['controller' => 'Goods', 'action' => 'add']);
$router->add('goods/watch/(?P<id>\d+)', ['controller' => 'Goods', 'action' => 'watch']);
$router->add('goods/edit/(?P<id>\d+)', ['controller' => 'Goods', 'action' => 'edit']);
$router->add('goods/delete/(?P<id>\d+)', ['controller' => 'Goods', 'action' => 'delete']);
$router->add('goods/getByWarehouse', ['controller' => 'Goods', 'action' => 'getByWarehouse']);
$router->add('goods/getWarehousesForGood', ['controller' => 'Goods', 'action' => 'getWarehousesForGood']);

$router->add('warehouses', ['controller' => 'Warehouses', 'action' => 'index']);
$router->add('warehouses/index', ['controller' => 'Warehouses', 'action' => 'index']);
$router->add('warehouses/add', ['controller' => 'Warehouses', 'action' => 'add']);
$router->add('warehouses/watch/(?P<id>\d+)', ['controller' => 'Warehouses', 'action' => 'watch']);
$router->add('warehouses/edit/(?P<id>\d+)', ['controller' => 'Warehouses', 'action' => 'edit']);
$router->add('warehouses/delete/(?P<id>\d+)', ['controller' => 'Warehouses', 'action' => 'delete']);

$dateTimePattern = '(?P<datetime>\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})';
$userIdPattern = '(?P<userid>\d+)';
$router->add('arrivals', ['controller' => 'Arrivals', 'action' => 'index']);
$router->add('arrivals/search/(?P<params>.*)', ['controller' => 'Arrivals', 'action' => 'index']); // Новий маршрут для пошуку
$router->add('arrivals/add', ['controller' => 'Arrivals', 'action' => 'add']);
$router->add('arrivals/watch/(?P<id>[a-zA-Z0-9-]+)', ['controller' => 'Arrivals', 'action' => 'watch']);
$router->add('arrivals/edit/(?P<id>[a-zA-Z0-9-]+)', ['controller' => 'Arrivals', 'action' => 'edit']);
$router->add('arrivals/delete/(?P<id>[a-zA-Z0-9-]+)', ['controller' => 'Arrivals', 'action' => 'delete']);

$router->add('messages/settings', ['controller' => 'Messages', 'action' => 'getChatSettings']);
$router->add('messages/groups/create', ['controller' => 'Messages', 'action' => 'createGroup']);
$router->add('messages/groups/update/(?P<id>\d+)', ['controller' => 'Messages', 'action' => 'updateGroup']);
$router->add('messages/groups/delete/(?P<id>\d+)', ['controller' => 'Messages', 'action' => 'deleteGroup']);
$router->add('messages/get-conversations', ['controller' => 'Messages', 'action' => 'getConversations']);
$router->add('messages/unread', ['controller' => 'Messages', 'action' => 'getUnread']);
$router->add('messages/fetch', ['controller' => 'Messages', 'action' => 'fetchMessages']);
$router->add('messages/send', ['controller' => 'Messages', 'action' => 'sendMessage']);
$router->add('messages/markread', ['controller' => 'Messages', 'action' => 'markAsRead']);

$router->add('transfers', ['controller' => 'Transfers', 'action' => 'index']);
$router->add('transfers/add', ['controller' => 'Transfers', 'action' => 'add']);
$router->add('transfers/edit/(?P<ids>[\d,]+)', ['controller' => 'Transfers', 'action' => 'edit']);
$router->add('transfers/watch/(?P<ids>[\d,]+)', ['controller' => 'Transfers', 'action' => 'watch']);
$router->add('transfers/cancel/(?P<ids>[\d,]+)', ['controller' => 'Transfers', 'action' => 'cancel']);

$router->add('albums', ['controller' => 'PhotoAlbums', 'action' => 'index']);
$router->add('albums/add', ['controller' => 'PhotoAlbums', 'action' => 'add']);
$router->add('albums/delete/(?P<id>\d+)', ['controller' => 'PhotoAlbums', 'action' => 'delete']);
$router->add('albums/delete-photo/(?P<id>\d+)', ['controller' => 'PhotoAlbums', 'action' => 'deletePhoto']);
$router->add('albums/edit/(?P<id>\d+)', ['controller' => 'PhotoAlbums', 'action' => 'edit']);
$router->add('albums/get-for-move', ['controller' => 'PhotoAlbums', 'action' => 'getAlbumsForMove']);
$router->add('albums/view/(?P<id>\d+)', ['controller' => 'PhotoAlbums', 'action' => 'viewAlbum']);
$router->add('albums/set-cover/(?P<albumId>\d+)/(?P<photoId>\d+)', ['controller' => 'PhotoAlbums', 'action' => 'setCover']);
$router->add('albums/upload/(?P<id>\d+)', ['controller' => 'PhotoAlbums', 'action' => 'upload']);
$router->add('photos/move', ['controller' => 'PhotoAlbums', 'action' => 'movePhotos']);

$router->add('settings', ['controller' => 'Settings', 'action' => 'index']); 

$router->add('currencies', ['controller' => 'Currencies', 'action' => 'index']);
$router->add('currencies/add', ['controller' => 'Currencies', 'action' => 'add']);
$router->add('currencies/delete/(?P<id>\d+)', ['controller' => 'Currencies', 'action' => 'delete']);
$router->add('currencies/update-rates', ['controller' => 'Currencies', 'action' => 'updateRates']);

$router->add('writeoffs', ['controller' => 'Writeoffs', 'action' => 'index']);
$router->add('writeoffs/add', ['controller' => 'Writeoffs', 'action' => 'add']);
$router->add('writeoffs/edit/(?P<ids>[\d,]+)', ['controller' => 'Writeoffs', 'action' => 'edit']);
$router->add('writeoffs/delete/(?P<ids>[\d,]+)', ['controller' => 'Writeoffs', 'action' => 'delete']);

$router->add('notes/get', ['controller' => 'Notes', 'action' => 'get']);
$router->add('notes/create', ['controller' => 'Notes', 'action' => 'create']);
$router->add('notes/update/(?P<id>\d+)', ['controller' => 'Notes', 'action' => 'update']);
$router->add('notes/delete/(?P<id>\d+)', ['controller' => 'Notes', 'action' => 'delete']);

$router->add('clipboard/get', ['controller' => 'Clipboard', 'action' => 'get']);
$router->add('clipboard/add', ['controller' => 'Clipboard', 'action' => 'add']);
$router->add('clipboard/clear', ['controller' => 'Clipboard', 'action' => 'clear']);

$router->add('account/settings', ['controller' => 'Account', 'action' => 'settings']);

// 1. Отримуємо URI і ВІДРІЗАЄМО GET-ПАРАМЕТРИ (все, що після знаку '?')
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');

// 2. "Відрізаємо" префікс 'admin/' від URL
$requestUri = trim($requestUri, '/');
$admin_route = preg_replace('/^admin\/?/', '', $requestUri);

// 3. Запускаємо роутер з контекстом 'Admin'
$router->dispatch($admin_route, 'Admin');