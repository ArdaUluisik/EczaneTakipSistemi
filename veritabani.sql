-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: EczaneDB
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
-- Table structure for table `eczaneler`
--

DROP TABLE IF EXISTS `eczaneler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eczaneler` (
  `EczaneID` int(11) NOT NULL AUTO_INCREMENT,
  `EczaneAdi` varchar(100) NOT NULL,
  `IlceID` int(11) NOT NULL,
  `Adres` text DEFAULT NULL,
  `Telefon` varchar(20) DEFAULT NULL,
  `Enlem` decimal(10,8) DEFAULT NULL,
  `Boylam` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`EczaneID`),
  KEY `IlceID` (`IlceID`),
  CONSTRAINT `eczaneler_ibfk_1` FOREIGN KEY (`IlceID`) REFERENCES `ilceler` (`IlceID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eczaneler`
--

LOCK TABLES `eczaneler` WRITE;
/*!40000 ALTER TABLE `eczaneler` DISABLE KEYS */;
INSERT INTO `eczaneler` VALUES (1,'Hayat Eczanesi',1,'Moda Caddesi No:10 Kadıköy','0216 111 22 33',NULL,NULL),(2,'Şifa Eczanesi',1,'Bahariye Cd. No:5 Kadıköy','0216 444 55 66',NULL,NULL),(3,'Merkez Eczane',2,'Çarşı İçi Beşiktaş','0212 222 33 44',NULL,NULL),(4,'Güneş Eczanesi',3,'Kızılay Meydanı Ankara','0312 123 45 67',NULL,NULL);
/*!40000 ALTER TABLE `eczaneler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eczanestok`
--

DROP TABLE IF EXISTS `eczanestok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eczanestok` (
  `StokID` int(11) NOT NULL AUTO_INCREMENT,
  `EczaneID` int(11) NOT NULL,
  `IlacID` int(11) NOT NULL,
  `Adet` int(11) DEFAULT 0,
  `Fiyat` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`StokID`),
  KEY `EczaneID` (`EczaneID`),
  KEY `IlacID` (`IlacID`),
  CONSTRAINT `eczanestok_ibfk_1` FOREIGN KEY (`EczaneID`) REFERENCES `eczaneler` (`EczaneID`) ON DELETE CASCADE,
  CONSTRAINT `eczanestok_ibfk_2` FOREIGN KEY (`IlacID`) REFERENCES `ilaclar` (`IlacID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eczanestok`
--

LOCK TABLES `eczanestok` WRITE;
/*!40000 ALTER TABLE `eczanestok` DISABLE KEYS */;
INSERT INTO `eczanestok` VALUES (1,1,1,50,45.50),(2,1,2,100,25.00),(3,2,3,20,85.00),(4,3,1,10,50.00);
/*!40000 ALTER TABLE `eczanestok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hastalar`
--

DROP TABLE IF EXISTS `hastalar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hastalar` (
  `HastaID` int(11) NOT NULL AUTO_INCREMENT,
  `TCNo` varchar(11) NOT NULL,
  `Sifre` varchar(255) NOT NULL,
  `AdSoyad` varchar(100) DEFAULT NULL,
  `Telefon` varchar(20) DEFAULT NULL,
  `Adres` text DEFAULT NULL,
  PRIMARY KEY (`HastaID`),
  UNIQUE KEY `TCNo` (`TCNo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hastalar`
--

LOCK TABLES `hastalar` WRITE;
/*!40000 ALTER TABLE `hastalar` DISABLE KEYS */;
INSERT INTO `hastalar` VALUES (1,'33333333333','1234','Mehmet Hasta','0555 111 22 33',NULL);
/*!40000 ALTER TABLE `hastalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ilaclar`
--

DROP TABLE IF EXISTS `ilaclar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ilaclar` (
  `IlacID` int(11) NOT NULL AUTO_INCREMENT,
  `IlacAdi` varchar(100) NOT NULL,
  `Barkod` varchar(50) DEFAULT NULL,
  `Aciklama` text DEFAULT NULL,
  `ResimYolu` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IlacID`),
  UNIQUE KEY `Barkod` (`Barkod`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ilaclar`
--

LOCK TABLES `ilaclar` WRITE;
/*!40000 ALTER TABLE `ilaclar` DISABLE KEYS */;
INSERT INTO `ilaclar` VALUES (1,'Aspirin','8690001','Ağrı kesici',NULL),(2,'Parol','8690002','Ateş düşürücü',NULL),(3,'Majezik','8690003','Diş ağrısı',NULL),(4,'Augmentin','8690004','Antibiyotik',NULL);
/*!40000 ALTER TABLE `ilaclar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ilceler`
--

DROP TABLE IF EXISTS `ilceler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ilceler` (
  `IlceID` int(11) NOT NULL AUTO_INCREMENT,
  `IlID` int(11) NOT NULL,
  `IlceAdi` varchar(50) NOT NULL,
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
INSERT INTO `ilceler` VALUES (1,6,'Çankaya'),(2,6,'Mamak'),(3,6,'Keçiören');
/*!40000 ALTER TABLE `ilceler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iller`
--

DROP TABLE IF EXISTS `iller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iller` (
  `IlID` int(11) NOT NULL AUTO_INCREMENT,
  `IlAdi` varchar(50) NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nobetcizelgesi` (
  `NobetID` int(11) NOT NULL AUTO_INCREMENT,
  `EczaneID` int(11) NOT NULL,
  `NobetTarihi` date NOT NULL,
  `Aciklama` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`NobetID`),
  KEY `EczaneID` (`EczaneID`),
  CONSTRAINT `nobetcizelgesi_ibfk_1` FOREIGN KEY (`EczaneID`) REFERENCES `eczaneler` (`EczaneID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nobetcizelgesi`
--

LOCK TABLES `nobetcizelgesi` WRITE;
/*!40000 ALTER TABLE `nobetcizelgesi` DISABLE KEYS */;
INSERT INTO `nobetcizelgesi` VALUES (1,1,'2025-12-05','Sabaha kadar açık');
/*!40000 ALTER TABLE `nobetcizelgesi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personel`
--

DROP TABLE IF EXISTS `personel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personel` (
  `PersonelID` int(11) NOT NULL AUTO_INCREMENT,
  `EczaneID` int(11) DEFAULT NULL,
  `TCNo` varchar(11) NOT NULL,
  `Sifre` varchar(255) NOT NULL,
  `AdSoyad` varchar(100) DEFAULT NULL,
  `Rol` varchar(20) DEFAULT 'Eczaci',
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siparisdetay` (
  `DetayID` int(11) NOT NULL AUTO_INCREMENT,
  `SiparisID` int(11) NOT NULL,
  `IlacID` int(11) NOT NULL,
  `EczaneID` int(11) NOT NULL,
  `Adet` int(11) NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siparisler` (
  `SiparisID` int(11) NOT NULL AUTO_INCREMENT,
  `HastaID` int(11) NOT NULL,
  `SiparisTarihi` datetime DEFAULT current_timestamp(),
  `ToplamTutar` decimal(10,2) DEFAULT NULL,
  `Durum` varchar(20) DEFAULT 'Bekleniyor',
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

--
-- Dumping events for database 'EczaneDB'
--

--
-- Dumping routines for database 'EczaneDB'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_HastaGiris` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_HastaGiris`(IN p_TCNo VARCHAR(11), IN p_Sifre VARCHAR(255))
BEGIN
    SELECT HastaID, AdSoyad, Telefon 
    FROM Hastalar WHERE TCNo = p_TCNo AND Sifre = p_Sifre;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_IlacBul` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_IlacBul`(IN p_IlacAdi VARCHAR(100), IN p_IlceID INT)
BEGIN
    SELECT E.EczaneAdi, E.Adres, E.Telefon, S.Adet, S.Fiyat
    FROM EczaneStok S
    JOIN Ilaclar I ON S.IlacID = I.IlacID
    JOIN Eczaneler E ON S.EczaneID = E.EczaneID
    WHERE I.IlacAdi LIKE CONCAT('%', p_IlacAdi, '%') 
      AND E.IlceID = p_IlceID
      AND S.Adet > 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_NobetciGetir` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_NobetciGetir`()
BEGIN
    SELECT E.EczaneAdi, E.Adres, E.Telefon, N.Aciklama
    FROM NobetCizelgesi N
    JOIN Eczaneler E ON N.EczaneID = E.EczaneID
    WHERE N.NobetTarihi = CURDATE();
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_PersonelGiris` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_PersonelGiris`(IN p_TCNo VARCHAR(11), IN p_Sifre VARCHAR(255))
BEGIN
    SELECT PersonelID, AdSoyad, Rol, EczaneID 
    FROM Personel WHERE TCNo = p_TCNo AND Sifre = p_Sifre;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-11 12:59:51
