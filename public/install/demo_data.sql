--
-- Наповнення таблиці `roles`
--
INSERT INTO `%%PREFIX%%roles` (`id`, `role_name`, `perm_chat`, `perm_roles`, `perm_users`, `perm_categories`, `perm_goods`, `perm_warehouses`, `perm_arrivals`, `perm_transfers`, `perm_albums`) VALUES
(2, 'Менеджер складу', 'v', '', '', 'v', 'v', 'vaed', 'va', 'vaed', 'v'),
(3, 'Контент-редактор', 'v', '', '', 'vaed', 'vaed', 'v', '', '', 'vaed');

-- ... (весь інший вміст вашого файлу fil_tables.sql) ...