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

-- Змінено на INSERT IGNORE
INSERT IGNORE INTO `%%PREFIX%%roles` (`id`, `role_name`, `perm_chat`, `perm_roles`, `perm_users`, `perm_categories`, `perm_goods`, `perm_warehouses`, `perm_arrivals`, `perm_transfers`, `perm_albums`) VALUES
(1, 'Адміністратор', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed', 'vaed');

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

-- Змінено на INSERT IGNORE
INSERT IGNORE INTO `%%PREFIX%%users` (`id`, `name`, `email`, `password`, `role_id`, `token`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$fW.1.Yups8QPkZ3jOE6vTu3S1ajw2g5S8V8C55qhbZxdgBv0uMh.S', 1, NULL);

CREATE TABLE IF NOT EXISTS `%%PREFIX%%categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `length` decimal(10,2) DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  `height` decimal(10,2) DEFAULT NULL,
  `weight` decimal(10,3) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%warehouses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%product_stock` (
  `good_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(15,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (`good_id`,`warehouse_id`),
  KEY `warehouse_id` (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%product_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_date` datetime NOT NULL,
  `good_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `balance` decimal(15,3) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `related_transaction_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `good_id` (`good_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `user_id` (`user_id`),
  KEY `transaction_date` (`transaction_date`),
  KEY `transaction_type` (`transaction_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%photo_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `cover_image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%chat_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%chat_group_members` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `recipient_id` (`recipient_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `%%PREFIX%%users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `%%PREFIX%%roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `%%PREFIX%%categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%goods`
  ADD CONSTRAINT `goods_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `%%PREFIX%%categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%product_stock`
  ADD CONSTRAINT `product_stock_ibfk_1` FOREIGN KEY (`good_id`) REFERENCES `%%PREFIX%%goods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_stock_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `%%PREFIX%%warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%product_transactions`
  ADD CONSTRAINT `product_transactions_ibfk_1` FOREIGN KEY (`good_id`) REFERENCES `%%PREFIX%%goods` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `product_transactions_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `%%PREFIX%%warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `product_transactions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `%%PREFIX%%users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%photo_albums`
  ADD CONSTRAINT `photo_albums_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `%%PREFIX%%photo_albums` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `%%PREFIX%%photo_albums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%chat_group_members`
  ADD CONSTRAINT `chat_group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `%%PREFIX%%chat_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chat_group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `%%PREFIX%%users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `%%PREFIX%%chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `%%PREFIX%%users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `%%PREFIX%%users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `%%PREFIX%%chat_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
