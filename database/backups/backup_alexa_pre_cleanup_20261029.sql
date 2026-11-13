-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: alexa
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (10,1,'Test User','087783200822','Jl. Setia Budi No 2','Jakarta Pusat','DKI Jakarta','10110','2026-10-08 05:00:57','2026-10-08 05:00:57'),(11,1,'Test User Order 2','087783200822','Jl. Setia Budi No 2','Jakarta Pusat','DKI Jakarta','10110','2026-10-08 17:30:46','2026-10-08 17:30:46'),(12,1,'Test User Order 2','087783200822','Jl. Setia Budi No 2','Jakarta Pusat','DKI Jakarta','10110','2026-10-13 17:29:14','2026-10-13 17:29:14');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (1,1,'2026-10-03 06:10:28','2026-10-03 06:10:28');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collections_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collections`
--

LOCK TABLES `collections` WRITE;
/*!40000 ALTER TABLE `collections` DISABLE KEYS */;
INSERT INTO `collections` VALUES (1,'Alexa Core','alexa-core','Everyday essentials defined by simplicity, refined proportions, and understated character.','2026-09-21 23:04:07','2026-09-21 23:04:07'),(2,'Alexa Active','alexa-active','Functional hydration designed for movement, active routines, and life on the go.','2026-09-21 23:04:07','2026-09-21 23:04:07'),(3,'Alexa Trek','alexa-trek','Durable everyday hydration designed for longer days, travel, and outdoor journeys.','2026-09-21 23:04:07','2026-09-21 23:04:07');
/*!40000 ALTER TABLE `collections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_22_000001_create_collections_table',1),(5,'2026_09_22_000002_create_products_table',1),(6,'2026_09_22_000003_create_product_variants_table',1),(7,'2026_10_03_000001_create_carts_table',2),(8,'2026_10_03_000002_create_cart_items_table',2),(9,'2026_10_03_000003_create_addresses_table',3),(10,'2026_10_03_000004_create_orders_table',3),(11,'2026_10_03_000005_create_order_items_table',3),(12,'2026_10_04_000001_create_payments_table',4),(13,'2026_10_04_000002_add_is_admin_to_users_table',5),(14,'2026_10_04_000003_create_shipments_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `subtotal` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (11,10,1,'Alexa Core','Midnight','ALX-CORE-MID',899000,1,899000,'2026-10-08 05:00:57','2026-10-08 05:00:57'),(12,11,6,'Alexa Active','Eclipse','ALX-ACT-ECL',1099000,1,1099000,'2026-10-08 17:30:46','2026-10-08 17:30:46'),(13,12,1,'Alexa Core','Midnight','ALX-CORE-MID',899000,1,899000,'2026-10-13 17:29:14','2026-10-13 17:29:14');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address_id` bigint unsigned NOT NULL,
  `subtotal` bigint unsigned NOT NULL,
  `shipping_cost` bigint unsigned NOT NULL DEFAULT '0',
  `total` bigint unsigned NOT NULL,
  `order_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_shipping_address_id_foreign` (`shipping_address_id`),
  CONSTRAINT `orders_shipping_address_id_foreign` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (10,1,'ALX-20261008-XXSN17',10,899000,0,899000,'pending','2026-10-08 05:00:57','2026-10-08 05:00:57'),(11,1,'ALX-20261009-GRRGZM',11,1099000,0,1099000,'processing','2026-10-08 17:30:46','2026-10-08 17:31:11'),(12,1,'ALX-20261014-B0IZ0O',12,899000,0,899000,'processing','2026-10-13 17:29:14','2026-10-13 17:32:18');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `payment_gateway` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'midtrans',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_redirect_url` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  KEY `payments_transaction_id_index` (`transaction_id`),
  KEY `payments_status_index` (`status`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (6,10,'midtrans',NULL,NULL,899000,'pending','19693f6e-b5e2-405f-9fcf-d765b5d85461','https://app.sandbox.midtrans.com/snap/v4/redirection/19693f6e-b5e2-405f-9fcf-d765b5d85461',NULL,NULL,'2026-10-08 05:02:21','2026-10-08 05:02:23'),(7,11,'midtrans','credit_card','bfc0c3fc-0f3a-4bd2-8cba-4bfb08f1c436',1099000,'paid','71dc1810-fe29-43a1-a6bf-4ca1299ca21d','https://app.sandbox.midtrans.com/snap/v4/redirection/71dc1810-fe29-43a1-a6bf-4ca1299ca21d','2026-10-08 17:31:11','{\"bank\": \"cimb\", \"currency\": \"IDR\", \"metadata\": [], \"order_id\": \"ALX-20261009-GRRGZM\", \"card_type\": \"credit\", \"expiry_time\": \"2026-10-17 07:31:07\", \"masked_card\": \"48111111-1114\", \"merchant_id\": \"M487405056\", \"status_code\": \"200\", \"fraud_status\": \"accept\", \"gross_amount\": \"1099000.00\", \"payment_type\": \"credit_card\", \"approval_code\": \"1791505868014\", \"signature_key\": \"ea6668f2aac03dee1f0cb70d217c43b12c09f7dceaf5b76414bc58a1b74ea4ced0b7569c979feb988dc4d685a34d48d1cb2f47260026929db5dcb9d2b546c799\", \"status_message\": \"midtrans payment notification\", \"transaction_id\": \"bfc0c3fc-0f3a-4bd2-8cba-4bfb08f1c436\", \"customer_details\": {\"email\": \"test@example.com\", \"phone\": \"+6287783200822\", \"full_name\": \"Test User Order 2\"}, \"transaction_time\": \"2026-10-09 07:31:07\", \"transaction_status\": \"capture\", \"channel_response_code\": \"00\", \"channel_response_message\": \"Approved\"}','2026-10-08 17:30:57','2026-10-08 17:31:11'),(8,12,'midtrans','credit_card','8a75edb5-2d47-4ad8-9ea0-67e98ec76060',899000,'paid','f48da3f0-8888-47a0-b0a7-d534b6b78afc','https://app.sandbox.midtrans.com/snap/v4/redirection/f48da3f0-8888-47a0-b0a7-d534b6b78afc','2026-10-13 17:32:18','{\"bank\": \"bni\", \"currency\": \"IDR\", \"metadata\": [], \"order_id\": \"ALX-20261014-B0IZ0O\", \"card_type\": \"credit\", \"expiry_time\": \"2026-10-22 07:32:14\", \"masked_card\": \"48111111-1114\", \"merchant_id\": \"M487405056\", \"status_code\": \"200\", \"fraud_status\": \"accept\", \"gross_amount\": \"899000.00\", \"payment_type\": \"credit_card\", \"approval_code\": \"1791937935431\", \"signature_key\": \"ad86dd28e80cdf37d860499d3571b68f8533263c0f23c07d9699ce5ccdfd76e4229e3c62da783d760abc624d7f77bf5db7c856dd19e09350ebe6f4a2119abb8d\", \"status_message\": \"midtrans payment notification\", \"transaction_id\": \"8a75edb5-2d47-4ad8-9ea0-67e98ec76060\", \"customer_details\": {\"email\": \"test@example.com\", \"phone\": \"+6287783200822\", \"full_name\": \"Test User Order 2\"}, \"transaction_time\": \"2026-10-14 07:32:14\", \"transaction_status\": \"capture\", \"channel_response_code\": \"00\", \"channel_response_message\": \"Approved\"}','2026-10-13 17:30:37','2026-10-13 17:32:18');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_hex` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` bigint unsigned NOT NULL,
  `stock` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,1,'Core Midnight','Midnight','#1C1C1C','ALX-CORE-MID',899000,18,'active','images/products/core-midnight.png','2026-09-21 23:04:07','2026-10-13 17:29:14'),(2,1,'Core Sage','Sage','#A8B5A2','ALX-CORE-SAG',899000,20,'active','images/products/core-sage.png','2026-09-21 23:04:07','2026-10-03 17:06:19'),(3,1,'Core Ivory','Ivory','#F2EEE6','ALX-CORE-IVY',899000,20,'active','images/products/core-ivory.png','2026-09-21 23:04:07','2026-09-21 23:04:07'),(4,1,'Core Lavender','Lavender','#C8BDD9','ALX-CORE-LAV',899000,20,'active','images/products/core-lavender.png','2026-09-21 23:04:07','2026-09-21 23:04:07'),(5,2,'Active Snow','Snow','#F5F5F3','ALX-ACT-SNW',1099000,20,'active','images/products/active-snow.png','2026-09-21 23:04:07','2026-10-03 18:02:02'),(6,2,'Active Eclipse','Eclipse','#292929','ALX-ACT-ECL',1099000,19,'active','images/products/active-eclipse.png','2026-09-21 23:04:07','2026-10-08 17:30:46'),(7,2,'Active Sky','Sky','#AFC7D8','ALX-ACT-SKY',1099000,20,'active','images/products/active-sky.png','2026-09-21 23:04:07','2026-09-21 23:04:07'),(8,2,'Active Blush','Blush','#DDB8B5','ALX-ACT-BLS',1099000,20,'active','images/products/active-blush.png','2026-09-21 23:04:07','2026-09-21 23:04:07'),(9,3,'Trek Midnight','Midnight','#1C1C1C','ALX-TRK-MID',1299000,20,'active','images/products/trek-midnight.png','2026-09-21 23:04:07','2026-09-21 23:04:07'),(10,3,'Trek Steel','Steel','#8C9296','ALX-TRK-STL',1299000,20,'active','images/products/trek-steel.png','2026-09-21 23:04:07','2026-09-21 23:04:07'),(11,3,'Trek Sand','Sand','#C8B89E','ALX-TRK-SND',1299000,20,'active','images/products/trek-sand.png','2026-09-21 23:04:07','2026-09-21 23:04:07'),(12,3,'Trek Onyx','Onyx','#111111','ALX-TRK-ONX',1299000,20,'active','images/products/trek-onyx.png','2026-09-21 23:04:07','2026-09-21 23:04:07');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `collection_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lid_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_collection_id_foreign` (`collection_id`),
  CONSTRAINT `products_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Alexa Core','alexa-core','Alexa Core is designed around simplicity. Its clean silhouette, considered proportions, and insulated stainless steel body make it a refined companion for everyday routines. From the first coffee of the morning to a long afternoon at your desk, Core keeps hydration within reach without compromising your sense of style.','500 ml','18/8 stainless steel','Screw cap','±280g','active','2026-09-21 23:04:07','2026-09-21 23:04:07'),(2,2,'Alexa Active','alexa-active','Alexa Active brings function into a refined form. Designed for movement, its sport spout and integrated carry handle make hydration convenient whether you\'re heading to the gym, commuting across the city, or spending the day outdoors.','600 ml','18/8 stainless steel','Sport spout with integrated carry handle','±320g','active','2026-09-21 23:04:07','2026-09-21 23:04:07'),(3,3,'Alexa Trek','alexa-trek','Alexa Trek is designed for days that take you further. Its larger capacity, wide-mouth design, and integrated carry handle make it suited to travel and outdoor use while maintaining the refined character of the Alexa collection.','750 ml','18/8 stainless steel','Wide-mouth with integrated carry handle','±390g','active','2026-09-21 23:04:07','2026-09-21 23:04:07');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('9I5uwSg5HGnLxaTPHBkZ2q91wy4tOORbyJqtyb6d',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ1T0kyc2RPdVRuaFNLZUh1dW9uU29PS0xqUU1pRE52TXR4aHhodmZ2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2FsZXhhLnRlc3RcL3Byb2R1Y3RzXC9hbGV4YS1jb3JlIiwicm91dGUiOiJwcm9kdWN0cy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1791936800),('B0zOzcHbRZbOkdE0cGo9rDcEiGGMSlFFw0zMky12',NULL,'127.0.0.1','Veritrans','eyJfdG9rZW4iOiJ5S3A0dVFuUm95eVVyOEdWeTExWDJBSkY5eTFudkdTUUUydU5sR3B4IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1791937938),('ncWXpzlUQbhSR6DqFwMCxiWCDPpe8Abda8phddUJ',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ6MkxKUW4yeFNJc2Q2UklMZXpvNVBqeE12ZnFrT0xidUhiemRvdDFxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9',1792072605),('ZExcuXQbGWLjqC8YIoS9EkhFzEdPqvYWIHFc0fQ1',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJnaTFsNVN0NGlEanBqcmhRcDZYMlpvRzU2M001OHdYRkdNU1ZCTWdiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9vcmRlcnMiLCJyb3V0ZSI6Im9yZGVycy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==',1791938108);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'biteship',
  `biteship_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biteship_tracking_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waybill_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `courier_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'jne',
  `courier_service` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reg',
  `courier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `tracking_url` text COLLATE utf8mb4_unicode_ci,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `history` json DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shipments_order_id_foreign` (`order_id`),
  KEY `shipments_biteship_order_id_index` (`biteship_order_id`),
  KEY `shipments_biteship_tracking_id_index` (`biteship_tracking_id`),
  KEY `shipments_waybill_id_index` (`waybill_id`),
  KEY `shipments_status_index` (`status`),
  CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipments`
--

LOCK TABLES `shipments` WRITE;
/*!40000 ALTER TABLE `shipments` DISABLE KEYS */;
INSERT INTO `shipments` VALUES (2,11,'biteship','6ac8364c67377126fc3eb72e',NULL,NULL,'jne','reg','JNE','pending',NULL,NULL,NULL,NULL,'{\"id\": \"6ac8364c67377126fc3eb72e\", \"note\": \"Order ALX-20261009-GRRGZM\", \"tags\": [], \"extra\": [], \"items\": [{\"sku\": null, \"name\": \"Alexa Active - Eclipse\", \"value\": 1099000, \"width\": 1, \"height\": 1, \"length\": 1, \"weight\": 320, \"category\": \"others\", \"quantity\": 1, \"description\": \"Insulated bottle SKU: ALX-ACT-ECL\"}], \"price\": 10000, \"object\": \"order\", \"origin\": {\"note\": \"Alexa Fulfillment Warehouse Gate 3\", \"address\": \"Jl. Jenderal Sudirman Kav. 52-53, SCBD\", \"coordinate\": {\"latitude\": null, \"longitude\": null}, \"postal_code\": 12190, \"contact_name\": \"Alexa Fulfillment Center\", \"contact_phone\": \"081299887766\", \"collection_method\": \"pickup\", \"administrative_division_level_1_name\": \"DKI Jakarta\", \"administrative_division_level_2_name\": \"Jakarta Selatan\", \"administrative_division_level_3_name\": \"Kebayoran Baru\", \"administrative_division_level_4_name\": \"Senayan\"}, \"status\": \"placed\", \"courier\": {\"link\": null, \"name\": null, \"type\": \"reg\", \"phone\": null, \"company\": \"jne\", \"history\": [], \"insurance\": {\"fee\": 0, \"note\": null, \"amount\": 0, \"fee_currency\": \"IDR\", \"amount_currency\": \"IDR\"}, \"waybill_id\": null, \"driver_name\": null, \"tracking_id\": null, \"driver_phone\": null, \"routing_code\": null, \"shipment_fee\": 10000, \"driver_photo_url\": null, \"driver_plate_number\": null}, \"message\": \"Order successfully retrieved\", \"shipper\": {\"name\": \"Alexa Fulfillment Center\", \"email\": \"fulfillment@alexa.store\", \"phone\": \"081299887766\", \"organization\": \"Alexa Lifestyle Indonesia\"}, \"success\": true, \"voucher\": {\"id\": null, \"name\": null, \"type\": null, \"value\": null}, \"currency\": \"IDR\", \"delivery\": {\"note\": null, \"type\": \"later\", \"datetime\": \"2026-10-09T09:00+07:00\", \"distance\": null, \"distance_unit\": \"kilometer\"}, \"short_id\": \"J-Uvz76GU\", \"return_id\": null, \"tax_lines\": [], \"invoice_id\": null, \"destination\": {\"note\": \"Handle with care, premium insulated tumbler\", \"address\": \"Jl. Setia Budi No 2, Jakarta Pusat, DKI Jakarta\", \"coordinate\": {\"latitude\": null, \"longitude\": null}, \"postal_code\": 10110, \"contact_name\": \"Test User Order 2\", \"contact_email\": \"test@example.com\", \"contact_phone\": \"087783200822\", \"cash_on_delivery\": {\"id\": null, \"fee\": 0, \"note\": null, \"type\": null, \"amount\": 0, \"status\": null, \"feeCurrency\": \"IDR\", \"amountCurrency\": \"IDR\", \"payment_method\": \"cash\", \"payment_status\": \"pending\"}, \"proof_of_delivery\": {\"fee\": 0, \"use\": false, \"link\": null, \"note\": null}, \"administrative_division_level_1_name\": \"DKI Jakarta\", \"administrative_division_level_2_name\": \"Jakarta Pusat\", \"administrative_division_level_3_name\": \"Gambir\", \"administrative_division_level_4_name\": \"Gambir\"}, \"reference_id\": null, \"ticket_status\": null, \"draft_order_id\": null}','2026-10-08 17:33:18','2026-10-08 17:38:44'),(3,12,'biteship','6acecdea2f370608ad69cd5e',NULL,NULL,'jne','reg','JNE','pending',NULL,NULL,NULL,NULL,'{\"id\": \"6acecdea2f370608ad69cd5e\", \"note\": \"Order ALX-20261014-B0IZ0O\", \"tags\": [], \"extra\": [], \"items\": [{\"sku\": null, \"name\": \"Alexa Core - Midnight\", \"value\": 899000, \"width\": 1, \"height\": 1, \"length\": 1, \"weight\": 280, \"category\": \"others\", \"quantity\": 1, \"description\": \"Insulated bottle SKU: ALX-CORE-MID\"}], \"price\": 10000, \"object\": \"order\", \"origin\": {\"note\": \"Alexa Fulfillment Warehouse Gate 3\", \"address\": \"Jl. Jenderal Sudirman Kav. 52-53, SCBD\", \"coordinate\": {\"latitude\": null, \"longitude\": null}, \"postal_code\": 12190, \"contact_name\": \"Alexa Fulfillment Center\", \"contact_phone\": \"081299887766\", \"collection_method\": \"pickup\", \"administrative_division_level_1_name\": \"DKI Jakarta\", \"administrative_division_level_2_name\": \"Jakarta Selatan\", \"administrative_division_level_3_name\": \"Kebayoran Baru\", \"administrative_division_level_4_name\": \"Senayan\"}, \"status\": \"placed\", \"courier\": {\"link\": null, \"name\": null, \"type\": \"reg\", \"phone\": null, \"company\": \"jne\", \"history\": [], \"insurance\": {\"fee\": 0, \"note\": null, \"amount\": 0, \"fee_currency\": \"IDR\", \"amount_currency\": \"IDR\"}, \"waybill_id\": null, \"driver_name\": null, \"tracking_id\": null, \"driver_phone\": null, \"routing_code\": null, \"shipment_fee\": 10000, \"driver_photo_url\": null, \"driver_plate_number\": null}, \"message\": \"Order successfully retrieved\", \"shipper\": {\"name\": \"Alexa Fulfillment Center\", \"email\": \"fulfillment@alexa.store\", \"phone\": \"081299887766\", \"organization\": \"Alexa Lifestyle Indonesia\"}, \"success\": true, \"voucher\": {\"id\": null, \"name\": null, \"type\": null, \"value\": null}, \"currency\": \"IDR\", \"delivery\": {\"note\": null, \"type\": \"later\", \"datetime\": \"2026-10-14T09:00+07:00\", \"distance\": null, \"distance_unit\": \"kilometer\"}, \"short_id\": \"Hx2juLIa-\", \"return_id\": null, \"tax_lines\": [], \"invoice_id\": null, \"destination\": {\"note\": \"Handle with care, premium insulated tumbler\", \"address\": \"Jl. Setia Budi No 2, Jakarta Pusat, DKI Jakarta\", \"coordinate\": {\"latitude\": null, \"longitude\": null}, \"postal_code\": 10110, \"contact_name\": \"Test User Order 2\", \"contact_email\": \"test@example.com\", \"contact_phone\": \"087783200822\", \"cash_on_delivery\": {\"id\": null, \"fee\": 0, \"note\": null, \"type\": null, \"amount\": 0, \"status\": null, \"feeCurrency\": \"IDR\", \"amountCurrency\": \"IDR\", \"payment_method\": \"cash\", \"payment_status\": \"pending\"}, \"proof_of_delivery\": {\"fee\": 0, \"use\": false, \"link\": null, \"note\": null}, \"administrative_division_level_1_name\": \"DKI Jakarta\", \"administrative_division_level_2_name\": \"Jakarta Pusat\", \"administrative_division_level_3_name\": \"Gambir\", \"administrative_division_level_4_name\": \"Gambir\"}, \"reference_id\": null, \"ticket_status\": null, \"draft_order_id\": null}','2026-10-13 17:33:47','2026-10-13 17:34:59');
/*!40000 ALTER TABLE `shipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@example.com',NULL,'$2y$12$ywik7U.6iQuwHo3we8jVQel6597fPjh2znaV0AHaGB1MYSD4837n6',1,NULL,'2026-10-03 06:08:18','2026-10-03 18:05:08'),(2,'Customer Alexa','customer@alexa.store',NULL,'$2y$12$xr2b0kQIuJGa3Y7TPpJ2Q.chmFjdrSsVUovRnq53vOxJYkrd5Lazi',0,NULL,'2026-10-06 19:11:16','2026-10-06 19:11:16');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-29 21:06:58
