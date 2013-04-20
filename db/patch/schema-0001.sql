-- MySQL dump 10.13  Distrib 5.5.28, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: Lampo
-- ------------------------------------------------------
-- Server version	5.5.28-1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Anturit`
--

DROP TABLE IF EXISTS `Anturit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Anturit` (
  `Anturi` mediumint(8) NOT NULL DEFAULT '0',
  `nimi` char(20) DEFAULT NULL,
  `sensorid` char(40) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Anturi`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Mittaukset`
--

DROP TABLE IF EXISTS `Mittaukset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Mittaukset` (
  `Aika` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Anturi` mediumint(2) NOT NULL DEFAULT '0',
  `Lampotila` decimal(7,2) DEFAULT NULL,
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `Aika` (`Aika`),
  KEY `Anturi` (`Anturi`)
) ENGINE=MyISAM AUTO_INCREMENT=859224 DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `testi`
--

DROP TABLE IF EXISTS `testi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testi` (
  `Aika` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Anturi` mediumint(2) NOT NULL DEFAULT '0',
  `Lampotila` float(4,2) NOT NULL DEFAULT '-99.00',
  KEY `Aika` (`Aika`,`Anturi`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmpmittaukset`
--

DROP TABLE IF EXISTS `tmpmittaukset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmpmittaukset` (
  `lampotila` float(4,2) DEFAULT NULL,
  `sensorid` char(40) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-04-20 21:11:36
