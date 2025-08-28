CREATE TABLE IF NOT EXISTS `%%PREFIX%%roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) NOT NULL,
  `perm_chat` varchar(10) NOT NULL DEFAULT '',
  `perm_roles` varchar(10) NOT NULL DEFAULT '',
  `perm_users` varchar(10) NOT NULL DEFAULT '',
  `perm_categories` varchar(10) NOT NULL DEFAULT '',
  `perm_goods` varchar(10) NOT NULL DEFAULT '',
  `perm_warehouses` varchar(10) NOT NULL DEFAULT '',
  `perm_arrivals` varchar(10) NOT NULL DEFAULT '',
  `perm_transfers` varchar(10) NOT NULL DEFAULT '',
  `perm_albums` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ... (всі інші CREATE TABLE запити з вашого файлу) ...

-- Всі ALTER TABLE запити для створення зв'язків
ALTER TABLE `%%PREFIX%%users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `%%PREFIX%%roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ... (всі інші ALTER TABLE запити) ...