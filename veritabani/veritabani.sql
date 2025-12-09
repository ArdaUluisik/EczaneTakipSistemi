-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eczanedb
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `eczaneler`
--

DROP TABLE IF EXISTS `eczaneler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eczaneler` (
  `EczaneID` int NOT NULL AUTO_INCREMENT,
  `EczaneAdi` varchar(100) COLLATE utf8mb4_turkish_ci NOT NULL,
  `IlceID` int NOT NULL,
  `Adres` text COLLATE utf8mb4_turkish_ci,
  `Telefon` varchar(20) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `Enlem` decimal(10,8) DEFAULT NULL,
  `Boylam` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`EczaneID`),
  KEY `IlceID` (`IlceID`),
  CONSTRAINT `eczaneler_ibfk_1` FOREIGN KEY (`IlceID`) REFERENCES `ilceler` (`IlceID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eczaneler`
--

LOCK TABLES `eczaneler` WRITE;
/*!40000 ALTER TABLE `eczaneler` DISABLE KEYS */;
INSERT INTO `eczaneler` VALUES (1,'Kadıköy Eczanesi',1,'Moda Caddesi No:10 Kadıköy','0216 111 22 33',NULL,NULL),(2,'Beşiktaş Eczanesi',2,'Çarşı İçi Beşiktaş','0212 222 33 44',NULL,NULL),(3,'Çankaya Eczanesi',3,'Kızılay Meydanı Ankara','0312 123 45 67',NULL,NULL);
/*!40000 ALTER TABLE `eczaneler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eczanestok`
--

DROP TABLE IF EXISTS `eczanestok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eczanestok` (
  `StokID` int NOT NULL AUTO_INCREMENT,
  `EczaneID` int NOT NULL,
  `IlacID` int NOT NULL,
  `Adet` int DEFAULT '0',
  `Fiyat` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`StokID`),
  KEY `EczaneID` (`EczaneID`),
  KEY `IlacID` (`IlacID`),
  CONSTRAINT `eczanestok_ibfk_1` FOREIGN KEY (`EczaneID`) REFERENCES `eczaneler` (`EczaneID`) ON DELETE CASCADE,
  CONSTRAINT `eczanestok_ibfk_2` FOREIGN KEY (`IlacID`) REFERENCES `ilaclar` (`IlacID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eczanestok`
--

LOCK TABLES `eczanestok` WRITE;
/*!40000 ALTER TABLE `eczanestok` DISABLE KEYS */;
INSERT INTO `eczanestok` VALUES (2,1,1,24,60.00);
/*!40000 ALTER TABLE `eczanestok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hastalar`
--

DROP TABLE IF EXISTS `hastalar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hastalar` (
  `HastaID` int NOT NULL AUTO_INCREMENT,
  `TCNo` varchar(11) COLLATE utf8mb4_turkish_ci NOT NULL,
  `Sifre` varchar(255) COLLATE utf8mb4_turkish_ci NOT NULL,
  `AdSoyad` varchar(100) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `Telefon` varchar(20) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `Adres` text COLLATE utf8mb4_turkish_ci,
  PRIMARY KEY (`HastaID`),
  UNIQUE KEY `TCNo` (`TCNo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hastalar`
--

LOCK TABLES `hastalar` WRITE;
/*!40000 ALTER TABLE `hastalar` DISABLE KEYS */;
INSERT INTO `hastalar` VALUES (1,'14275166798','1234','Arda Uluışık','5066898325','Mamak');
/*!40000 ALTER TABLE `hastalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ilaclar`
--

DROP TABLE IF EXISTS `ilaclar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ilaclar` (
  `IlacID` int NOT NULL AUTO_INCREMENT,
  `IlacAdi` varchar(100) COLLATE utf8mb4_turkish_ci NOT NULL,
  `Barkod` varchar(50) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `Aciklama` text COLLATE utf8mb4_turkish_ci,
  `ResimYolu` varchar(255) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `ReceteTuru` enum('Normal','Kirmizi','Sari','Yesil') COLLATE utf8mb4_turkish_ci DEFAULT 'Normal',
  PRIMARY KEY (`IlacID`),
  UNIQUE KEY `Barkod` (`Barkod`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ilaclar`
--

LOCK TABLES `ilaclar` WRITE;
/*!40000 ALTER TABLE `ilaclar` DISABLE KEYS */;
INSERT INTO `ilaclar` VALUES (1,'Aspirin','86999203','Açıklama girilmedi',NULL,'Sari');
/*!40000 ALTER TABLE `ilaclar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ilceler`
--

DROP TABLE IF EXISTS `ilceler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ilceler` (
  `IlceID` int NOT NULL AUTO_INCREMENT,
  `IlID` int NOT NULL,
  `IlceAdi` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL,
  PRIMARY KEY (`IlceID`),
  KEY `IlID` (`IlID`),
  CONSTRAINT `ilceler_ibfk_1` FOREIGN KEY (`IlID`) REFERENCES `iller` (`IlID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ilceler`
--

LOCK TABLES `ilceler` WRITE;
/*!40000 ALTER TABLE `ilceler` DISABLE KEYS */;
INSERT INTO `ilceler` VALUES (1,34,'Kadıköy'),(2,34,'Beşiktaş'),(3,6,'Çankaya');
/*!40000 ALTER TABLE `ilceler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iller`
--

DROP TABLE IF EXISTS `iller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iller` (
  `IlID` int NOT NULL AUTO_INCREMENT,
  `IlAdi` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL,
  PRIMARY KEY (`IlID`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iller`
--

LOCK TABLES `iller` WRITE;
/*!40000 ALTER TABLE `iller` DISABLE KEYS */;
INSERT INTO `iller` VALUES (6,'Ankara'),(34,'İstanbul');
/*!40000 ALTER TABLE `iller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nobetcizelgesi`
--

DROP TABLE IF EXISTS `nobetcizelgesi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nobetcizelgesi` (
  `NobetID` int NOT NULL AUTO_INCREMENT,
  `EczaneID` int NOT NULL,
  `NobetTarihi` date NOT NULL,
  `Aciklama` varchar(255) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  PRIMARY KEY (`NobetID`),
  KEY `EczaneID` (`EczaneID`),
  CONSTRAINT `nobetcizelgesi_ibfk_1` FOREIGN KEY (`EczaneID`) REFERENCES `eczaneler` (`EczaneID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nobetcizelgesi`
--

LOCK TABLES `nobetcizelgesi` WRITE;
/*!40000 ALTER TABLE `nobetcizelgesi` DISABLE KEYS */;
INSERT INTO `nobetcizelgesi` VALUES (1,1,'2025-12-09','Sabaha kadar açık'),(2,1,'2025-12-09','Sabah 08:00\'e kadar açık'),(3,2,'2025-12-09','24 Saat Açık');
/*!40000 ALTER TABLE `nobetcizelgesi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personel`
--

DROP TABLE IF EXISTS `personel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personel` (
  `PersonelID` int NOT NULL AUTO_INCREMENT,
  `EczaneID` int DEFAULT NULL,
  `TCNo` varchar(11) COLLATE utf8mb4_turkish_ci NOT NULL,
  `Sifre` varchar(255) COLLATE utf8mb4_turkish_ci NOT NULL,
  `AdSoyad` varchar(100) COLLATE utf8mb4_turkish_ci DEFAULT NULL,
  `Rol` varchar(20) COLLATE utf8mb4_turkish_ci DEFAULT 'Eczaci',
  PRIMARY KEY (`PersonelID`),
  UNIQUE KEY `TCNo` (`TCNo`),
  KEY `EczaneID` (`EczaneID`),
  CONSTRAINT `personel_ibfk_1` FOREIGN KEY (`EczaneID`) REFERENCES `eczaneler` (`EczaneID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel`
--

LOCK TABLES `personel` WRITE;
/*!40000 ALTER TABLE `personel` DISABLE KEYS */;
INSERT INTO `personel` VALUES (1,1,'11111111111','1234','Ahmet Yılmaz','Eczaci');
/*!40000 ALTER TABLE `personel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siparisdetay`
--

DROP TABLE IF EXISTS `siparisdetay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siparisdetay` (
  `DetayID` int NOT NULL AUTO_INCREMENT,
  `SiparisID` int NOT NULL,
  `IlacID` int NOT NULL,
  `EczaneID` int NOT NULL,
  `Adet` int NOT NULL,
  `BirimFiyat` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`DetayID`),
  KEY `SiparisID` (`SiparisID`),
  KEY `IlacID` (`IlacID`),
  KEY `EczaneID` (`EczaneID`),
  CONSTRAINT `siparisdetay_ibfk_1` FOREIGN KEY (`SiparisID`) REFERENCES `siparisler` (`SiparisID`),
  CONSTRAINT `siparisdetay_ibfk_2` FOREIGN KEY (`IlacID`) REFERENCES `ilaclar` (`IlacID`),
  CONSTRAINT `siparisdetay_ibfk_3` FOREIGN KEY (`EczaneID`) REFERENCES `eczaneler` (`EczaneID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisdetay`
--

LOCK TABLES `siparisdetay` WRITE;
/*!40000 ALTER TABLE `siparisdetay` DISABLE KEYS */;
/*!40000 ALTER TABLE `siparisdetay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siparisler`
--

DROP TABLE IF EXISTS `siparisler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siparisler` (
  `SiparisID` int NOT NULL AUTO_INCREMENT,
  `HastaID` int NOT NULL,
  `SiparisTarihi` datetime DEFAULT CURRENT_TIMESTAMP,
  `ToplamTutar` decimal(10,2) DEFAULT NULL,
  `Durum` varchar(20) COLLATE utf8mb4_turkish_ci DEFAULT 'Bekleniyor',
  PRIMARY KEY (`SiparisID`),
  KEY `HastaID` (`HastaID`),
  CONSTRAINT `siparisler_ibfk_1` FOREIGN KEY (`HastaID`) REFERENCES `hastalar` (`HastaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisler`
--

LOCK TABLES `siparisler` WRITE;
/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
/*!40000 ALTER TABLE `siparisler` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-09 15:11:58
