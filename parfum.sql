-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 20 mai 2025 à 23:53
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `parfum`
--

-- --------------------------------------------------------

--
-- Structure de la table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `logo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `brands`
--

INSERT INTO `brands` (`id`, `name`, `description`, `logo_url`, `created_at`) VALUES
(11, 'chanel n5', '', '', '2025-05-16 19:23:49'),
(6, 'GUCCI', '', '', '2025-05-11 20:50:17'),
(3, 'Tom Ford', 'Marque américaine de luxe', NULL, '2025-05-04 21:54:51'),
(9, 'armani', '', '', '2025-05-12 10:16:50');

-- --------------------------------------------------------

--
-- Structure de la table `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(21, 4, '2025-05-11 19:01:29', '2025-05-11 19:01:29');

-- --------------------------------------------------------

--
-- Structure de la table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(21, 21, 2, 1, '2025-05-11 19:01:29', '2025-05-11 19:03:03');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(9, 'parfum femme', '', '2025-05-11 20:50:41'),
(2, 'Parfums Hommes', 'Parfums pour hommes', '2025-05-04 21:54:51'),
(13, 'unisex', '', '2025-05-16 19:24:02');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` enum('credit_card','paypal','cash_on_delivery') NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_postal_code` varchar(20) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_first_name` varchar(50) NOT NULL,
  `shipping_last_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_method`, `shipping_address`, `shipping_city`, `shipping_postal_code`, `shipping_phone`, `shipping_first_name`, `shipping_last_name`, `created_at`, `updated_at`) VALUES
(1, 4, 215.00, 'delivered', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-06 21:31:11', '2025-05-11 20:56:28'),
(2, 4, 86.00, 'delivered', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-06 21:32:10', '2025-05-06 21:42:08'),
(3, 4, 86.00, 'processing', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-06 21:34:02', '2025-05-11 21:46:30'),
(8, 9, 200.00, 'pending', 'cash_on_delivery', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 21:04:28', '2025-05-11 21:04:28'),
(5, 4, 86.00, 'processing', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-06 23:31:16', '2025-05-11 20:56:21'),
(6, 4, 86.00, 'processing', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-06 23:33:20', '2025-05-11 20:29:45'),
(9, 9, 100.00, 'pending', 'cash_on_delivery', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 21:10:43', '2025-05-11 21:10:43'),
(10, 9, 150.00, 'delivered', 'cash_on_delivery', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 21:35:48', '2025-05-11 21:46:42'),
(11, 9, 50.00, 'delivered', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 21:44:14', '2025-05-11 21:46:38'),
(12, 1, 250.00, 'pending', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 21:48:23', '2025-05-11 21:48:23'),
(13, 9, 50.00, 'pending', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 22:04:07', '2025-05-11 22:04:07'),
(16, 9, 50.00, 'pending', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 22:10:00', '2025-05-11 22:10:00'),
(17, 9, 100.00, 'pending', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 22:14:40', '2025-05-11 22:14:40'),
(19, 9, 50.00, 'pending', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 22:15:31', '2025-05-11 22:15:31'),
(20, 9, 50.00, 'pending', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 22:17:06', '2025-05-11 22:17:06'),
(21, 9, 50.00, 'pending', 'credit_card', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 22:23:18', '2025-05-11 22:23:18'),
(22, 9, 50.00, 'processing', 'cash_on_delivery', 'sdrftgyhujkop^mpoiuytreszqsertyui', 'rabat', '0123', '0123456789', 'zineb', 'hilmi', '2025-05-11 22:34:41', '2025-05-16 19:24:26');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 1, 5, 43.00, '2025-05-06 21:31:12'),
(2, 2, 1, 2, 43.00, '2025-05-06 21:32:10'),
(3, 3, 1, 2, 43.00, '2025-05-06 21:34:02'),
(8, 8, 7, 4, 50.00, '2025-05-11 21:04:28'),
(5, 5, 2, 2, 43.00, '2025-05-06 23:31:16'),
(6, 6, 2, 2, 43.00, '2025-05-06 23:33:20'),
(9, 9, 7, 2, 50.00, '2025-05-11 21:10:44'),
(10, 10, 7, 3, 50.00, '2025-05-11 21:35:48'),
(11, 11, 7, 1, 50.00, '2025-05-11 21:44:14'),
(12, 12, 7, 5, 50.00, '2025-05-11 21:48:23'),
(13, 13, 7, 1, 50.00, '2025-05-11 22:04:07'),
(14, 16, 7, 1, 50.00, '2025-05-11 22:10:00'),
(15, 17, 7, 2, 50.00, '2025-05-11 22:14:40'),
(16, 19, 7, 1, 50.00, '2025-05-11 22:15:31'),
(17, 20, 7, 1, 50.00, '2025-05-11 22:17:06'),
(18, 21, 7, 1, 50.00, '2025-05-11 22:23:18'),
(19, 22, 7, 1, 50.00, '2025-05-11 22:34:41');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `brand_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `brand_id`, `category_id`, `image_url`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(9, 'si', '', 500.00, 9, 9, '', 100, '2025-05-16 19:22:49', '2025-05-16 19:22:49'),
(8, 'chanel n5', '', 500.00, 1, 9, '', 100, '2025-05-12 10:15:54', '2025-05-12 10:15:54');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `first_name`, `last_name`, `address`, `city`, `postal_code`, `country`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@luxeparfums.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User', NULL, NULL, NULL, NULL, NULL, '2025-05-04 21:54:51', '2025-05-04 21:54:51'),
(2, 'myspace', 'myspace@luxeparfums.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'moi', 'User', NULL, NULL, NULL, NULL, NULL, '2025-05-04 22:44:04', '2025-05-04 22:44:04'),
(8, 'zineb', 'zinebhilmi105@gmail.com', '$2y$12$5P8bqEOroSAjM38.uPByq..t0z4VYB.uXNGQHDH.pMGrfhVcVYstK', 'client', 'zineb', 'hilmi', NULL, NULL, NULL, NULL, NULL, '2025-05-11 21:01:36', '2025-05-11 21:01:36'),
(9, 'ikrame', 'zinebhilmi.02@gmail.com', '$2y$12$RePyxnPTVvVdedqDngbv9OjsN4mDwxuDtrinGPChNpCz50zot1SM2', 'client', 'zineb', 'hilmi', NULL, NULL, NULL, NULL, NULL, '2025-05-11 21:03:07', '2025-05-11 21:03:07');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
