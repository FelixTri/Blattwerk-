CREATE DATABASE IF NOT EXISTS blattwerk_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blattwerk_shop;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 27. Apr 2025 um 21:18
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `blattwerk_shop`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(201, 5, 2, 1),
(202, 5, 3, 1),
(218, 1, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(3, 'Kakteen'),
(2, 'Kräuter'),
(1, 'Zimmerpflanzen');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `invoices`
--

INSERT INTO `invoices` (`id`, `order_id`, `invoice_number`, `created_at`) VALUES
(1, 1, 'RW2025-000001', '2025-04-26 16:17:45'),
(2, 2, 'RW2025-000002', '2025-04-26 16:18:14'),
(3, 6, 'RW2025-000003', '2025-04-26 16:19:11'),
(4, 7, 'RW2025-000004', '2025-04-26 16:27:04'),
(5, 10, 'RW2025-000005', '2025-04-26 20:58:16'),
(6, 12, 'RW2025-000006', '2025-04-26 21:09:37'),
(7, 13, 'RW2025-000007', '2025-04-27 21:06:47');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_used` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `payment_used`, `created_at`) VALUES
(1, 1, NULL, '2025-04-18 17:37:26'),
(2, 1, NULL, '2025-04-18 18:00:10'),
(3, 5, NULL, '2025-04-25 17:42:04'),
(4, 1, NULL, '2025-04-26 15:46:29'),
(5, 1, NULL, '2025-04-26 15:56:32'),
(6, 1, NULL, '2025-04-26 16:04:24'),
(7, 1, NULL, '2025-04-26 16:27:00'),
(8, 1, NULL, '2025-04-26 17:15:33'),
(9, 1, NULL, '2025-04-26 17:20:10'),
(10, 1, 'stored', '2025-04-26 20:58:09'),
(11, 1, 'stored', '2025-04-26 21:03:54'),
(12, 1, 'stored', '2025-04-26 21:09:35'),
(13, 1, 'stored', '2025-04-27 21:06:43');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `order_items`
--

CREATE TABLE `order_items` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `order_items`
--

INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`) VALUES
(1, 3, 1),
(1, 2, 1),
(1, 1, 1),
(1, 4, 1),
(2, 1, 2),
(2, 2, 4),
(2, 3, 2),
(3, 2, 1),
(3, 3, 1),
(4, 1, 1),
(4, 2, 1),
(5, 2, 1),
(5, 3, 1),
(6, 2, 1),
(7, 2, 3),
(8, 2, 1),
(10, 2, 1),
(13, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `rating`, `image`, `category_id`) VALUES
(1, 'Monstera Deliciosa', 'Tropische Zimmerpflanze mit großen Blättern.', 29.99, 4.5, 'productpictures/monstera.jpg', 1),
(2, 'Basilikum', 'Frisches Basilikum im Topf.', 3.49, 4.0, 'productpictures/basilikum.jpg', 2),
(3, 'Aloe Vera', 'Pflegeleichte Sukkulente mit heilender Wirkung.', 12.90, 4.8, 'productpictures/aloe.jpg', 1),
(4, 'Kaktus', 'Kleiner Deko-Kaktus mit Blüte.', 9.51, 3.9, 'productpictures/kaktus.jpg', 3);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `salutation` varchar(10) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `payment_info` text DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `salutation`, `first_name`, `last_name`, `address`, `postal_code`, `city`, `email`, `username`, `password`, `payment_info`, `role`, `active`) VALUES
(1, 'Herr', 'Christoph', 'Bout', 'Krafft-Ebinggasse 4', '1140', 'Wien', 'boutchristoph@gmail.com', 'wi23b005', '$2y$10$a5mEspqKZQeJaw2dfe/oreI.zZM0lnOSzA/N6zoNd34kdyweyP7qG', 'AT22 0000 0000 0000', 'user', 1),
(3, 'Herr', 'Christoph', 'Bout', 'Krafft-Ebinggase 4', '1140', 'Wien', 'wi23b005@technikum-wien.at', 'wi23b005_01', '$2y$10$G4uRa8k/OE2F8APgiXH.g.0A2Bsbki1KW66MC9wecvLyvLBksNJ3S', '', 'user', 1),
(5, 'Herr', 'Admin', 'Admin', 'Aidmin', '0000', 'Admin', 'admin@admin.com', 'admin', '$2y$10$YWsan0hoEPPOABlfjfUqzeIXqJVt0XdpLbLBbcQoUuKcoBJHpaz5W', '', 'admin', 1),
(6, 'Herr', 'User', 'User', 'User', '0000', 'User', 'user@user.com', 'user', '$2y$10$3qkPY0YOjuhWc/dVmmo16OwKgXs1QDjkdVyqBP929DQLHJRMQuWym', '', 'user', 1);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indizes für die Tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indizes für die Tabelle `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_invoice_number` (`invoice_number`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indizes für die Tabelle `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`) USING BTREE;

--
-- Indizes für die Tabelle `order_items`
--
ALTER TABLE `order_items`
  ADD KEY `fk_order_id` (`order_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- Indizes für die Tabelle `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT für Tabelle `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT für Tabelle `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT für Tabelle `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints der Tabelle `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;


CREATE TABLE vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(5) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
