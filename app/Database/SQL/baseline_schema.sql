-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: restoCRM
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned DEFAULT NULL,
  `branch_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `reference_id` int unsigned DEFAULT NULL,
  `old_data` json DEFAULT NULL,
  `new_data` json DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_restaurant` (`restaurant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `booking_settings`
--

DROP TABLE IF EXISTS `booking_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `is_enabled` tinyint(1) DEFAULT '0',
  `listed_on_platform` tinyint(1) DEFAULT '0',
  `accepts_online_payment` tinyint(1) DEFAULT '0',
  `booking_advance_days` int DEFAULT '30',
  `slot_duration_min` int DEFAULT '60',
  `min_guests` int DEFAULT '1',
  `max_guests` int DEFAULT '20',
  `deposit_required` tinyint(1) DEFAULT '0',
  `deposit_type` enum('flat','per_person') DEFAULT 'flat',
  `deposit_amount` decimal(8,2) DEFAULT '0.00',
  `cancellation_hours` int DEFAULT '2',
  `auto_confirm` tinyint(1) DEFAULT '1',
  `booking_instructions` text,
  `cuisines` varchar(500) DEFAULT NULL,
  `avg_cost_for_two` decimal(8,2) DEFAULT '0.00',
  `photos` text,
  `tags` varchar(500) DEFAULT NULL,
  `open_time` time DEFAULT '10:00:00',
  `close_time` time DEFAULT '23:00:00',
  `closed_days` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_id` (`restaurant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `booking_slots`
--

DROP TABLE IF EXISTS `booking_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_slots` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `max_covers` int DEFAULT '10',
  `booked_covers` int DEFAULT '0',
  `is_blocked` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `restaurant_date` (`restaurant_id`,`slot_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `branch_type` enum('main','sub','franchise','express','kiosk') DEFAULT 'sub',
  `manager_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `is_24_hours` tinyint(1) DEFAULT '0',
  `working_days` varchar(50) DEFAULT '1,2,3,4,5,6,7',
  `has_dine_in` tinyint(1) DEFAULT '1',
  `has_takeaway` tinyint(1) DEFAULT '1',
  `has_delivery` tinyint(1) DEFAULT '0',
  `printer_ip` varchar(50) DEFAULT NULL,
  `printer_port` int DEFAULT '9100',
  `printer_type` enum('thermal_80mm','thermal_57mm','dot_matrix','laser') DEFAULT 'thermal_80mm',
  `kot_printer_ip` varchar(50) DEFAULT NULL,
  `kot_printer_port` int DEFAULT '9100',
  `billing_prefix` varchar(10) DEFAULT NULL,
  `billing_counter` int unsigned DEFAULT '1',
  `kot_counter` int unsigned NOT NULL DEFAULT '1',
  `kot_prefix` varchar(10) DEFAULT 'KOT',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_handovers`
--

DROP TABLE IF EXISTS `cash_handovers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_handovers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `shift_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `movement_type` enum('handover','bank_deposit','safe_drop') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'handover',
  `amount` decimal(12,2) NOT NULL,
  `denominations` json DEFAULT NULL,
  `slip_number` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','handed','deposited','cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `received_by` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reference_no` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `handed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `shift_id` (`shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `discount_type` enum('percent','flat') DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT '0.00',
  `max_discount_amount` decimal(10,2) DEFAULT '0.00',
  `usage_limit` int DEFAULT '0',
  `usage_count` int DEFAULT '0',
  `per_user_limit` int DEFAULT '1',
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `applicable_on` enum('all','dine_in','takeaway','delivery') DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `anniversary` date DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `notes` text,
  `loyalty_points` decimal(10,2) DEFAULT '0.00',
  `total_orders` int DEFAULT '0',
  `total_spent` decimal(12,2) DEFAULT '0.00',
  `last_visit` datetime DEFAULT NULL,
  `source` enum('walk_in','online','phone','app','other') DEFAULT 'walk_in',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `day_close_logs`
--

DROP TABLE IF EXISTS `day_close_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `day_close_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `closed_by` int unsigned NOT NULL,
  `close_date` date NOT NULL,
  `cash_in` decimal(10,2) DEFAULT '0.00',
  `cash_expenses` decimal(10,2) DEFAULT '0.00',
  `cash_expected` decimal(10,2) DEFAULT '0.00',
  `cash_actual` decimal(10,2) DEFAULT '0.00',
  `cash_difference` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_date` (`branch_id`,`close_date`),
  KEY `branch_id` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `expense_categories_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `amount` decimal(12,2) NOT NULL,
  `paid_by` enum('cash','card','upi','other') DEFAULT 'cash',
  `expense_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference` varchar(200) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory_categories`
--

DROP TABLE IF EXISTS `inventory_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `inventory_categories_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'pcs',
  `min_stock` decimal(10,3) DEFAULT '0.000',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory_stock`
--

DROP TABLE IF EXISTS `inventory_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_stock` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `current_stock` decimal(10,3) DEFAULT '0.000',
  `unit_cost` decimal(10,2) DEFAULT '0.00',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_branch` (`inventory_item_id`,`branch_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `inventory_stock_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_stock_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `transaction_type` enum('purchase','sale','transfer_in','transfer_out','adjustment','waste','return') NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT '0.00',
  `reference_id` int unsigned DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inventory_item_id` (`inventory_item_id`),
  CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_gstin` varchar(20) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `is_printed` tinyint(1) DEFAULT '0',
  `printed_at` datetime DEFAULT NULL,
  `print_count` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kot_items`
--

DROP TABLE IF EXISTS `kot_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kot_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kot_id` int unsigned NOT NULL,
  `order_item_id` int unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `notes` text,
  `status` enum('pending','preparing','ready') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `kot_id` (`kot_id`),
  KEY `order_item_id` (`order_item_id`),
  CONSTRAINT `kot_items_ibfk_1` FOREIGN KEY (`kot_id`) REFERENCES `kots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kot_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kots`
--

DROP TABLE IF EXISTS `kots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kots` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `kot_number` varchar(50) NOT NULL,
  `table_number` varchar(20) DEFAULT NULL,
  `order_type` varchar(50) DEFAULT NULL,
  `status` enum('pending','in_progress','ready','served','cancelled') DEFAULT 'pending',
  `prepared_by` int unsigned DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  `served_at` datetime DEFAULT NULL,
  `printed_at` datetime DEFAULT NULL,
  `estimated_ready_at` datetime DEFAULT NULL,
  `printed_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `kots_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kots_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_rules`
--

DROP TABLE IF EXISTS `loyalty_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_rules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `earn_points_per_amount` decimal(10,2) DEFAULT '1.00',
  `earn_amount` decimal(10,2) DEFAULT '10.00',
  `redeem_points_per_amount` decimal(10,2) DEFAULT '1.00',
  `redeem_value` decimal(10,2) DEFAULT '0.50',
  `min_redeem_points` int DEFAULT '50',
  `max_redeem_percent` decimal(5,2) DEFAULT '20.00',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `loyalty_rules_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_transactions`
--

DROP TABLE IF EXISTS `loyalty_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `customer_id` int unsigned NOT NULL,
  `order_id` int unsigned DEFAULT NULL,
  `transaction_type` enum('earn','redeem','expire','adjust') NOT NULL,
  `points` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `loyalty_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_addon_groups`
--

DROP TABLE IF EXISTS `menu_addon_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_addon_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `selection_type` enum('single','multiple') DEFAULT 'multiple',
  `min_selection` int DEFAULT '0',
  `max_selection` int DEFAULT '0',
  `is_required` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  CONSTRAINT `menu_addon_groups_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_addons`
--

DROP TABLE IF EXISTS `menu_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_addons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `addon_group_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `addon_group_id` (`addon_group_id`),
  CONSTRAINT `menu_addons_ibfk_1` FOREIGN KEY (`addon_group_id`) REFERENCES `menu_addon_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_categories`
--

DROP TABLE IF EXISTS `menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `name_local` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `menu_categories_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_item_addon_groups`
--

DROP TABLE IF EXISTS `menu_item_addon_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_item_addon_groups` (
  `menu_item_id` int unsigned NOT NULL,
  `addon_group_id` int unsigned NOT NULL,
  PRIMARY KEY (`menu_item_id`,`addon_group_id`),
  KEY `addon_group_id` (`addon_group_id`),
  CONSTRAINT `menu_item_addon_groups_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_item_addon_groups_ibfk_2` FOREIGN KEY (`addon_group_id`) REFERENCES `menu_addon_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_item_branches`
--

DROP TABLE IF EXISTS `menu_item_branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_item_branches` (
  `menu_item_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`menu_item_id`,`branch_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `menu_item_branches_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_item_branches_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `category_id` int unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `name_local` varchar(200) DEFAULT NULL,
  `description` text,
  `short_code` varchar(20) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `item_type` enum('veg','non_veg','egg','vegan') DEFAULT 'veg',
  `food_type` enum('food','beverage','dessert','combo','addon','other') DEFAULT 'food',
  `base_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_percent` decimal(5,2) DEFAULT '0.00',
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `cess_percent` decimal(5,2) DEFAULT '0.00',
  `is_recommended` tinyint(1) DEFAULT '0',
  `is_bestseller` tinyint(1) DEFAULT '0',
  `is_spicy` tinyint(1) DEFAULT '0',
  `calories` int DEFAULT NULL,
  `prep_time_mins` int DEFAULT '0',
  `available_from` time DEFAULT NULL,
  `available_to` time DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_variants`
--

DROP TABLE IF EXISTS `menu_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_variants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` int unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `menu_item_id` (`menu_item_id`),
  CONSTRAINT `menu_variants_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text,
  `data_json` json DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_feedback`
--

DROP TABLE IF EXISTS `order_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned DEFAULT NULL,
  `food_rating` tinyint(1) DEFAULT '0',
  `service_rating` tinyint(1) DEFAULT '0',
  `ambience_rating` tinyint(1) DEFAULT '0',
  `overall_rating` tinyint(1) DEFAULT '0',
  `comment` text,
  `would_return` tinyint(1) DEFAULT '0',
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `restaurant_id` (`restaurant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_item_addons`
--

DROP TABLE IF EXISTS `order_item_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_item_addons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` int unsigned NOT NULL,
  `addon_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `order_item_id` (`order_item_id`),
  CONSTRAINT `order_item_addons_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `menu_item_id` int unsigned NOT NULL,
  `variant_id` int unsigned DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `variant_name` varchar(100) DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL DEFAULT '1.000',
  `unit_price` decimal(10,2) NOT NULL,
  `tax_percent` decimal(5,2) DEFAULT '0.00',
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `total_price` decimal(12,2) NOT NULL,
  `notes` text,
  `status` enum('pending','preparing','ready','served','cancelled') DEFAULT 'pending',
  `kot_number` varchar(50) DEFAULT NULL,
  `kot_printed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menu_item_id` (`menu_item_id`),
  KEY `idx_order_name` (`order_id`,`name`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `table_id` int unsigned DEFAULT NULL,
  `customer_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `order_type` enum('dine_in','takeaway','delivery','online','drive_through') DEFAULT 'dine_in',
  `source` enum('pos','qr_customer','online','phone') NOT NULL DEFAULT 'pos',
  `estimated_mins` int DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','ready','served','completed','cancelled','refunded') DEFAULT 'pending',
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `delivery_address` text,
  `delivery_status` enum('unassigned','assigned','out_for_delivery','delivered','failed') DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `no_of_guests` int DEFAULT '1',
  `waiter_id` int unsigned DEFAULT NULL,
  `rider_id` int unsigned DEFAULT NULL,
  `rider_name` varchar(120) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT '0.00',
  `discount_type` enum('flat','percent','coupon','loyalty') DEFAULT 'flat',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `discount_percent` decimal(5,2) DEFAULT '0.00',
  `coupon_code` varchar(50) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `cgst_amount` decimal(10,2) DEFAULT '0.00',
  `sgst_amount` decimal(10,2) DEFAULT '0.00',
  `igst_amount` decimal(10,2) DEFAULT '0.00',
  `cess_amount` decimal(10,2) DEFAULT '0.00',
  `service_charge` decimal(10,2) DEFAULT '0.00',
  `packing_charge` decimal(10,2) DEFAULT '0.00',
  `delivery_charge` decimal(10,2) DEFAULT '0.00',
  `round_off` decimal(5,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) DEFAULT '0.00',
  `balance_amount` decimal(12,2) DEFAULT '0.00',
  `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
  `notes` text,
  `kitchen_notes` text,
  `feedback_token` varchar(64) DEFAULT NULL,
  `feedback_submitted` tinyint(1) DEFAULT '0',
  `cancelled_reason` text,
  `cancelled_by` int unsigned DEFAULT NULL,
  `billed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `table_id` (`table_id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_branch` (`branch_id`,`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','card','upi','wallet','net_banking','online','credit','other') NOT NULL DEFAULT 'cash',
  `payment_reference` varchar(200) DEFAULT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `gateway_response` json DEFAULT NULL,
  `change_returned` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `branch_id` (`branch_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `public_bookings`
--

DROP TABLE IF EXISTS `public_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_bookings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(20) NOT NULL,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned DEFAULT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `guests` int NOT NULL DEFAULT '1',
  `guest_name` varchar(100) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `guest_email` varchar(150) DEFAULT NULL,
  `special_requests` text,
  `occasion` enum('birthday','anniversary','business','date','family','other','none') DEFAULT 'none',
  `status` enum('pending','confirmed','cancelled','completed','no_show') DEFAULT 'pending',
  `payment_status` enum('not_required','pending','paid','refunded') DEFAULT 'not_required',
  `payment_amount` decimal(8,2) DEFAULT '0.00',
  `payment_ref` varchar(100) DEFAULT NULL,
  `cancel_reason` text,
  `cancelled_by` enum('guest','restaurant','system') DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `slot_date` (`slot_date`),
  KEY `guest_phone` (`guest_phone`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int unsigned NOT NULL,
  `table_id` int unsigned DEFAULT NULL,
  `customer_id` int unsigned DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `guests` int DEFAULT '2',
  `occasion` varchar(100) DEFAULT NULL,
  `special_requests` text,
  `status` enum('pending','confirmed','seated','completed','cancelled','no_show') DEFAULT 'pending',
  `notes` text,
  `confirmed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `table_id` (`table_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `restaurants`
--

DROP TABLE IF EXISTS `restaurants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restaurants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` int unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `booking_slug` varchar(100) DEFAULT NULL,
  `short_desc` varchar(300) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `restaurant_type` enum('qsr','casual_dining','fine_dining','cafe','bakery','bar','food_truck','cloud_kitchen','buffet','dhaba','hotel','pizza','ice_cream','juice_bar','other') NOT NULL DEFAULT 'qsr',
  `cuisine_type` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `custom_domain` varchar(255) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `fssai_number` varchar(30) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `pincode` varchar(10) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `currency_symbol` varchar(5) DEFAULT 'Gé¦',
  `timezone` varchar(50) DEFAULT 'Asia/Kolkata',
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `default_tax_percent` decimal(5,2) DEFAULT '5.00',
  `service_charge_percent` decimal(5,2) DEFAULT '0.00',
  `billing_prefix` varchar(10) DEFAULT 'INV',
  `billing_counter` int unsigned DEFAULT '1',
  `kot_prefix` varchar(10) DEFAULT 'KOT',
  `kot_counter` int unsigned DEFAULT '1',
  `theme_color` varchar(10) DEFAULT '#FF6B35',
  `receipt_header` text,
  `receipt_footer` text,
  `receipt_show_logo` tinyint(1) DEFAULT '1',
  `subscription_status` enum('trial','active','suspended','cancelled','expired') DEFAULT 'trial',
  `trial_ends_at` datetime DEFAULT NULL,
  `subscription_starts_at` datetime DEFAULT NULL,
  `subscription_ends_at` datetime DEFAULT NULL,
  `payment_link_token` varchar(64) DEFAULT NULL,
  `payment_link_expires_at` datetime DEFAULT NULL,
  `billing_cycle` enum('monthly','yearly') DEFAULT 'monthly',
  `next_billing_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `booking_slug` (`booking_slug`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `saas_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `permissions_json` json DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `saas_plans`
--

DROP TABLE IF EXISTS `saas_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saas_plans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `price_monthly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_yearly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_branches` int NOT NULL DEFAULT '1',
  `max_users` int NOT NULL DEFAULT '5',
  `max_menu_items` int NOT NULL DEFAULT '100',
  `max_tables` int NOT NULL DEFAULT '20',
  `allow_api_access` tinyint(1) DEFAULT '0',
  `allow_whitelabel` tinyint(1) DEFAULT '0',
  `allow_custom_domain` tinyint(1) DEFAULT '0',
  `allow_thermal_print` tinyint(1) DEFAULT '1',
  `allow_kot_print` tinyint(1) DEFAULT '1',
  `allow_online_ordering` tinyint(1) DEFAULT '0',
  `allow_loyalty` tinyint(1) DEFAULT '0',
  `allow_reports_advanced` tinyint(1) DEFAULT '0',
  `features_json` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned DEFAULT NULL,
  `branch_id` int unsigned DEFAULT NULL,
  `key` varchar(150) NOT NULL,
  `value` text,
  `type` enum('text','json','boolean','number') DEFAULT 'text',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`restaurant_id`,`branch_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shifts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `opening_cash` decimal(10,2) NOT NULL DEFAULT '0.00',
  `opening_denominations` json DEFAULT NULL,
  `cash_sales` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_sales` decimal(10,2) NOT NULL DEFAULT '0.00',
  `expected_cash` decimal(10,2) NOT NULL DEFAULT '0.00',
  `closing_denominations` json DEFAULT NULL,
  `counted_cash` decimal(10,2) NOT NULL DEFAULT '0.00',
  `difference` decimal(10,2) NOT NULL DEFAULT '0.00',
  `orders_count` int NOT NULL DEFAULT '0',
  `status` enum('open','closed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open',
  `notes` text COLLATE utf8mb4_general_ci,
  `opened_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subscription_payments`
--

DROP TABLE IF EXISTS `subscription_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `plan_id` int unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `billing_cycle` enum('monthly','yearly') NOT NULL DEFAULT 'monthly',
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `idx_restaurant` (`restaurant_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `subscription_payments_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `saas_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `table_areas`
--

DROP TABLE IF EXISTS `table_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_areas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `table_areas_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `table_bookings`
--

DROP TABLE IF EXISTS `table_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `table_bookings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `table_id` int unsigned NOT NULL,
  `guest_name` varchar(150) NOT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `guests` int DEFAULT '2',
  `booking_datetime` datetime DEFAULT NULL,
  `notes` text,
  `status` enum('confirmed','seated','cancelled','no_show') DEFAULT 'confirmed',
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `cancelled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `table_id` (`table_id`),
  KEY `status` (`status`),
  KEY `booking_datetime` (`booking_datetime`),
  CONSTRAINT `tb_fk_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tb_fk_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int unsigned NOT NULL,
  `area_id` int unsigned DEFAULT NULL,
  `table_number` varchar(20) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL,
  `booked_name` varchar(100) DEFAULT NULL,
  `booked_phone` varchar(20) DEFAULT NULL,
  `booked_for` datetime DEFAULT NULL,
  `booked_note` text,
  `capacity` int DEFAULT '4',
  `shape` enum('square','round','rectangle') DEFAULT 'square',
  `status` enum('available','occupied','reserved','cleaning','inactive','booked') NOT NULL DEFAULT 'available',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_token` (`qr_token`),
  KEY `branch_id` (`branch_id`),
  KEY `area_id` (`area_id`),
  CONSTRAINT `tables_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tables_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `table_areas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trial_leads`
--

DROP TABLE IF EXISTS `trial_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trial_leads` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `restaurant_name` varchar(180) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branches` int unsigned NOT NULL DEFAULT '1',
  `plan_id` int unsigned DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `source` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'website',
  `status` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'new',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_branches`
--

DROP TABLE IF EXISTS `user_branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_branches` (
  `user_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`branch_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `user_branches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_branches_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned DEFAULT NULL,
  `branch_id` int unsigned DEFAULT NULL,
  `role_id` int unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `pin` varchar(10) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT '0',
  `two_factor_secret` varchar(100) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified_at` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `branch_id` (`branch_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `waste_logs`
--

DROP TABLE IF EXISTS `waste_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `waste_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` int unsigned NOT NULL,
  `branch_id` int unsigned NOT NULL,
  `logged_by` int unsigned DEFAULT NULL,
  `item_name` varchar(200) NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `unit` varchar(30) DEFAULT 'portion',
  `cost` decimal(8,2) DEFAULT '0.00',
  `reason` enum('spoiled','over_cooked','wrong_order','prep_error','expired','other') DEFAULT 'other',
  `notes` text,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-25 19:22:19
