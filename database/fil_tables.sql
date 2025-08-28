--
-- Наповнення таблиці `roles`
--
INSERT INTO `%%PREFIX%%roles` (`id`, `role_name`, `perm_chat`, `perm_roles`, `perm_users`, `perm_categories`, `perm_goods`, `perm_warehouses`, `perm_arrivals`, `perm_transfers`, `perm_albums`) VALUES
(2, 'Менеджер складу', 'v', '', '', 'v', 'v', 'vaed', 'va', 'vaed', 'v'),
(3, 'Контент-редактор', 'v', '', '', 'vaed', 'vaed', 'v', '', '', 'vaed');

--
-- Наповнення таблиці `users`
-- Паролі для всіх: 'password123'
--
INSERT INTO `%%PREFIX%%users` (`id`, `name`, `email`, `password`, `role_id`) VALUES
(2, 'Віктор Коваленко', 'manager@example.com', '$2y$10$9iQuda.0T.s2F.1g3p.eJ.Vl.d3b.Y2F5H.1Q/x.A8V7Y.Z.O9s1m', 2),
(3, 'Олена Петренко', 'editor@example.com', '$2y$10$5rA.q8j2s.4Lh3g.7H.q/u.d.5v.R1Y/j.6H.g.8N/i.0B.s.A0v2', 3);

--
-- Наповнення таблиці `categories`
--
INSERT INTO `%%PREFIX%%categories` (`id`, `name`, `is_active`, `parent_id`) VALUES
(1, 'Електроніка', 1, NULL),
(2, 'Смартфони', 1, 1),
(3, 'Ноутбуки', 1, 1),
(4, 'Одяг', 1, NULL),
(5, 'Футболки', 1, 4),
(6, 'Джинси', 0, 4),
(7, 'Книги', 1, NULL);

--
-- Наповнення таблиці `goods`
--
INSERT INTO `%%PREFIX%%goods` (`id`, `name`, `description`, `price`, `weight`, `category_id`, `is_active`) VALUES
(1, 'Смартфон Pixel 10 Pro', 'Флагманський смартфон з найкращою камерою.', 35999.00, 0.190, 2, 1),
(2, 'Ноутбук Laptoper Air M5', 'Ультратонкий та потужний ноутбук для роботи та творчості.', 62499.00, 1.240, 3, 1),
(3, 'Футболка "Hello World"', 'Класична футболка для програмістів. 100% бавовна.', 850.00, 0.200, 5, 1),
(4, 'Джинси "Classic Fit"', 'Зручні джинси на кожен день.', 1999.00, 0.600, 6, 1),
(5, 'Книга "Чистий код"', 'Обов''язкова до прочитання для кожного розробника.', 650.00, 0.750, 7, 1),
(6, 'Старий смартфон', 'Вже не продається, але є в базі.', 4500.00, 0.150, 2, 0);

--
-- Наповнення таблиці `warehouses`
--
INSERT INTO `%%PREFIX%%warehouses` (`id`, `name`, `address`) VALUES
(1, 'Основний склад (Київ)', 'м. Київ, вул. Промислова, 1'),
(2, 'Склад-магазин (Львів)', 'м. Львів, пл. Ринок, 10');

--
-- Наповнення таблиць `photo_albums` та `photos`
--
INSERT INTO `%%PREFIX%%photo_albums` (`id`, `name`, `description`, `parent_id`, `cover_image_id`) VALUES
(1, 'Товари 2025', 'Фотографії наших основних товарів.', NULL, NULL),
(2, 'Смартфони', 'Професійні фото смартфонів.', 1, NULL),
(3, 'Ноутбуки', 'Промо-фото ноутбуків.', 1, NULL),
(4, 'Маркетинг', 'Матеріали для рекламних кампаній.', NULL, NULL);

INSERT INTO `%%PREFIX%%photos` (`id`, `album_id`, `filename`, `note`) VALUES
(1, 2, '1.jpg', 'Pixel 10 Pro - вид спереду'),
(2, 2, '2.jpg', 'Pixel 10 Pro - блок камер'),
(3, 3, '3.jpg', 'Laptoper Air M5 - відкритий'),
(4, 4, '4.png', 'Логотип компанії');

UPDATE `%%PREFIX%%photo_albums` SET `cover_image_id` = 1 WHERE `id` = 2;
UPDATE `%%PREFIX%%photo_albums` SET `cover_image_id` = 3 WHERE `id` = 3;

--
-- Імітація руху товарів: `product_transactions` та `product_stock`
--
INSERT INTO `%%PREFIX%%product_transactions` (`transaction_date`, `good_id`, `warehouse_id`, `quantity`, `balance`, `transaction_type`, `document_type`, `user_id`) VALUES
('2025-08-10 10:00:00', 1, 1, 100.000, 100.000, 'arrival', 'arrival_form', 1),
('2025-08-10 10:00:00', 2, 1, 50.000, 50.000, 'arrival', 'arrival_form', 1),
('2025-08-10 10:00:00', 3, 1, 250.000, 250.000, 'arrival', 'arrival_form', 1),
('2025-08-10 10:00:00', 5, 1, 120.000, 120.000, 'arrival', 'arrival_form', 1);

INSERT INTO `%%PREFIX%%product_stock` (`good_id`, `warehouse_id`, `quantity`) VALUES
(1, 1, 100.000),
(2, 1, 50.000),
(3, 1, 250.000),
(5, 1, 120.000);

INSERT INTO `%%PREFIX%%product_transactions` (`transaction_date`, `good_id`, `warehouse_id`, `quantity`, `balance`, `transaction_type`, `document_type`, `user_id`, `related_transaction_id`, `comment`) VALUES
('2025-08-15 14:30:00', 1, 1, -20.000, 80.000, 'transfer_out', 'transfer_form', 2, NULL, 'Партія для магазину у Львові');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `%%PREFIX%%product_transactions` (`transaction_date`, `good_id`, `warehouse_id`, `quantity`, `balance`, `transaction_type`, `document_type`, `user_id`, `related_transaction_id`, `comment`) VALUES
('2025-08-15 14:30:00', 1, 2, 20.000, 20.000, 'transfer_in', 'transfer_form', 2, @last_id, 'Партія для магазину у Львові');

INSERT INTO `%%PREFIX%%product_transactions` (`transaction_date`, `good_id`, `warehouse_id`, `quantity`, `balance`, `transaction_type`, `document_type`, `user_id`, `related_transaction_id`, `comment`) VALUES
('2025-08-15 14:30:00', 2, 1, -10.000, 40.000, 'transfer_out', 'transfer_form', 2, NULL, 'Партія для магазину у Львові');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `%%PREFIX%%product_transactions` (`transaction_date`, `good_id`, `warehouse_id`, `quantity`, `balance`, `transaction_type`, `document_type`, `user_id`, `related_transaction_id`, `comment`) VALUES
('2025-08-15 14:30:00', 2, 2, 10.000, 10.000, 'transfer_in', 'transfer_form', 2, @last_id, 'Партія для магазину у Львові');

UPDATE `%%PREFIX%%product_stock` SET `quantity` = 80.000 WHERE `good_id` = 1 AND `warehouse_id` = 1;
INSERT INTO `%%PREFIX%%product_stock` (`good_id`, `warehouse_id`, `quantity`) VALUES (1, 2, 20.000);
UPDATE `%%PREFIX%%product_stock` SET `quantity` = 40.000 WHERE `good_id` = 2 AND `warehouse_id` = 1;
INSERT INTO `%%PREFIX%%product_stock` (`good_id`, `warehouse_id`, `quantity`) VALUES (2, 2, 10.000);

--
-- Наповнення таблиць чату
--
INSERT INTO `%%PREFIX%%chat_groups` (`id`, `group_name`) VALUES (1, 'Загальний чат');

INSERT INTO `%%PREFIX%%chat_group_members` (`group_id`, `user_id`) VALUES
(1, 1),
(1, 2),
(1, 3);

INSERT INTO `%%PREFIX%%chat_messages` (`sender_id`, `recipient_id`, `group_id`, `body`, `created_at`) VALUES
(1, 2, NULL, 'Вікторе, добрий день! Як пройшло переміщення товарів?', '2025-08-15 16:00:00'),
(2, 1, NULL, 'Доброго! Все чудово, товари вже на складі у Львові.', '2025-08-15 16:01:00'),
(3, NULL, 1, 'Всім привіт! Які плани на сьогодні?', '2025-08-16 09:05:00'),
(1, NULL, 1, 'Привіт! Сьогодні потрібно оновити описи для нових смартфонів.', '2025-08-16 09:06:00');
