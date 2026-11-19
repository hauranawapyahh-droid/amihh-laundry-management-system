-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: amih_laundry_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `biaya_operasional`
--

DROP TABLE IF EXISTS `biaya_operasional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biaya_operasional` (
  `id_biaya` int(11) NOT NULL AUTO_INCREMENT,
  `id_pencatat` int(11) NOT NULL,
  `nama_biaya` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `jumlah_biaya` decimal(12,2) NOT NULL,
  `tanggal_biaya` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_biaya`),
  KEY `fk_biaya_pencatat` (`id_pencatat`),
  CONSTRAINT `fk_biaya_pencatat` FOREIGN KEY (`id_pencatat`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biaya_operasional`
--

LOCK TABLES `biaya_operasional` WRITE;
/*!40000 ALTER TABLE `biaya_operasional` DISABLE KEYS */;
INSERT INTO `biaya_operasional` VALUES (1,1,'Listrik Bulan September','Utilitas',500000.00,'2026-09-27','Tagihan PLN','2026-09-27 03:20:34'),(2,1,'Biaya Listrik','-',100.00,'2026-09-27',NULL,'2026-09-27 09:07:35');
/*!40000 ALTER TABLE `biaya_operasional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_transaksi`
--

DROP TABLE IF EXISTS `detail_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `id_layanan` int(11) NOT NULL,
  `berat_jumlah` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detail`),
  KEY `fk_detail_transaksi` (`id_transaksi`),
  KEY `fk_detail_layanan` (`id_layanan`),
  CONSTRAINT `fk_detail_layanan` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_transaksi`
--

LOCK TABLES `detail_transaksi` WRITE;
/*!40000 ALTER TABLE `detail_transaksi` DISABLE KEYS */;
INSERT INTO `detail_transaksi` VALUES (1,1,1,2.00,7000.00,14000.00,'2026-09-26 15:08:08'),(2,2,2,2.00,9000.00,18000.00,'2026-09-26 16:03:16'),(3,3,1,3.00,7000.00,21000.00,'2026-09-27 02:30:40'),(4,4,2,2.00,9000.00,18000.00,'2026-09-27 02:40:30'),(5,5,1,2.00,7000.00,14000.00,'2026-10-06 02:02:08'),(6,6,8,3.00,38000.00,114000.00,'2026-10-06 20:20:19'),(7,7,8,3.00,38000.00,114000.00,'2026-10-06 20:32:04'),(8,8,8,4.00,38000.00,152000.00,'2026-10-06 21:57:04'),(9,9,8,3.00,38000.00,114000.00,'2026-10-06 22:19:57'),(10,9,4,1.00,5000.00,5000.00,'2026-10-06 22:19:57'),(11,9,3,2.00,30000.00,60000.00,'2026-10-06 22:19:57'),(12,10,8,1.00,38000.00,38000.00,'2026-10-06 22:21:27'),(13,10,3,3.00,30000.00,90000.00,'2026-10-06 22:21:27'),(14,11,1,5.00,8000.00,40000.00,'2026-10-06 22:59:57'),(16,13,8,3.00,38000.00,114000.00,'2026-10-07 04:24:55'),(17,14,1,2.00,8000.00,16000.00,'2026-10-07 04:39:40'),(18,15,2,7.00,15000.00,105000.00,'2026-10-07 05:11:44'),(19,16,4,2.00,5000.00,10000.00,'2026-10-07 05:27:35'),(20,16,3,1.00,30000.00,30000.00,'2026-10-07 05:27:35'),(21,16,1,2.00,8000.00,16000.00,'2026-10-07 05:27:36'),(22,17,1,2.00,8000.00,16000.00,'2026-10-07 05:34:37'),(23,18,2,1.00,15000.00,15000.00,'2026-10-09 21:39:47'),(24,18,1,1.00,8000.00,8000.00,'2026-10-09 21:39:47'),(25,19,1,2.00,8000.00,16000.00,'2026-10-10 08:11:39'),(27,21,1,3.00,8000.00,24000.00,'2026-10-20 20:15:01'),(28,22,1,2.00,8000.00,16000.00,'2026-10-21 00:01:51'),(29,22,4,1.00,5000.00,5000.00,'2026-10-21 00:01:51'),(30,23,2,3.00,15000.00,45000.00,'2026-10-21 02:30:39'),(31,24,1,3.00,8000.00,24000.00,'2026-10-21 03:14:27'),(32,25,2,2.00,15000.00,30000.00,'2026-10-21 19:02:09'),(33,26,2,2.00,15000.00,30000.00,'2026-10-30 19:10:53'),(34,27,2,2.00,15000.00,30000.00,'2026-10-30 19:12:18'),(35,28,2,2.00,15000.00,30000.00,'2026-10-30 19:18:36'),(36,29,2,2.00,15000.00,30000.00,'2026-11-03 21:32:16'),(37,30,2,2.00,15000.00,30000.00,'2026-11-03 21:56:22'),(38,31,2,2.00,15000.00,30000.00,'2026-11-03 22:13:05'),(39,32,2,2.00,15000.00,30000.00,'2026-11-04 01:38:02'),(40,33,2,2.00,15000.00,30000.00,'2026-11-09 23:07:20'),(41,34,2,2.00,15000.00,30000.00,'2026-11-10 00:06:32'),(42,35,2,2.00,15000.00,30000.00,'2026-11-10 00:11:49'),(43,36,2,2.00,15000.00,30000.00,'2026-11-10 01:01:07'),(44,37,2,2.00,15000.00,30000.00,'2026-11-10 03:15:58'),(45,38,2,2.00,15000.00,30000.00,'2026-11-10 03:25:33'),(46,39,2,2.00,15000.00,30000.00,'2026-11-10 06:18:40'),(47,40,2,2.00,15000.00,30000.00,'2026-11-10 06:35:18'),(48,41,2,2.00,15000.00,30000.00,'2026-11-16 00:52:25'),(49,42,2,2.00,15000.00,30000.00,'2026-11-16 07:50:06');
/*!40000 ALTER TABLE `detail_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `layanan`
--

DROP TABLE IF EXISTS `layanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `layanan` (
  `id_layanan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_layanan` varchar(100) NOT NULL,
  `jenis_satuan` enum('KG','PCS') NOT NULL,
  `harga_per_satuan` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `id_dibuat_oleh` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_layanan`),
  KEY `fk_layanan_pembuat` (`id_dibuat_oleh`),
  CONSTRAINT `fk_layanan_pembuat` FOREIGN KEY (`id_dibuat_oleh`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layanan`
--

LOCK TABLES `layanan` WRITE;
/*!40000 ALTER TABLE `layanan` DISABLE KEYS */;
INSERT INTO `layanan` VALUES (1,'Cuci Regular','KG',8000.00,1,1,'2026-09-26 08:46:27'),(2,'Cuci Express','KG',15000.00,1,1,'2026-09-26 08:46:27'),(3,'Selimut Besar','PCS',30000.00,1,1,'2026-09-26 08:46:27'),(4,'Setrika','KG',5000.00,1,1,'2026-09-26 08:46:27'),(7,'cuci biasa','KG',10.00,0,1,'2026-09-27 09:06:36'),(8,'Set Bed Cover','PCS',38000.00,1,1,'2026-10-06 19:58:57');
/*!40000 ALTER TABLE `layanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2026_10_21_020343_add_bukti_pembayaran_to_pembayaran_table',1),(3,'2026_11_10_070324_add_rejected_status_to_pembayaran_table',2),(4,'2026_11_16_032325_add_metode_pembayaran_to_transaksi_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pelanggan`
--

DROP TABLE IF EXISTS `pelanggan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `no_telepon` varchar(20) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_user` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_pelanggan`),
  UNIQUE KEY `no_telepon` (`no_telepon`),
  UNIQUE KEY `pelanggan_id_user_unique` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pelanggan`
--

LOCK TABLES `pelanggan` WRITE;
/*!40000 ALTER TABLE `pelanggan` DISABLE KEYS */;
INSERT INTO `pelanggan` VALUES (1,'Pelanggan Contoh 1','081234567890','Ciwidey','2026-09-26 08:46:27',NULL),(2,'Pelanggan Contoh 2','081298765432','Panundaan','2026-09-26 08:46:27',NULL),(5,'haura','085794590048','pasirjambu','2026-09-27 09:04:23',8),(6,'amel','+62 815-7364-3842','citiwu','2026-09-27 09:05:44',23),(8,'siti','05795959595','amerika','2026-10-01 19:04:56',24),(9,'dayu','085794590090','citiwu','2026-10-03 22:57:58',25),(10,'arkana','08575757575','pasjam','2026-10-06 22:17:11',26),(11,'rudi','085894593456','citiwu','2026-10-07 03:55:00',7),(12,'desi','085723634280','panundaan','2026-10-07 05:11:23',6),(13,'bayu','0857123653','paskal','2026-10-09 21:37:45',27),(14,'riri','085794590049','citiwu','2026-10-10 08:09:28',28),(19,'Pelanggan Umum','UMUM-MO209WWQ8SHO3NR',NULL,'2026-10-25 23:19:59',NULL),(24,'rina','085794590041','pasjam','2026-11-03 21:31:33',9),(25,'titut','085794590040','pasjam','2026-11-03 21:37:23',11),(26,'Pelanggan Umum','UMUM-1OGKKOUGRHKKU9H',NULL,'2026-11-03 21:44:04',NULL),(27,'amelia','081573643842','citiwu','2026-11-03 21:54:36',29),(28,'amelia','081573643841','citiwu','2026-11-03 22:12:15',30),(29,'amel','08157643841','citiwu','2026-11-03 22:15:07',12),(30,'febi','085700644880','sukawening','2026-11-04 01:37:26',13),(31,'rere','085794590030','pasjam','2026-11-04 02:45:19',22),(32,'amelia','081573643847','citiwu','2026-11-09 22:23:11',31),(33,'Pelanggan Umum','UMUM-VHJRSLBUI1IFS4E',NULL,'2026-11-10 00:06:18',NULL),(34,'lia','085794590056','citiwu','2026-11-16 00:51:07',32);
/*!40000 ALTER TABLE `pelanggan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `metode_pembayaran` enum('TUNAI','TRANSFER_BANK','QRIS') NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kembalian` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_konfirmasi` enum('PENDING','CONFIRMED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `catatan_pelunasan` text DEFAULT NULL,
  `id_konfirmator` int(11) DEFAULT NULL,
  `waktu_konfirmasi` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `fk_pembayaran_transaksi` (`id_transaksi`),
  KEY `fk_pembayaran_konfirmator` (`id_konfirmator`),
  CONSTRAINT `fk_pembayaran_konfirmator` FOREIGN KEY (`id_konfirmator`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pembayaran_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran`
--

LOCK TABLES `pembayaran` WRITE;
/*!40000 ALTER TABLE `pembayaran` DISABLE KEYS */;
INSERT INTO `pembayaran` VALUES (1,4,'TUNAI',20000.00,2000.00,'CONFIRMED','Lunas via tunai',2,'2026-09-27 09:48:18','2026-09-27 02:45:21',NULL),(2,5,'TUNAI',20000.00,6000.00,'CONFIRMED','Pembayaran test Sprint 2',2,'2026-10-06 09:05:42','2026-10-06 02:05:42',NULL),(3,3,'TRANSFER_BANK',50000.00,29000.00,'CONFIRMED',NULL,2,'2026-10-07 03:05:56','2026-10-06 20:05:56',NULL),(4,8,'TUNAI',160000.00,8000.00,'CONFIRMED',NULL,2,'2026-10-07 04:57:04','2026-10-06 21:57:04',NULL),(5,9,'TUNAI',200000.00,21000.00,'CONFIRMED',NULL,2,'2026-10-07 05:19:57','2026-10-06 22:19:57',NULL),(6,10,'TUNAI',150000.00,22000.00,'CONFIRMED',NULL,2,'2026-10-07 05:21:27','2026-10-06 22:21:27',NULL),(7,11,'TUNAI',40000.00,0.00,'CONFIRMED',NULL,2,'2026-10-07 05:59:57','2026-10-06 22:59:57',NULL),(8,13,'TUNAI',115000.00,1000.00,'CONFIRMED',NULL,2,'2026-10-07 11:24:55','2026-10-07 04:24:55',NULL),(9,14,'TUNAI',16000.00,0.00,'CONFIRMED',NULL,2,'2026-10-07 11:39:40','2026-10-07 04:39:40',NULL),(10,15,'TUNAI',110000.00,5000.00,'CONFIRMED',NULL,2,'2026-10-07 12:11:44','2026-10-07 05:11:44',NULL),(11,16,'TUNAI',60000.00,4000.00,'CONFIRMED',NULL,2,'2026-10-07 12:27:36','2026-10-07 05:27:36',NULL),(12,17,'TUNAI',20000.00,4000.00,'CONFIRMED',NULL,2,'2026-10-07 12:34:37','2026-10-07 05:34:37',NULL),(13,18,'TUNAI',25000.00,2000.00,'CONFIRMED',NULL,2,'2026-10-10 04:39:47','2026-10-09 21:39:47',NULL),(14,19,'TUNAI',20000.00,4000.00,'CONFIRMED',NULL,2,'2026-10-10 15:11:39','2026-10-10 08:11:39',NULL),(15,21,'QRIS',24000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-10-20 20:15:01',NULL),(16,22,'QRIS',21000.00,0.00,'CONFIRMED','Bukti pembayaran diverifikasi kasir.',2,'2026-10-21 09:17:26','2026-10-21 00:01:51','bukti-pembayaran/6lHrbJIIfHmAaggH0H0MRUR50odKbpR8MBL4L9Ij.png'),(17,23,'QRIS',45000.00,0.00,'CONFIRMED','Bukti pembayaran diverifikasi kasir.',2,'2026-10-31 01:50:15','2026-10-21 02:30:39','bukti-pembayaran/tR5VNqNkiWZOeCwUJERTXYuf9wUWOWXpV6jMmU8W.png'),(18,24,'TUNAI',24000.00,0.00,'CONFIRMED',NULL,2,'2026-10-21 10:14:27','2026-10-21 03:14:27',NULL),(19,25,'QRIS',30000.00,0.00,'CONFIRMED','Bukti pembayaran diverifikasi kasir.',2,'2026-10-22 02:03:30','2026-10-21 19:02:09','bukti-pembayaran/NyxTvIviOidRUXNow0dVcdnMuuB2XnN2CuFKsM7D.png'),(20,26,'QRIS',30000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-10-30 19:10:53',NULL),(21,27,'QRIS',30000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-10-30 19:12:18',NULL),(22,28,'QRIS',30000.00,0.00,'CONFIRMED','Bukti pembayaran diverifikasi kasir.',2,'2026-10-31 02:24:03','2026-10-30 19:18:36','bukti-pembayaran/CLgROr4zRXwSmiXGQFJr0qK2qUqRfXEzYVsvsMM3.jpg'),(23,29,'QRIS',30000.00,0.00,'CONFIRMED','Bukti pembayaran diverifikasi kasir.',2,'2026-11-04 04:39:13','2026-11-03 21:32:16','bukti-pembayaran/02uirHRPGGmSbfdmzYJyYAjnRir3Clxnxz6uI9O9.jpg'),(24,30,'QRIS',30000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-11-03 21:56:22',NULL),(25,31,'QRIS',30000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-11-03 22:13:05',NULL),(26,32,'QRIS',30000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-11-04 01:38:02',NULL),(27,33,'QRIS',30000.00,0.00,'CONFIRMED','Bukti pembayaran diverifikasi kasir.',2,'2026-11-10 06:52:14','2026-11-09 23:07:20',NULL),(28,34,'QRIS',30000.00,0.00,'CONFIRMED','Pembayaran telah diperiksa dan dikonfirmasi kasir.',2,'2026-11-10 07:17:32','2026-11-10 00:06:32',NULL),(29,35,'QRIS',30000.00,0.00,'CONFIRMED','Pembayaran telah diperiksa dan dikonfirmasi kasir.',2,'2026-11-10 07:18:51','2026-11-10 00:11:49','bukti-pembayaran/ItIYrfF4M5mUQGq5FMaPrDAyksWpjU2SplCef48Y.jpg'),(30,36,'QRIS',30000.00,0.00,'CONFIRMED','Pembayaran telah diperiksa dan dikonfirmasi kasir.',2,'2026-11-10 08:06:48','2026-11-10 01:01:07','bukti-pembayaran/cIIz8Enov0Ftlz9mBwDyV7VcSvUw7lgzKiGWh4Oc.jpg'),(31,37,'TUNAI',30000.00,0.00,'CONFIRMED',NULL,2,'2026-11-10 10:15:58','2026-11-10 03:15:58',NULL),(32,38,'QRIS',30000.00,0.00,'CONFIRMED','Pembayaran telah diperiksa dan dikonfirmasi kasir.',2,'2026-11-10 10:28:23','2026-11-10 03:25:33','bukti-pembayaran/r4JNtYdZw6GqPQdDhs194LMmzXGmHUEMHFJxxdbl.jpg'),(33,39,'QRIS',30000.00,0.00,'CONFIRMED','Pembayaran telah diperiksa dan dikonfirmasi kasir.',2,'2026-11-10 13:25:38','2026-11-10 06:18:40','bukti-pembayaran/pSyE9EQcVeCRx5TLlEggEYg3xRKUcinVUwj39pPq.jpg'),(34,40,'QRIS',30000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-11-10 06:35:18',NULL),(35,41,'QRIS',30000.00,0.00,'CONFIRMED','Pembayaran telah diperiksa dan dikonfirmasi kasir.',2,'2026-11-16 07:54:40','2026-11-16 00:52:25','bukti-pembayaran/FmTS1ZSTqau5AeKSHVkWK8kzVY7GQcH0qWjoWJKd.jpg'),(36,42,'QRIS',30000.00,0.00,'PENDING',NULL,NULL,NULL,'2026-11-16 07:50:06',NULL);
/*!40000 ALTER TABLE `pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proses_cucian`
--

DROP TABLE IF EXISTS `proses_cucian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proses_cucian` (
  `id_proses` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `status_proses` enum('DITERIMA','DICUCI','DIKERINGKAN','DISETRIKA','SELESAI') NOT NULL DEFAULT 'DITERIMA',
  `catatan_proses` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_proses`),
  KEY `idx_proses_transaksi` (`id_transaksi`),
  KEY `idx_proses_staff` (`id_staff`),
  CONSTRAINT `fk_proses_staff` FOREIGN KEY (`id_staff`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE,
  CONSTRAINT `fk_proses_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proses_cucian`
--

LOCK TABLES `proses_cucian` WRITE;
/*!40000 ALTER TABLE `proses_cucian` DISABLE KEYS */;
INSERT INTO `proses_cucian` VALUES (1,3,3,'DITERIMA','Cucian diterima dari pelanggan','2026-09-27 03:04:27'),(2,3,3,'DICUCI',NULL,'2026-09-27 03:04:32'),(3,5,3,'DITERIMA','Cucian diterima dan siap diproses','2026-10-06 02:07:49'),(4,5,3,'DICUCI','Cucian sedang dicuci','2026-10-06 02:09:48'),(5,5,3,'DIKERINGKAN','Cucian sedang dikeringkan','2026-10-06 02:11:01'),(6,5,3,'DISETRIKA','Cucian sedang disetrika','2026-10-06 02:14:04'),(7,5,3,'SELESAI','Cucian selesai diproses','2026-10-06 02:15:37'),(8,3,3,'DIKERINGKAN',NULL,'2026-10-06 22:57:14'),(9,3,3,'DISETRIKA',NULL,'2026-10-06 22:59:04'),(10,3,3,'SELESAI',NULL,'2026-10-06 22:59:09'),(11,11,3,'DICUCI',NULL,'2026-10-07 04:21:57'),(12,10,3,'DICUCI',NULL,'2026-10-07 04:22:02'),(13,9,3,'DICUCI',NULL,'2026-10-07 04:22:06'),(14,8,3,'DICUCI',NULL,'2026-10-07 04:22:11'),(15,11,3,'DIKERINGKAN',NULL,'2026-10-07 04:23:30'),(16,10,3,'DIKERINGKAN',NULL,'2026-10-07 04:23:48'),(17,10,3,'DISETRIKA',NULL,'2026-10-07 04:23:51'),(18,10,3,'SELESAI',NULL,'2026-10-07 04:23:57'),(19,16,3,'DICUCI',NULL,'2026-10-07 05:28:50'),(20,16,3,'DIKERINGKAN',NULL,'2026-10-07 05:30:07'),(21,16,3,'DISETRIKA',NULL,'2026-10-07 05:30:25'),(22,16,3,'SELESAI',NULL,'2026-10-07 05:30:41'),(23,18,3,'DICUCI',NULL,'2026-10-09 21:40:28'),(24,18,3,'DIKERINGKAN',NULL,'2026-10-10 07:31:15'),(25,18,3,'DISETRIKA',NULL,'2026-10-10 07:31:25'),(26,18,3,'SELESAI',NULL,'2026-10-10 07:31:29'),(27,28,3,'DICUCI',NULL,'2026-10-30 19:25:16'),(28,28,3,'DIKERINGKAN',NULL,'2026-10-30 19:25:40'),(29,29,3,'DICUCI',NULL,'2026-11-03 21:39:31'),(30,29,3,'DIKERINGKAN',NULL,'2026-11-03 21:39:35'),(31,29,3,'DISETRIKA',NULL,'2026-11-03 21:39:38'),(32,32,3,'DICUCI',NULL,'2026-11-04 02:49:23'),(33,29,3,'SELESAI',NULL,'2026-11-04 02:49:28'),(34,32,3,'DIKERINGKAN',NULL,'2026-11-07 22:34:18'),(35,32,3,'SELESAI',NULL,'2026-11-07 22:46:57'),(36,31,3,'DICUCI',NULL,'2026-11-09 22:10:59'),(37,31,3,'SELESAI',NULL,'2026-11-09 22:11:06'),(38,30,3,'SELESAI',NULL,'2026-11-09 22:11:14'),(39,28,3,'SELESAI',NULL,'2026-11-09 22:11:23'),(40,36,3,'DICUCI',NULL,'2026-11-10 01:02:36'),(41,37,3,'DICUCI',NULL,'2026-11-10 03:19:22'),(42,37,3,'DISETRIKA',NULL,'2026-11-10 03:19:33'),(43,38,3,'DISETRIKA',NULL,'2026-11-10 03:29:47'),(44,38,3,'SELESAI',NULL,'2026-11-10 03:31:56'),(45,39,3,'DISETRIKA',NULL,'2026-11-10 06:27:29');
/*!40000 ALTER TABLE `proses_cucian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(30) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_kasir` int(11) NOT NULL,
  `id_verifikator` int(11) DEFAULT NULL,
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_pembayaran` enum('BELUM_DIBAYAR','LUNAS') NOT NULL DEFAULT 'BELUM_DIBAYAR',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status_pengambilan` enum('BELUM_DIAMBIL','SUDAH_DIAMBIL') NOT NULL DEFAULT 'BELUM_DIAMBIL',
  `waktu_pengambilan` datetime DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_transaksi`),
  UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  KEY `idx_transaksi_pelanggan` (`id_pelanggan`),
  KEY `idx_transaksi_kasir` (`id_kasir`),
  KEY `idx_transaksi_verifikator` (`id_verifikator`),
  CONSTRAINT `fk_transaksi_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_verifikator` FOREIGN KEY (`id_verifikator`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (1,'TRX1790435288442',1,1,NULL,14000.00,'',NULL,'BELUM_DIAMBIL',NULL,'Laundry cepat','2026-09-26 15:08:08','2026-09-26 15:08:08'),(2,'TRX1790438596005',1,1,NULL,18000.00,'',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-09-26 16:03:16','2026-09-26 16:03:16'),(3,'TRX1790476240277',1,1,2,21000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'testing','2026-09-27 02:30:40','2026-10-06 20:05:56'),(4,'TRX1790476830803',2,2,NULL,18000.00,'LUNAS',NULL,'SUDAH_DIAMBIL','2026-09-27 09:53:44','test kasir','2026-09-27 02:40:30','2026-09-27 02:53:44'),(5,'TRX-20261006090208-RO8Y',1,2,2,14000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'Test Sprint 2','2026-10-06 02:02:08','2026-10-06 02:05:42'),(6,'TRX-20261007032019-HRAH',6,2,NULL,114000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-06 20:20:19','2026-10-06 20:20:19'),(7,'TRX-20261007033204-CS8U',6,2,NULL,114000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-06 20:32:04','2026-10-06 20:32:04'),(8,'TRX-20261007045704-PFVX',9,2,2,152000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-06 21:57:04','2026-10-06 21:57:04'),(9,'TRX-20261007051957-DDBK',10,2,2,179000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-06 22:19:57','2026-10-06 22:19:57'),(10,'TRX-20261007052127-VRDD',5,2,2,128000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-06 22:21:27','2026-10-06 22:21:27'),(11,'TRX-20261007055957-P5J8',8,2,2,40000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-06 22:59:57','2026-10-06 22:59:57'),(13,'TRX-20261007112455-852Z',11,2,2,114000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-07 04:24:55','2026-10-07 04:24:55'),(14,'TRX-20261007113940-9BF1',1,2,2,16000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-07 04:39:40','2026-10-07 04:39:40'),(15,'TRX-20261007121144-5BEJ',12,2,2,105000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-07 05:11:44','2026-10-07 05:11:44'),(16,'TRX-20261007122735-RC1I',6,2,2,56000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-07 05:27:35','2026-10-07 05:27:36'),(17,'TRX-20261007123437-D47S',12,2,2,16000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'Demo Sprint 2','2026-10-07 05:34:37','2026-10-07 05:34:37'),(18,'TRX-20261010043947-GOSM',13,2,2,23000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-09 21:39:47','2026-10-09 21:39:47'),(19,'TRX-20261010151139-QLRV',14,2,2,16000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'pakaian putihnya di pisahkan','2026-10-10 08:11:39','2026-10-10 08:11:39'),(21,'TRX-20261021031501-V6JG',6,2,NULL,24000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-20 20:15:01','2026-10-20 20:15:01'),(22,'TRX-20261021070151-DZVF',12,2,2,21000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-21 00:01:51','2026-10-21 02:17:26'),(23,'TRX-20261021093039-9PVM',5,2,2,45000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-21 02:30:39','2026-10-30 18:50:15'),(24,'TRX-20261021101427-32UF',5,2,2,24000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-21 03:14:27','2026-10-21 03:14:27'),(25,'TRX-20261022020209-FZ9S',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-10-21 19:02:09','2026-10-21 19:03:30'),(26,'TRX-20261031021053-UON6',6,2,NULL,30000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,'baju putih di pisahkan','2026-10-30 19:10:53','2026-10-30 19:10:53'),(27,'TRX-20261031021218-KELQ',6,2,NULL,30000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,'baju putih dipisahkan','2026-10-30 19:12:18','2026-10-30 19:12:18'),(28,'TRX-20261031021836-Q73V',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'baju putih dipisahkan','2026-10-30 19:18:36','2026-10-30 19:24:03'),(29,'TRX-20261104043216-LBUP',24,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'pisahkan baju putih','2026-11-03 21:32:16','2026-11-03 21:39:13'),(30,'TRX-20261104045622-E8FC',27,2,NULL,30000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,'baju putih harus di pisah','2026-11-03 21:56:22','2026-11-03 21:56:22'),(31,'TRX-20261104051305-LVN3',28,2,NULL,30000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-11-03 22:13:05','2026-11-03 22:13:05'),(32,'TRX-20261104083802-9P6I',30,2,NULL,30000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,'baju putih di pisahkan','2026-11-04 01:38:02','2026-11-04 01:38:02'),(33,'TRX-20261110060720-VUEN',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'baju putih dipisahkan','2026-11-09 23:07:20','2026-11-09 23:52:14'),(34,'TRX-20261110070632-INC8',33,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-11-10 00:06:32','2026-11-10 00:17:32'),(35,'TRX-20261110071149-G7E4',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-11-10 00:11:49','2026-11-10 00:18:51'),(36,'TRX-20261110080107-AW95',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'baju putih dipisahkan','2026-11-10 01:01:07','2026-11-10 01:06:48'),(37,'TRX-20261110101558-HADQ',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'pisahkan baju putih','2026-11-10 03:15:58','2026-11-10 03:15:58'),(38,'TRX-20261110102533-TK68',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-11-10 03:25:33','2026-11-10 03:28:23'),(39,'TRX-20261110131840-LJ45',5,2,2,30000.00,'LUNAS',NULL,'BELUM_DIAMBIL',NULL,'pisahkan baju putih','2026-11-10 06:18:40','2026-11-10 06:25:38'),(40,'TRX-20261110133518-9RBE',14,2,NULL,30000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,'baju putih dipisahkan','2026-11-10 06:35:18','2026-11-10 06:35:18'),(41,'TRX-20261116075225-SCER',34,2,2,30000.00,'LUNAS','QRIS','BELUM_DIAMBIL',NULL,'baju putih dipisahkan','2026-11-16 00:52:25','2026-11-16 00:54:40'),(42,'TRX-20261116145006-5QVA',34,2,NULL,30000.00,'BELUM_DIBAYAR',NULL,'BELUM_DIAMBIL',NULL,NULL,'2026-11-16 07:50:06','2026-11-16 07:50:06');
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('ADMIN','KASIR','STAFF_LAUNDRY','OWNER','CUSTOMER') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin','$2y$10$L5mGsKS7fLg6ZyC1Gn/Z5Ocn.4hTX9BBbzR5Fi7AQzWYGOvAOi9D2','ADMIN',1,'2026-09-26 08:46:27'),(2,'Kasir Amih Laundry','kasir','$2b$10$5dP3w9jVEo..6CJ9vylNperNnOf4g8Nz9cHJzyB7sNmUaeLMLANC6','KASIR',1,'2026-09-26 08:46:27'),(3,'Staff Laundry','staff','$2y$12$vOXaBVtWynhlB9vjkteeh.Vj5Ifxh6B1.LClouJYw9QzBvR63CuMa','STAFF_LAUNDRY',1,'2026-09-26 08:46:27'),(4,'Owner Amih Laundry','owner','$2b$10$5dP3w9jVEo..6CJ9vylNperNnOf4g8Nz9cHJzyB7sNmUaeLMLANC6','OWNER',1,'2026-09-26 08:46:27'),(6,'desi','desi','$2y$12$zQISMDQ2sOQO5j6D0GXy2uOKkxheKrVE5Rs9iRJSbhxNWXJP/7c7y','CUSTOMER',1,'2026-10-11 04:45:40'),(7,'rudi','rudi','$2y$12$0iD5qy1iRpv6Db5H.xlEK.nXFE/dav6NJB4p6EjRLRF/GfVeBsnuW','CUSTOMER',1,'2026-10-11 04:45:40'),(8,'haura','haura','$2y$12$TlHjZxxO7rZ729zl6vOWzeUgEjKWnHCGN5nGkyRkKw1/qFTBmVu5m','CUSTOMER',1,'2026-10-21 02:27:21'),(9,'rina','rina','$2y$12$5H7AH28gS53V4QncKpqcauQa6orkpkq1ghH2SYidIXSNPEhS0FDue','CUSTOMER',1,'2026-10-21 19:07:29'),(10,'dea','+62 858-0408-5297','$2y$10$DhDAYd80w5hOpRehLU1xlu0H6PwWFWEo2Ty1l1DNhwVkvAg94ohSy','CUSTOMER',1,'2026-10-25 22:30:06'),(11,'titut','titut','$2y$12$jERMtg7CCM8UUlVKlM/6K.Usgwyx0/zf8bwc4mGsWuXzgLuUmACXu','CUSTOMER',1,'2026-11-03 21:37:23'),(12,'amel','amel3841','$2y$12$8UkTFOGM5ZErEmXV3yCxbOLamPTtqlOe.rHIEZg0/MdTZ0fc1ETgK','CUSTOMER',1,'2026-11-03 22:15:07'),(13,'febi','febi','$2y$12$NxfuAZGG4/hF/83vOqOBAeyvkMa.6UJ3fufQKBV3.fiWNl/AhdGAW','CUSTOMER',1,'2026-11-04 01:37:26'),(14,'amel','amel3842','$2y$12$DvoNRN4nIJwOfi.2jGZXGeAyJAjrqLzcDgUXV3lIcxAlcKkXbeIr6','CUSTOMER',1,'2026-11-04 02:32:30'),(15,'siti','siti','$2y$12$XVzncJ/v/s9Nbt/i5o6OtOJVJj7FWomfEwXSR5JUxfiWScAwPkqom','CUSTOMER',1,'2026-11-04 02:32:30'),(16,'dayu','dayu','$2y$12$MvMEx/np0CiRSt.XEamk.uo2IxitErjpfzzwMsTmb9liZ83dhzs6G','CUSTOMER',1,'2026-11-04 02:32:30'),(17,'arkana','arkana','$2y$12$8QMxTEVqgOzOOOf7xoFmUOk9ac9E2T/yziddX2dsilgWHjJBdIY1q','CUSTOMER',1,'2026-11-04 02:32:30'),(18,'bayu','bayu','$2y$12$CGQzjSjCA01qVZJH7Iwy6eOcxeU.xf45FRJVsehyVyKIx1Sqvub8a','CUSTOMER',1,'2026-11-04 02:32:31'),(19,'riri','riri','$2y$12$1qldYISHkb69sddGq1ZBSO.UKXEyKovGUopJXO54xQQZ75M8RGI8W','CUSTOMER',1,'2026-11-04 02:32:31'),(20,'amelia','amelia3842','$2y$12$c.KyETuNVX2ovpnEkT.vkORlQGIBv8pi3Xnvo10T/6V60g5itledO','CUSTOMER',1,'2026-11-04 02:32:32'),(21,'amelia','amelia3841','$2y$12$Ubq81mSjyP9AOdvuJ4SmR.0Sye9hpW4yXkFJzp3bnD/7fX7wC4pCi','CUSTOMER',1,'2026-11-04 02:32:32'),(22,'rere','rere','$2y$12$81u8eOKlKEsaWP4VmGhDTu0yNRYX7E4rRgg1SQzv6r2I3lfb2q4k6','CUSTOMER',1,'2026-11-04 02:45:19'),(23,'amel','amel38423842','$2y$12$hGiyL6Kw6n.EwMwUp8muIe/pYHuvFzpqZu/p3eUfQiURtXj7rO2N.','CUSTOMER',1,'2026-11-04 03:06:34'),(24,'siti','siti9595','$2y$12$c8Qsl2w2dr4wctFus9hWleRjgBAfkNMqXskcX5IPLmsOc6TNy6qpW','CUSTOMER',1,'2026-11-04 03:06:34'),(25,'dayu','dayu0090','$2y$12$2HWr039AgSmeCs2E9FCDneBOhb.3cNK6PD1dJ4djpwU7oMwnpX0eC','CUSTOMER',1,'2026-11-04 03:06:35'),(26,'arkana','arkana7575','$2y$12$5uhmSSTav5iOrKmTwuMg6e5rygwHQwfNlWc2HPNEz2iKuzGMVypIG','CUSTOMER',1,'2026-11-04 03:06:35'),(27,'bayu','bayu3653','$2y$12$BQLPcTNQJLmFEkFOKg64buVt0OEUaF/jlFj/0HowHV3FBgsNdbyn.','CUSTOMER',1,'2026-11-04 03:06:36'),(28,'riri','riri0049','$2y$12$gt0ZbYT5qA5N6aEsW2rUCuaBeo1.1jdMaU4Z6oz54hWvD6H1z5buu','CUSTOMER',1,'2026-11-04 03:06:36'),(29,'amelia','amelia38423842','$2y$12$MjBCYssfA27p1N5vXb/BDuGchbiHKta7BKmL.TMS6bynoyGqXyeSq','CUSTOMER',1,'2026-11-04 03:06:37'),(30,'amelia','amelia38413841','$2y$12$EiqhI6ugxXHIQuI6SWM0UOhqwQjJXE4uURBgRMdBtciZ0ufh4zqIG','CUSTOMER',1,'2026-11-04 03:06:37'),(31,'amelia','amelia','$2y$12$cY47F6Os/rd34jN/0KFwauTnTNIZzo0UFb9UWV96UlSspJjoWaomS','CUSTOMER',1,'2026-11-09 22:23:11'),(32,'lia','lia','$2y$12$tLB04Y3j/KQi.V0VklPD7.08tDwTODwv.xeBCGGioXo4GWQi7Hy6O','CUSTOMER',1,'2026-11-16 00:51:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'amih_laundry_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-17 18:19:10
