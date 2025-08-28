<?php
// ===================================================================
// Файл: mvc/_template_permissions_table.php
// Призначення: Шаблон для відображення таблиці дозволів.
// Очікує змінну $permissions_source, яка містить дані про дозволи.
// ===================================================================

// Допоміжна функція для відображення іконок
function render_permission_icon($permissions, $char) {
    if (strpos($permissions ?? '', $char) !== false) {
        return '<i class="fas fa-check-circle perm-icon yes"></i>';
    } else {
        return '<i class="fas fa-times-circle perm-icon no"></i>';
    }
}
?>
<table class="orders-table">
    <thead>
        <tr>
            <th>Розділ</th>
            <th style="text-align: center;">Перегляд (v)</th>
            <th style="text-align: center;">Додавання (a)</th>
            <th style="text-align: center;">Редагування (e)</th>
            <th style="text-align: center;">Видалення (d)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Повідомлення</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_chat'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_chat'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_chat'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_chat'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Ролі</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_roles'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_roles'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_roles'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_roles'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Користувачі</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_users'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_users'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_users'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_users'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Категорії</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_categories'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_categories'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_categories'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_categories'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Товари</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_goods'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_goods'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_goods'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_goods'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Склади</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_warehouses'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_warehouses'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_warehouses'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_warehouses'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Надходження</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_arrivals'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_arrivals'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_arrivals'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_arrivals'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Переміщення</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_transfers'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_transfers'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_transfers'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_transfers'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Фотоальбоми</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_albums'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_albums'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_albums'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_albums'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Валюти</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_currencies'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_currencies'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_currencies'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_currencies'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Списання</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_writeoffs'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_writeoffs'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_writeoffs'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_writeoffs'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Налаштування</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_settings'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_settings'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_settings'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_settings'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Нотатки</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_notes'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_notes'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_notes'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_notes'], 'd'); ?></td>
        </tr>
        <tr>
            <td>Буфер обміну</td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_clipboard'], 'v'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_clipboard'], 'a'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_clipboard'], 'e'); ?></td>
            <td style="text-align: center;"><?php echo render_permission_icon($permissions_source['perm_clipboard'], 'd'); ?></td>
        </tr>
    </tbody>
</table>