-- MariaDB dump 10.19  Distrib 10.5.12-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: u359933141_payroll
-- ------------------------------------------------------
-- Server version	10.5.12-MariaDB-cll-lve

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
-- Table structure for table `admin_log`
--

DROP TABLE IF EXISTS `admin_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `time` varchar(100) DEFAULT NULL,
  `date` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_log_ibfk_1` (`admin_id`),
  CONSTRAINT `admin_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `super_admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_log`
--

/*!40000 ALTER TABLE `admin_log` DISABLE KEYS */;
INSERT INTO `admin_log` VALUES (39,11,'Perla De Vera','Login','Login','08:23:15pm','2022/04/01'),(40,11,'Perla De Vera','Add','Available Employee','08:25:02pm','2022/04/01'),(41,11,'Perla De Vera','Add','Company','08:29:25pm','2022/04/01'),(42,11,'Perla De Vera','Assign','Available Employee','08:30:40pm','2022/04/01'),(43,11,'Perla De Vera','Add','Secretary','08:33:13pm','2022/04/01'),(44,11,'Perla De Vera','Delete','Company Position','08:34:46pm','2022/04/01'),(45,11,'Perla De Vera','Delete','Company Position','08:35:00pm','2022/04/01'),(46,11,'Perla De Vera','Delete','Company Position','08:35:11pm','2022/04/01'),(47,11,'Perla De Vera','Add','Available Employee','08:36:28pm','2022/04/01'),(48,11,'Perla De Vera','Delete','Company Position','08:37:13pm','2022/04/01'),(49,11,'Perla De Vera','Delete','Company Position','08:37:27pm','2022/04/01'),(50,11,'Perla De Vera','Edit','Company Position','08:38:15pm','2022/04/01'),(51,11,'Perla De Vera','Delete','Company Position','08:38:28pm','2022/04/01'),(52,11,'Perla De Vera','Edit','Company Position','08:39:02pm','2022/04/01'),(53,11,'Perla De Vera','Edit','Company Position','08:39:18pm','2022/04/01'),(54,11,'Perla De Vera','Edit','Company Position','08:39:37pm','2022/04/01'),(55,11,'Perla De Vera','Edit','Company Position','08:39:57pm','2022/04/01'),(56,11,'Perla De Vera','Edit','Company Position','08:40:14pm','2022/04/01'),(57,11,'Perla De Vera','Edit','Company Position','08:40:30pm','2022/04/01'),(58,11,'Perla De Vera','Assign','Available Employee','08:41:21pm','2022/04/01'),(59,11,'Perla De Vera','Reject','Leave','09:11:36pm','2022/04/01');
/*!40000 ALTER TABLE `admin_log` ENABLE KEYS */;

--
-- Table structure for table `admin_profile`
--

DROP TABLE IF EXISTS `admin_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_profile` (
  `a_id` int(11) NOT NULL AUTO_INCREMENT,
  `sa_id` int(11) NOT NULL,
  `image` longblob DEFAULT NULL,
  `created` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`a_id`),
  KEY `sa_id` (`sa_id`),
  CONSTRAINT `admin_profile_ibfk_1` FOREIGN KEY (`sa_id`) REFERENCES `super_admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_profile`
--

/*!40000 ALTER TABLE `admin_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_profile` ENABLE KEYS */;

--
-- Table structure for table `adminfeedback`
--

DROP TABLE IF EXISTS `adminfeedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adminfeedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `adminfeedback_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `super_admin` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adminfeedback`
--

/*!40000 ALTER TABLE `adminfeedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `adminfeedback` ENABLE KEYS */;

--
-- Table structure for table `automatic_generated_salary`
--

DROP TABLE IF EXISTS `automatic_generated_salary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `automatic_generated_salary` (
  `log` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(50) DEFAULT NULL,
  `total_hours` float DEFAULT NULL,
  `total_overtime` float DEFAULT NULL,
  `standard_pay` float DEFAULT NULL,
  `overtime_pay` float DEFAULT NULL,
  `regular_holiday` int(11) DEFAULT NULL,
  `regular_holiday_pay` float DEFAULT NULL,
  `special_holiday` int(11) DEFAULT NULL,
  `special_holiday_pay` float DEFAULT NULL,
  `thirteenmonth` float DEFAULT NULL,
  `sss` float NOT NULL,
  `pagibig` float NOT NULL,
  `philhealth` float NOT NULL,
  `violation` float DEFAULT NULL,
  `cashbond` float NOT NULL,
  `other` varchar(100) DEFAULT NULL,
  `other_amount` float DEFAULT NULL,
  `vale` float DEFAULT NULL,
  `total_hours_late` int(11) DEFAULT NULL,
  `late_total` float DEFAULT NULL,
  `total_gross` float DEFAULT NULL,
  `total_deduction` float DEFAULT NULL,
  `total_netpay` float DEFAULT NULL,
  `start` varchar(50) DEFAULT NULL,
  `end` varchar(50) DEFAULT NULL,
  `start_id` int(20) DEFAULT NULL,
  `end_id` int(20) DEFAULT NULL,
  `for_release` varchar(20) DEFAULT NULL,
  `date_created` varchar(50) DEFAULT NULL,
  `date_released` varchar(50) DEFAULT NULL,
  `bonus_status` varchar(20) DEFAULT NULL,
  `process_by` varchar(50) NOT NULL,
  PRIMARY KEY (`log`)
) ENGINE=InnoDB AUTO_INCREMENT=1676 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `automatic_generated_salary`
--

/*!40000 ALTER TABLE `automatic_generated_salary` DISABLE KEYS */;
/*!40000 ALTER TABLE `automatic_generated_salary` ENABLE KEYS */;

--
-- Table structure for table `cashadvance`
--

DROP TABLE IF EXISTS `cashadvance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cashadvance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(50) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cashadvance`
--

/*!40000 ALTER TABLE `cashadvance` DISABLE KEYS */;
INSERT INTO `cashadvance` VALUES (8,'2022-88','April 1, 2022',200,'paid');
/*!40000 ALTER TABLE `cashadvance` ENABLE KEYS */;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `hired_guards` varchar(3) DEFAULT NULL,
  `cpnumber` varchar(13) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `comp_location` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `boundary_size` varchar(255) DEFAULT NULL,
  `watType` varchar(50) NOT NULL,
  `shifts` varchar(50) NOT NULL,
  `shift_span` varchar(50) NOT NULL,
  `day_start` varchar(100) NOT NULL,
  `isDeleted` tinyint(4) NOT NULL DEFAULT 0,
  `date` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_compName` (`company_name`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

/*!40000 ALTER TABLE `company` DISABLE KEYS */;
INSERT INTO `company` VALUES (21,'Molave Drive','2','09060766219','filacad22@gmail.com','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','121.02493694103515','14.721979173298692','0.678km','','Day','12','06:00 am',0,'2022/03/26'),(22,'Petron','1','09060766219','francis.marianne.herrera@gmail.com','Bagbag Sauyo, Quezon City','121.03179869573177','14.696065572460412','0.108km','','Day','12','06:00 am',0,'2022/03/26'),(23,'BDO','0','09408177714','zansample3@gmail.com','BDO, Mindanao Ave, Quezon City, 1116, Philippines','121.03174269007224','14.677830307713165','0.019km','','Shift1','8','07:00 am',1,'2022/03/29'),(24,'Robinson Novaliches','2','09327878787','rob.inson@gmail.com','Robinsons Supermarket, Robinsons Nova Market, Quezon City, 1118, Philippines','121.05536676500486','14.735976169343644','0.117km','','Shift1','8','07:00 am',0,'2022/03/29'),(28,'Magnolia Place','3','09821389283','magnoliaplace@apartment.com','Tandang Sora Ave, Novaliches, Quezon City','121.02477172115181','14.679259533345515','0.971km','','Shift1','8','06:00 am',0,'2022/03/31'),(29,'JTDV Security Agency','2','09354756456','sicnarfarerreh@gmail.com','#4000 Gem Bldg. Gen T. De Leon Karuhatan Valenzuela City.','120.99112448530695','14.685410586486356','0.007km','','Shift1','8','07:00 am',0,'2022/04/01');
/*!40000 ALTER TABLE `company` ENABLE KEYS */;

--
-- Table structure for table `contributions`
--

DROP TABLE IF EXISTS `contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributions` (
  `log` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(50) NOT NULL,
  `sss` float NOT NULL,
  `philhealth` float NOT NULL,
  `pagibig` float NOT NULL,
  `cashbond` float NOT NULL,
  `date` varchar(50) NOT NULL,
  PRIMARY KEY (`log`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributions`
--

/*!40000 ALTER TABLE `contributions` DISABLE KEYS */;
/*!40000 ALTER TABLE `contributions` ENABLE KEYS */;

--
-- Table structure for table `deductions`
--

DROP TABLE IF EXISTS `deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deductions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deduction` varchar(50) DEFAULT NULL,
  `percentage` float DEFAULT NULL,
  `amount` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deductions`
--

/*!40000 ALTER TABLE `deductions` DISABLE KEYS */;
INSERT INTO `deductions` VALUES (129,'cashbond',NULL,50),(130,'Miscellaneous Fee',NULL,50);
/*!40000 ALTER TABLE `deductions` ENABLE KEYS */;

--
-- Table structure for table `do_event`
--

DROP TABLE IF EXISTS `do_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `do_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(50) NOT NULL,
  `execute_at` varchar(50) NOT NULL,
  `do_function` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `do_event`
--

/*!40000 ALTER TABLE `do_event` DISABLE KEYS */;
INSERT INTO `do_event` VALUES (99,'Absent_2022_76','2022-04-09 00:00:00','UPDATE `employee` SET `availability` = \'Unavailable\' WHERE `empId` = \'2022-76\'; DELETE FROM `do_event` WHERE `event_name` = \'Absent_2022_76\''),(100,'Absent_2022_78','2022-04-09 00:00:00','UPDATE `employee` SET `availability` = \'Unavailable\' WHERE `empId` = \'2022-78\'; DELETE FROM `do_event` WHERE `event_name` = \'Absent_2022_78\''),(101,'Absent_2022_79','2022-04-09 00:00:00','UPDATE `employee` SET `availability` = \'Unavailable\' WHERE `empId` = \'2022-79\'; DELETE FROM `do_event` WHERE `event_name` = \'Absent_2022_79\''),(102,'Absent_2022_80','2022-04-09 00:00:00','UPDATE `employee` SET `availability` = \'Unavailable\' WHERE `empId` = \'2022-80\'; DELETE FROM `do_event` WHERE `event_name` = \'Absent_2022_80\'');
/*!40000 ALTER TABLE `do_event` ENABLE KEYS */;

--
-- Table structure for table `emp_attendance`
--

DROP TABLE IF EXISTS `emp_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emp_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(50) NOT NULL,
  `timeIn` varchar(255) DEFAULT NULL,
  `timeOut` varchar(255) DEFAULT NULL,
  `datetimeIn` varchar(100) DEFAULT NULL,
  `datetimeOut` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `login_session` varchar(10) DEFAULT NULL,
  `salary_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empId` (`empId`)
) ENGINE=InnoDB AUTO_INCREMENT=400 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emp_attendance`
--

/*!40000 ALTER TABLE `emp_attendance` DISABLE KEYS */;
INSERT INTO `emp_attendance` VALUES (301,'2022-86','06:03:00 AM','06:00:00 PM','2021/01/01','2021/01/01','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(302,'2022-86','06:00:00 AM','06:00:00 PM','2021/01/02','2021/01/02','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(303,'2022-86','06:00:00 AM','05:46:00 PM','2021/01/03','2021/01/03','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(304,'2022-86','06:00:00 AM','06:00:00 PM','2021/01/04','2021/01/04','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(305,'2022-86','06:30:00 AM','05:47:00 PM','2021/01/05','2021/01/05','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(306,'2022-86','06:05:00 AM','06:00:00 PM','2021/01/06','2021/01/06','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(307,'2022-86','06:00:00 AM','05:48:00 PM','2021/01/07','2021/01/07','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(308,'2022-86','06:00:00 AM','06:00:00 PM','2021/01/08','2021/01/08','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(309,'2022-86','06:05:00 AM','06:00:00 PM','2021/01/09','2021/01/09','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(310,'2022-86','06:02:00 AM','06:00:00 PM','2021/01/10','2021/01/10','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(311,'2022-86','06:30:00 AM','05:47:00 PM','2021/01/11','2021/01/11','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(312,'2022-86','06:00:00 AM','06:00:00 PM','2021/01/12','2021/01/12','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(313,'2022-86','06:00:00 AM','02:00:00 PM','2021/01/13','2021/01/13','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(314,'2022-86','06:00:00 AM','01:49:00 PM','2021/01/14','2021/01/14','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(315,'2022-86','06:12:00 AM','05:48:00 PM','2021/01/15','2021/01/15','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(316,'2022-88','06:22:00 AM','06:00:00 PM','2021/01/01','2021/01/01','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(317,'2022-88','06:00:00 AM','06:00:00 PM','2021/01/02','2021/01/02','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(318,'2022-88','06:00:00 AM','05:46:00 PM','2021/01/03','2021/01/03','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(319,'2022-88','06:00:00 AM','06:00:00 PM','2021/01/04','2021/01/04','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(320,'2022-88','06:10:00 AM','05:47:00 PM','2021/01/05','2021/01/05','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(321,'2022-88','06:20:00 AM','06:00:00 PM','2021/01/06','2021/01/06','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(322,'2022-88','06:00:00 AM','05:48:00 PM','2021/01/07','2021/01/07','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(323,'2022-88','06:00:00 AM','06:00:00 PM','2021/01/08','2021/01/08','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(324,'2022-88','06:08:00 AM','06:00:00 PM','2021/01/09','2021/01/09','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(325,'2022-88','06:01:00 AM','06:00:00 PM','2021/01/10','2021/01/10','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(326,'2022-88','06:42:00 AM','05:47:00 PM','2021/01/11','2021/01/11','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(327,'2022-88','06:00:00 AM','06:00:00 PM','2021/01/12','2021/01/12','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(328,'2022-88','06:00:00 AM','02:00:00 PM','2021/01/13','2021/01/13','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(329,'2022-88','06:00:00 AM','01:49:00 PM','2021/01/14','2021/01/14','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Good','false','unpaid'),(330,'2022-88','06:42:00 AM','05:48:00 PM','2021/01/15','2021/01/15','Robinsons Supermarket, Robinsons Nova Market, Quezon City','Late','false','unpaid'),(331,'2022-78','02:03:00 PM','10:00:00 PM','2021/01/01','2021/01/01','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(332,'2022-78','02:00:00 PM','10:00:00 PM','2021/01/02','2021/01/02','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(333,'2022-78','02:00:00 PM','09:46:00 PM','2021/01/03','2021/01/03','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(334,'2022-78','02:00:00 PM','10:00:00 PM','2021/01/04','2021/01/04','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(335,'2022-78','02:30:00 PM','09:47:00 PM','2021/01/05','2021/01/05','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(336,'2022-78','02:05:00 PM','10:00:00 PM','2021/01/06','2021/01/06','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(337,'2022-78','02:00:00 PM','09:48:00 PM','2021/01/07','2021/01/07','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(338,'2022-78','02:00:00 PM','10:00:00 PM','2021/01/08','2021/01/08','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(339,'2022-78','02:05:00 PM','10:00:00 PM','2021/01/09','2021/01/09','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(340,'2022-78','02:02:00 PM','10:00:00 PM','2021/01/10','2021/01/10','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(341,'2022-78','02:30:00 PM','09:47:00 PM','2021/01/11','2021/01/11','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(342,'2022-78','02:00:00 PM','10:00:00 PM','2021/01/12','2021/01/12','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(343,'2022-78','02:00:00 PM','10:00:00 PM','2021/01/13','2021/01/13','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(344,'2022-78','02:00:00 PM','09:49:00 PM','2021/01/14','2021/01/14','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(345,'2022-78','02:12:00 PM','09:48:00 PM','2021/01/15','2021/01/15','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(346,'2022-80','02:03:00 PM','10:00:00 PM','2021/02/01','2021/02/01','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(347,'2022-80','02:00:00 PM','10:00:00 PM','2021/02/02','2021/02/02','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(348,'2022-80','02:00:00 PM','09:46:00 PM','2021/02/03','2021/02/03','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(349,'2022-80','02:00:00 PM','10:00:00 PM','2021/02/04','2021/02/04','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(350,'2022-80','02:30:00 PM','09:47:00 PM','2021/02/05','2021/02/05','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(351,'2022-80','02:05:00 PM','10:00:00 PM','2021/02/06','2021/02/06','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(352,'2022-80','02:00:00 PM','09:48:00 PM','2021/02/07','2021/02/07','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(353,'2022-80','02:00:00 PM','10:00:00 PM','2021/02/08','2021/02/08','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(354,'2022-80','02:05:00 PM','10:00:00 PM','2021/02/09','2021/02/09','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(355,'2022-80','02:02:00 PM','10:00:00 PM','2021/02/10','2021/02/10','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(356,'2022-80','02:30:00 PM','09:47:00 PM','2021/02/11','2021/02/11','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(357,'2022-80','02:00:00 PM','10:00:00 PM','2021/02/12','2021/02/12','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(358,'2022-80','02:00:00 PM','10:00:00 PM','2021/02/13','2021/02/13','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(359,'2022-80','02:00:00 PM','09:49:00 PM','2021/02/14','2021/02/14','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Good','false','unpaid'),(360,'2022-80','02:12:00 PM','09:48:00 PM','2021/02/15','2021/02/15','Rebisco - Main, General Luis st., Caloocan, Quezon City 1125, Philippines','Late','false','unpaid'),(362,'2022-76',NULL,'10:00:00 PM','2022/04/03','2022/04/04',NULL,'Absent','false',NULL),(363,'2022-78',NULL,NULL,'2022/04/03','2022/04/03',NULL,'Absent','false',NULL),(364,'2022-80',NULL,NULL,'2022/04/03','2022/04/03',NULL,'Absent','false',NULL),(365,'2022-1',NULL,NULL,'2022/04/04','2022/04/04',NULL,'Absent','false',NULL),(366,'2022-77',NULL,NULL,'2022/04/04','2022/04/04',NULL,'Absent','false',NULL),(367,'2022-79',NULL,NULL,'2022/04/04','2022/04/04',NULL,'Absent','false',NULL),(368,'2022-86',NULL,NULL,'2022/04/04','2022/04/04',NULL,'Absent','false',NULL),(369,'2022-88',NULL,NULL,'2022/04/04','2022/04/04',NULL,'Absent','false',NULL),(370,'2022-90',NULL,NULL,'2022/04/04','2022/04/04',NULL,'Absent','false',NULL),(371,'2022-96',NULL,NULL,'2022/04/04','2022/04/04',NULL,'Absent','false',NULL),(372,'2022-76','02:06:21 PM','10:00:00 PM','2022/04/04','2022/04/04','Tandang Sora Ave, Novaliches, Quezon City','Late','false','unpaid'),(373,'2022-1',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(374,'2022-77',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(375,'2022-79',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(376,'2022-86',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(377,'2022-88',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(378,'2022-90',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(379,'2022-96',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(380,'2022-76',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(381,'2022-78',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(382,'2022-80',NULL,NULL,'2022/04/05','2022/04/05',NULL,'Absent','false',NULL),(383,'2022-1',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(384,'2022-77',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(385,'2022-79',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(386,'2022-86',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(387,'2022-88',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(388,'2022-90',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(389,'2022-96',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(390,'2022-76',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(391,'2022-78',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(392,'2022-80',NULL,NULL,'2022/04/06','2022/04/06',NULL,'Absent','false',NULL),(393,'2022-76',NULL,NULL,'2022/04/07','2022/04/07',NULL,'Absent','false',NULL),(394,'2022-78',NULL,NULL,'2022/04/07','2022/04/07',NULL,'Absent','false',NULL),(395,'2022-80',NULL,NULL,'2022/04/07','2022/04/07',NULL,'Absent','false',NULL),(396,'2022-76',NULL,NULL,'2022/04/08','2022/04/08',NULL,'Absent','false',NULL),(397,'2022-78',NULL,NULL,'2022/04/08','2022/04/08',NULL,'Absent','false',NULL),(398,'2022-79',NULL,NULL,'2022/04/08','2022/04/08',NULL,'Absent','false',NULL),(399,'2022-80',NULL,NULL,'2022/04/08','2022/04/08',NULL,'Absent','false',NULL);
/*!40000 ALTER TABLE `emp_attendance` ENABLE KEYS */;

--
-- Table structure for table `emp_log`
--

DROP TABLE IF EXISTS `emp_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emp_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `date_created` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `empId` (`empId`),
  CONSTRAINT `emp_log_ibfk_1` FOREIGN KEY (`empId`) REFERENCES `employee` (`empId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=451 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emp_log`
--

/*!40000 ALTER TABLE `emp_log` DISABLE KEYS */;
INSERT INTO `emp_log` VALUES (1,'2022-1','Logout','2022/03/27 09:32:19 PM'),(2,'2022-76','Login','2022-03-27 09:49:33 PM'),(3,'2022-1','Login','2022-03-27 09:57:24 PM'),(4,'2022-78','Login','2022-03-28 07:03:06 AM'),(5,'2022-78','Login','2022-03-28 08:44:48 AM'),(6,'2022-1','Login','2022-03-28 11:24:16 AM'),(7,'2022-76','Logout','2022/03/28 11:38:06 AM'),(8,'2022-1','Login','2022-03-28 11:38:11 AM'),(9,'2022-1','Login','2022-03-28 11:40:13 AM'),(10,'2022-1','Login','2022-03-28 11:41:37 AM'),(11,'2022-1','Request Leave','2022/03/28 11:52:17 AM'),(12,'2022-1','Added Violation to 2022-77','2022/03/28 11:53:09 AM'),(13,'2022-1','Logout','2022/03/28 12:45:52 PM'),(14,'2022-1','Login','2022-03-28 03:21:58 PM'),(15,'2022-1','Login','2022-03-28 03:28:56 PM'),(16,'2022-1','Logout','2022/03/28 03:28:58 PM'),(17,'2022-1','Login','2022-03-28 03:45:34 PM'),(18,'2022-1','Logout','2022/03/28 03:45:54 PM'),(19,'2022-1','Login','2022-03-28 03:46:01 PM'),(20,'2022-1','Logout','2022/03/28 03:46:36 PM'),(21,'2022-1','Login','2022-03-28 03:50:38 PM'),(22,'2022-1','Login','2022-03-28 03:55:38 PM'),(23,'2022-1','Logout','2022/03/28 03:56:08 PM'),(24,'2022-1','Login','2022-03-28 04:06:50 PM'),(25,'2022-1','Login','2022-03-28 04:07:42 PM'),(26,'2022-1','Login','2022-03-28 04:44:51 PM'),(27,'2022-1','Logout','2022/03/28 04:45:16 PM'),(28,'2022-1','Login','2022-03-28 04:46:17 PM'),(29,'2022-76','Login','2022-03-28 04:47:02 PM'),(30,'2022-76','Logout','2022/03/28 04:47:05 PM'),(31,'2022-1','Login','2022-03-28 04:47:14 PM'),(32,'2022-1','Login','2022-03-28 04:47:59 PM'),(33,'2022-1','Login','2022-03-28 04:48:46 PM'),(34,'2022-1','Login','2022-03-28 04:49:31 PM'),(35,'2022-1','Logout','2022/03/28 04:51:57 PM'),(36,'2022-1','Login','2022-03-28 04:52:13 PM'),(37,'2022-1','Login','2022-03-28 04:58:49 PM'),(38,'2022-76','Login','2022-03-28 05:00:25 PM'),(39,'2022-76','Login','2022-03-28 05:02:45 PM'),(40,'2022-1','Login','2022-03-28 05:09:55 PM'),(41,'2022-1','Login','2022-03-28 05:10:33 PM'),(42,'2022-1','Login','2022-03-28 05:12:45 PM'),(43,'2022-1','Logout','2022/03/28 05:12:56 PM'),(44,'2022-1','Login','2022-03-28 05:13:03 PM'),(45,'2022-76','Logout','2022/03/28 05:14:19 PM'),(46,'2022-1','Login','2022-03-28 05:14:38 PM'),(47,'2022-1','Logout','2022/03/28 05:16:22 PM'),(48,'2022-76','Login','2022-03-28 05:18:15 PM'),(49,'2022-1','Login','2022-03-28 05:18:52 PM'),(50,'2022-1','Login','2022-03-28 05:21:57 PM'),(51,'2022-76','Logout','2022/03/28 05:29:02 PM'),(52,'2022-1','Login','2022-03-28 05:31:01 PM'),(53,'2022-1','Logout','2022/03/28 05:31:08 PM'),(54,'2022-1','Login','2022-03-28 05:31:16 PM'),(55,'2022-1','Logout','2022/03/28 05:31:42 PM'),(56,'2022-1','Login','2022-03-28 05:31:47 PM'),(57,'2022-1','Login','2022-03-28 05:33:11 PM'),(58,'2022-1','Login','2022-03-28 05:33:49 PM'),(59,'2022-1','Login','2022-03-28 05:34:10 PM'),(60,'2022-1','Logout','2022/03/28 05:34:17 PM'),(61,'2022-1','Login','2022-03-28 05:34:28 PM'),(62,'2022-1','Logout','2022/03/28 05:35:28 PM'),(63,'2022-76','Login','2022-03-28 05:37:23 PM'),(64,'2022-76','Logout','2022/03/28 05:37:29 PM'),(65,'2022-1','Login','2022-03-28 05:37:52 PM'),(66,'2022-1','Logout','2022/03/28 05:38:10 PM'),(67,'2022-1','Login','2022-03-28 05:38:59 PM'),(68,'2022-1','Logout','2022/03/28 05:39:38 PM'),(69,'2022-1','Login','2022-03-28 05:41:01 PM'),(70,'2022-1','Logout','2022/03/28 05:41:05 PM'),(71,'2022-1','Login','2022-03-28 05:41:46 PM'),(72,'2022-1','Logout','2022/03/28 05:42:22 PM'),(73,'2022-1','Login','2022-03-28 05:42:35 PM'),(74,'2022-1','Logout','2022/03/28 05:42:45 PM'),(75,'2022-1','Login','2022-03-28 05:42:53 PM'),(76,'2022-1','Logout','2022/03/28 05:44:54 PM'),(77,'2022-1','Login','2022-03-28 05:45:11 PM'),(78,'2022-1','Logout','2022/03/28 05:46:05 PM'),(79,'2022-1','Login','2022-03-28 05:46:26 PM'),(80,'2022-1','Login','2022-03-28 05:48:40 PM'),(81,'2022-1','Login','2022-03-28 05:49:24 PM'),(82,'2022-1','Login','2022-03-28 05:51:04 PM'),(83,'2022-1','Logout','2022/03/28 05:54:20 PM'),(84,'2022-76','Login','2022-03-28 05:56:03 PM'),(85,'2022-76','Logout','2022/03/28 05:56:34 PM'),(86,'2022-1','Login','2022-03-28 05:56:39 PM'),(87,'2022-1','Logout','2022/03/28 05:57:51 PM'),(88,'2022-1','Login','2022-03-28 06:02:12 PM'),(89,'2022-1','Login','2022-03-28 06:03:38 PM'),(90,'2022-1','Logout','2022/03/28 06:03:43 PM'),(91,'2022-1','Login','2022-03-28 06:03:48 PM'),(92,'2022-1','Logout','2022/03/28 06:04:13 PM'),(93,'2022-1','Logout','2022/03/28 06:09:17 PM'),(94,'2022-1','Login','2022-03-28 06:09:32 PM'),(95,'2022-1','Login','2022-03-28 06:11:17 PM'),(96,'2022-1','Logout','2022/03/28 06:11:23 PM'),(97,'2022-1','Login','2022-03-28 06:13:04 PM'),(98,'2022-1','Login','2022-03-28 06:14:41 PM'),(99,'2022-1','Logout','2022/03/28 06:15:10 PM'),(100,'2022-1','Login','2022-03-28 06:18:03 PM'),(101,'2022-1','Login','2022-03-28 06:28:19 PM'),(102,'2022-1','Logout','2022/03/28 06:29:25 PM'),(103,'2022-1','Login','2022-03-28 06:29:35 PM'),(104,'2022-1','Logout','2022/03/28 06:31:02 PM'),(105,'2022-1','Login','2022-03-28 06:31:08 PM'),(106,'2022-1','Login','2022-03-28 06:33:40 PM'),(107,'2022-1','Login','2022-03-28 06:37:35 PM'),(108,'2022-1','Login','2022-03-28 06:39:03 PM'),(109,'2022-1','Logout','2022/03/28 06:39:11 PM'),(110,'2022-1','Login','2022-03-28 06:40:26 PM'),(111,'2022-1','Login','2022-03-28 06:44:19 PM'),(112,'2022-1','Login','2022-03-28 07:01:52 PM'),(113,'2022-1','Logout','2022/03/28 07:08:04 PM'),(114,'2022-1','Logout','2022/03/28 07:22:30 PM'),(115,'2022-1','Login','2022-03-28 07:23:17 PM'),(116,'2022-1','Logout','2022/03/28 07:34:39 PM'),(117,'2022-1','Login','2022-03-28 07:46:41 PM'),(118,'2022-1','Logout','2022/03/28 08:20:10 PM'),(119,'2022-1','Login','2022-03-28 09:34:05 PM'),(120,'2022-76','Login','2022-03-28 10:18:31 PM'),(121,'2022-76','Login','2022-03-28 10:20:49 PM'),(122,'2022-76','Login','2022-03-29 01:01:05 AM'),(123,'2022-76','Login','2022-03-29 01:03:23 AM'),(124,'2022-1','Login','2022-03-29 08:10:03 AM'),(125,'2022-1','Login','2022-03-29 08:20:00 AM'),(126,'2022-1','Login','2022-03-29 08:37:27 AM'),(127,'2022-1','Login','2022-03-29 09:16:55 AM'),(128,'2022-76','Login','2022-03-29 09:50:05 AM'),(129,'2022-76','Login','2022-03-29 09:52:02 AM'),(130,'2022-1','Login','2022-03-29 09:52:24 AM'),(131,'2022-1','Login','2022-03-29 10:22:29 AM'),(132,'2022-1','Logout','2022/03/29 10:25:13 AM'),(133,'2022-76','Login','2022-03-29 10:34:45 AM'),(134,'2022-1','Login','2022-03-29 10:43:56 AM'),(135,'2022-1','Logout','2022/03/29 10:48:48 AM'),(136,'2022-1','Login','2022-03-29 10:49:12 AM'),(137,'2022-76','Login','2022-03-29 10:50:59 AM'),(138,'2022-76','Login','2022-03-29 10:52:40 AM'),(139,'2022-76','Login','2022-03-29 10:55:03 AM'),(140,'2022-1','Logout','2022/03/29 11:09:21 AM'),(141,'2022-76','Login','2022-03-29 11:09:27 AM'),(142,'2022-76','Request Leave','2022/03/29 11:11:11 AM'),(143,'2022-76','Logout','2022/03/29 11:12:53 AM'),(144,'2022-76','Login','2022-03-29 11:13:10 AM'),(145,'2022-1','Login','2022-03-29 11:16:51 AM'),(146,'2022-76','Login','2022-03-29 11:29:14 AM'),(147,'2022-76','Logout','2022/03/29 11:30:30 AM'),(148,'2022-76','Logout','2022/03/29 11:58:23 AM'),(149,'2022-1','Login','2022-03-29 01:15:32 PM'),(150,'2022-1','Logout','2022/03/29 01:26:39 PM'),(151,'2022-77','Login','2022-03-29 01:26:45 PM'),(152,'2022-77','Login','2022-03-29 01:44:54 PM'),(153,'2022-77','Request Leave','2022/03/29 02:08:10 PM'),(154,'2022-77','Logout','2022/03/29 02:10:35 PM'),(155,'2022-77','Login','2022-03-29 02:36:10 PM'),(156,'2022-78','Login','2022-03-29 02:50:34 PM'),(157,'2022-77','Logout','2022/03/29 02:51:38 PM'),(158,'2022-1','Login','2022-03-29 02:55:54 PM'),(159,'2022-1','Logout','2022/03/29 03:00:35 PM'),(160,'2022-77','Login','2022-03-29 03:00:47 PM'),(161,'2022-77','Logout','2022/03/29 03:04:44 PM'),(162,'2022-77','Login','2022-03-29 03:04:48 PM'),(163,'2022-77','Logout','2022/03/29 03:21:22 PM'),(164,'2022-1','Login','2022-03-29 03:21:34 PM'),(165,'2022-1','Logout','2022/03/29 03:24:38 PM'),(166,'2022-76','Login','2022-03-29 03:24:42 PM'),(167,'2022-76','Logout','2022/03/29 03:25:50 PM'),(168,'2022-1','Login','2022-03-29 03:26:08 PM'),(169,'2022-1','Logout','2022/03/29 03:29:57 PM'),(170,'2022-76','Login','2022-03-29 03:30:01 PM'),(171,'2022-76','Logout','2022/03/29 03:30:09 PM'),(172,'2022-77','Login','2022-03-29 03:30:14 PM'),(173,'2022-77','Logout','2022/03/29 03:30:21 PM'),(174,'2022-76','Login','2022-03-29 03:30:29 PM'),(175,'2022-76','Logout','2022/03/29 03:33:48 PM'),(176,'2022-1','Login','2022-03-29 03:33:53 PM'),(177,'2022-1','Logout','2022/03/29 03:34:12 PM'),(178,'2022-76','Login','2022-03-29 03:34:19 PM'),(179,'2022-76','Logout','2022/03/29 03:38:22 PM'),(180,'2022-76','Login','2022-03-29 03:38:27 PM'),(181,'2022-76','Logout','2022/03/29 03:39:43 PM'),(182,'2022-1','Login','2022-03-29 03:40:32 PM'),(183,'2022-1','Logout','2022/03/29 03:41:30 PM'),(184,'2022-76','Login','2022-03-29 03:41:45 PM'),(185,'2022-76','Logout','2022/03/29 03:43:19 PM'),(186,'2022-77','Logout','2022/03/29 03:43:49 PM'),(187,'2022-76','Login','2022-03-29 03:46:46 PM'),(188,'2022-76','Logout','2022/03/29 03:58:52 PM'),(189,'2022-77','Login','2022-03-29 03:58:57 PM'),(190,'2022-77','Logout','2022/03/29 04:01:11 PM'),(191,'2022-1','Login','2022-03-29 04:01:17 PM'),(192,'2022-1','Login','2022-03-29 04:03:00 PM'),(193,'2022-1','Logout','2022/03/29 04:03:20 PM'),(194,'2022-77','Login','2022-03-29 04:03:36 PM'),(195,'2022-1','Login','2022-03-29 04:06:19 PM'),(196,'2022-1','Login','2022-03-29 04:09:32 PM'),(197,'2022-1','Login','2022-03-29 04:11:07 PM'),(198,'2022-1','Login','2022-03-29 04:30:54 PM'),(199,'2022-1','Logout','2022/03/29 04:39:46 PM'),(200,'2022-76','Login','2022-03-29 04:39:58 PM'),(201,'2022-76','Logout','2022/03/29 04:48:44 PM'),(202,'2022-1','Login','2022-03-29 04:48:51 PM'),(203,'2022-1','Logout','2022/03/29 04:59:35 PM'),(204,'2022-1','Logout','2022/03/29 05:10:09 PM'),(205,'2022-76','Login','2022-03-29 05:11:33 PM'),(206,'2022-78','Login','2022-03-29 05:19:44 PM'),(207,'2022-1','Login','2022-03-29 05:45:14 PM'),(208,'2022-1','Logout','2022/03/29 05:45:46 PM'),(209,'2022-1','Login','2022-03-29 06:09:43 PM'),(210,'2022-76','Login','2022-03-29 06:30:28 PM'),(211,'2022-76','Login','2022-03-29 06:56:05 PM'),(212,'2022-76','Login','2022-03-29 07:24:22 PM'),(213,'2022-76','Logout','2022/03/29 07:27:46 PM'),(214,'2022-1','Login','2022-03-29 07:27:53 PM'),(215,'2022-1','Logout','2022/03/29 07:50:06 PM'),(216,'2022-1','Login','2022-03-29 07:50:21 PM'),(217,'2022-1','Logout','2022/03/29 07:50:39 PM'),(218,'2022-77','Login','2022-03-29 07:50:44 PM'),(219,'2022-77','Logout','2022/03/29 08:02:33 PM'),(220,'2022-77','Login','2022-03-29 08:02:39 PM'),(221,'2022-77','Logout','2022/03/29 08:02:45 PM'),(222,'2022-76','Login','2022-03-29 08:02:50 PM'),(223,'2022-76','Logout','2022/03/29 08:06:13 PM'),(224,'2022-76','Login','2022-03-29 08:06:18 PM'),(225,'2022-76','Logout','2022/03/29 11:33:17 PM'),(226,'2022-76','Login','2022-03-29 11:52:18 PM'),(227,'2022-76','Logout','2022/03/29 11:52:46 PM'),(228,'2022-1','Login','2022-03-29 11:52:52 PM'),(229,'2022-1','Login','2022-03-29 11:54:36 PM'),(230,'2022-1','Logout','2022/03/29 11:54:54 PM'),(231,'2022-1','Logout','2022/03/29 11:57:05 PM'),(232,'2022-1','Login','2022-03-29 11:57:21 PM'),(233,'2022-1','Added Violation to 2022-76','2022/03/29 11:58:56 PM'),(234,'2022-1','Logout','2022/03/30 12:02:42 AM'),(235,'2022-1','Login','2022-03-30 12:54:06 AM'),(236,'2022-1','Login','2022-03-30 12:56:37 AM'),(237,'2022-1','Login','2022-03-30 01:22:52 AM'),(238,'2022-1','Logout','2022/03/30 01:23:52 AM'),(239,'2022-1','Login','2022-03-30 09:42:46 AM'),(240,'2022-76','Login','2022-03-30 12:32:05 PM'),(241,'2022-76','Request Leave','2022/03/30 12:35:22 PM'),(242,'2022-76','Logout','2022/03/30 12:37:45 PM'),(243,'2022-1','Login','2022-03-30 12:37:54 PM'),(244,'2022-1','Logout','2022/03/30 12:45:42 PM'),(245,'2022-77','Login','2022-03-30 12:45:48 PM'),(246,'2022-77','Logout','2022/03/30 12:51:44 PM'),(247,'2022-1','Login','2022-03-30 12:52:03 PM'),(248,'2022-1','Logout','2022/03/30 12:52:46 PM'),(249,'2022-77','Login','2022-03-30 12:52:53 PM'),(250,'2022-77','Logout','2022/03/30 12:55:12 PM'),(251,'2022-1','Login','2022-03-30 12:58:11 PM'),(252,'2022-1','Logout','2022/03/30 01:07:27 PM'),(253,'2022-1','Login','2022-03-30 01:14:45 PM'),(254,'2022-1','Login','2022-03-30 01:20:12 PM'),(255,'2022-1','Logout','2022/03/30 01:23:10 PM'),(256,'2022-1','Login','2022-03-30 01:23:16 PM'),(257,'2022-1','Logout','2022/03/30 01:38:38 PM'),(258,'2022-84','Login','2022-03-30 01:42:48 PM'),(259,'2022-84','Logout','2022/03/30 01:43:53 PM'),(260,'2022-1','Login','2022-03-30 01:44:05 PM'),(261,'2022-1','Logout','2022/03/30 01:44:33 PM'),(262,'2022-84','Login','2022-03-30 01:44:38 PM'),(263,'2022-84','Logout','2022/03/30 01:44:46 PM'),(264,'2022-84','Login','2022-03-30 01:44:58 PM'),(265,'2022-84','Logout','2022/03/30 01:45:01 PM'),(266,'2022-1','Login','2022-03-30 01:45:10 PM'),(267,'2022-1','Logout','2022/03/30 01:45:42 PM'),(268,'2022-84','Login','2022-03-30 01:45:49 PM'),(269,'2022-84','Logout','2022/03/30 01:46:59 PM'),(270,'2022-1','Login','2022-03-30 02:28:13 PM'),(271,'2022-1','Added Violation to 2022-84','2022/03/30 02:29:41 PM'),(272,'2022-1','Logout','2022/03/30 02:32:41 PM'),(273,'2022-84','Login','2022-03-30 02:32:53 PM'),(274,'2022-84','Logout','2022/03/30 02:39:05 PM'),(275,'2022-1','Login','2022-03-30 02:39:12 PM'),(276,'2022-1','Logout','2022/03/30 02:42:27 PM'),(277,'2022-1','Login','2022-03-30 05:29:11 PM'),(278,'2022-1','Logout','2022/03/30 05:29:22 PM'),(279,'2022-1','Login','2022-03-30 05:29:26 PM'),(280,'2022-1','Logout','2022/03/30 05:29:31 PM'),(281,'2022-77','Login','2022-03-30 05:29:36 PM'),(282,'2022-77','Logout','2022/03/30 05:29:48 PM'),(283,'2022-1','Login','2022-03-30 09:41:33 PM'),(284,'2022-76','Login','2022-03-30 09:49:15 PM'),(285,'2022-1','Login','2022-03-31 01:57:15 AM'),(286,'2022-1','Logout','2022/03/31 01:57:21 AM'),(287,'2022-1','Login','2022-03-31 02:00:41 AM'),(288,'2022-1','Login','2022-03-31 02:23:59 AM'),(289,'2022-1','Login','2022-03-31 10:58:02 AM'),(290,'2022-1','Logout','2022/03/31 11:04:07 AM'),(291,'2022-1','Login','2022-03-31 11:08:50 AM'),(292,'2022-76','Login','2022-03-31 12:08:21 PM'),(293,'2022-76','Logout','2022/03/31 12:08:25 PM'),(294,'2022-1','Login','2022-03-31 01:19:54 PM'),(295,'2022-1','Login','2022-03-31 03:21:28 PM'),(296,'2022-76','Login','2022-03-31 03:30:35 PM'),(297,'2022-76','Logout','2022/03/31 03:36:00 PM'),(298,'2022-1','Login','2022-03-31 03:37:03 PM'),(299,'2022-87','Login','2022-03-31 03:39:39 PM'),(300,'2022-87','Logout','2022/03/31 03:41:53 PM'),(301,'2022-1','Login','2022-03-31 03:42:06 PM'),(302,'2022-76','Login','2022-03-31 03:45:18 PM'),(303,'2022-76','Logout','2022/03/31 03:46:15 PM'),(304,'2022-1','Login','2022-03-31 03:46:25 PM'),(305,'2022-1','Logout','2022/03/31 03:54:50 PM'),(306,'2022-77','Login','2022-03-31 03:55:05 PM'),(307,'2022-77','Request Leave','2022/03/31 03:56:01 PM'),(308,'2022-77','Logout','2022/03/31 03:59:26 PM'),(309,'2022-1','Login','2022-03-31 04:01:03 PM'),(310,'2022-1','Logout','2022/03/31 04:03:18 PM'),(311,'2022-1','Login','2022-03-31 04:04:59 PM'),(312,'2022-1','Added Violation to 2022-77','2022/03/31 04:05:39 PM'),(313,'2022-1','Logout','2022/03/31 04:09:55 PM'),(314,'2022-77','Login','2022-03-31 04:11:22 PM'),(315,'2022-77','Login','2022-03-31 04:12:13 PM'),(316,'2022-77','Logout','2022/03/31 04:14:07 PM'),(320,'2022-84','Login','2022-03-31 04:51:44 PM'),(321,'2022-84','Login','2022-03-31 04:52:33 PM'),(323,'2022-1','Login','2022-03-31 06:01:59 PM'),(324,'2022-1','Logout','2022/03/31 06:06:07 PM'),(325,'2022-1','Login','2022-03-31 06:09:13 PM'),(326,'2022-1','Logout','2022/03/31 06:10:48 PM'),(327,'2022-1','Login','2022-03-31 06:46:44 PM'),(328,'2022-1','Logout','2022/03/31 07:00:44 PM'),(329,'2022-76','Login','2022-03-31 07:00:51 PM'),(330,'2022-76','Logout','2022/03/31 07:02:32 PM'),(331,'2022-1','Login','2022-03-31 07:02:37 PM'),(332,'2022-1','Added Violation to 2022-76','2022/03/31 07:04:39 PM'),(333,'2022-1','Logout','2022/03/31 07:10:05 PM'),(334,'2022-76','Login','2022-03-31 07:10:09 PM'),(335,'2022-76','Request Leave','2022/03/31 07:16:16 PM'),(336,'2022-76','Logout','2022/03/31 07:22:59 PM'),(337,'2022-76','Login','2022-03-31 07:24:00 PM'),(338,'2022-76','Logout','2022/03/31 07:32:03 PM'),(339,'2022-76','Login','2022-03-31 07:32:27 PM'),(340,'2022-76','Logout','2022/03/31 07:33:36 PM'),(341,'2022-1','Login','2022-03-31 07:33:46 PM'),(342,'2022-1','Logout','2022/03/31 07:34:02 PM'),(343,'2022-83','Login','2022-03-31 07:34:28 PM'),(344,'2022-83','Logout','2022/03/31 07:34:37 PM'),(345,'2022-83','Login','2022-03-31 08:07:56 PM'),(346,'2022-83','Logout','2022/03/31 08:08:13 PM'),(347,'2022-1','Login','2022-03-31 08:11:39 PM'),(348,'2022-1','Logout','2022/03/31 08:11:41 PM'),(349,'2022-76','Login','2022-03-31 08:12:07 PM'),(350,'2022-76','Logout','2022/03/31 08:12:13 PM'),(351,'2022-83','Login','2022-03-31 08:13:11 PM'),(352,'2022-83','Logout','2022/03/31 08:13:16 PM'),(353,'2022-1','Login','2022-03-31 08:13:30 PM'),(354,'2022-1','Logout','2022/03/31 08:18:53 PM'),(355,'2022-76','Login','2022-03-31 08:40:53 PM'),(356,'2022-76','Logout','2022/03/31 08:41:42 PM'),(357,'2022-1','Login','2022-03-31 08:41:48 PM'),(358,'2022-1','Logout','2022/03/31 08:49:58 PM'),(359,'2022-1','Login','2022-04-01 02:54:36 AM'),(360,'2022-1','Login','2022-04-01 03:26:28 AM'),(361,'2022-1','Logout','2022/04/01 03:27:42 AM'),(362,'2022-1','Login','2022-04-01 03:27:46 AM'),(363,'2022-1','Logout','2022/04/01 03:27:49 AM'),(364,'2022-78','Login','2022-04-01 08:20:21 AM'),(365,'2022-1','Login','2022-04-01 09:17:55 AM'),(366,'2022-1','Logout','2022/04/01 09:19:54 AM'),(367,'2022-76','Login','2022-04-01 09:24:09 AM'),(368,'2022-76','Logout','2022/04/01 09:29:39 AM'),(369,'2022-1','Login','2022-04-01 09:29:53 AM'),(370,'2022-1','Logout','2022/04/01 09:41:49 AM'),(371,'2022-1','Login','2022-04-01 10:30:15 AM'),(372,'2022-1','Logout','2022/04/01 10:30:49 AM'),(373,'2022-88','Login','2022-04-01 10:44:21 AM'),(374,'2022-88','Logout','2022/04/01 12:05:49 PM'),(377,'2022-1','Login','2022-04-01 03:54:34 PM'),(378,'2022-1','Login','2022-04-01 04:33:15 PM'),(379,'2022-1','Logout','2022/04/01 04:37:37 PM'),(380,'2022-1','Login','2022-04-01 05:15:49 PM'),(381,'2022-1','Added Violation to 2022-76','2022/04/01 05:16:19 PM'),(382,'2022-1','Added Violation to 2022-76','2022/04/01 05:20:59 PM'),(383,'2022-1','Logout','2022/04/01 05:22:09 PM'),(384,'2022-76','Login','2022-04-01 05:22:49 PM'),(385,'2022-76','Logout','2022/04/01 05:23:16 PM'),(386,'2022-1','Login','2022-04-01 06:38:05 PM'),(387,'2022-1','Logout','2022/04/01 06:46:49 PM'),(388,'2022-96','Login','2022-04-01 08:41:37 PM'),(389,'2022-96','Login','2022-04-01 08:41:39 PM'),(390,'2022-96','Logout','2022/04/01 08:41:40 PM'),(391,'2022-96','Logout','2022/04/01 08:44:14 PM'),(392,'2022-1','Login','2022-04-01 08:44:24 PM'),(393,'2022-1','Logout','2022/04/01 08:54:05 PM'),(394,'2022-76','Login','2022-04-01 08:54:35 PM'),(395,'2022-76','Logout','2022/04/01 08:55:13 PM'),(396,'2022-76','Login','2022-04-01 08:56:43 PM'),(397,'2022-76','Logout','2022/04/01 08:58:49 PM'),(398,'2022-1','Login','2022-04-01 08:58:59 PM'),(399,'2022-1','Request Leave','2022/04/01 09:01:10 PM'),(400,'2022-1','Logout','2022/04/01 09:25:34 PM'),(401,'2022-1','Login','2022-04-03 09:52:32 PM'),(402,'2022-1','Logout','2022/04/03 09:58:41 PM'),(403,'2022-76','Login','2022-04-03 09:58:48 PM'),(404,'2022-76','Logout','2022/04/03 10:04:00 PM'),(405,'2022-1','Login','2022-04-03 10:04:05 PM'),(406,'2022-1','Logout','2022/04/03 10:08:25 PM'),(407,'2022-76','Login','2022-04-03 10:08:30 PM'),(408,'2022-76','Logout','2022/04/03 10:16:10 PM'),(409,'2022-76','Login','2022-04-03 10:16:15 PM'),(410,'2022-76','Logout','2022/04/03 10:20:28 PM'),(411,'2022-1','Login','2022-04-03 10:20:36 PM'),(412,'2022-1','Logout','2022/04/03 10:21:39 PM'),(413,'2022-80','Login','2022-04-03 10:26:45 PM'),(414,'2022-80','Logout','2022/04/03 10:32:47 PM'),(415,'2022-76','Login','2022-04-03 10:32:54 PM'),(416,'2022-76','Logout','2022/04/03 10:33:02 PM'),(417,'2022-1','Login','2022-04-03 10:33:07 PM'),(418,'2022-1','Logout','2022/04/03 10:38:00 PM'),(419,'2022-76','Login','2022-04-03 10:38:06 PM'),(420,'2022-76','Logout','2022/04/03 10:38:15 PM'),(421,'2022-1','Login','2022-04-04 11:11:42 AM'),(422,'2022-1','Logout','2022/04/04 11:12:01 AM'),(423,'2022-76','Login','2022-04-04 11:12:15 AM'),(424,'2022-76','Logout','2022/04/04 11:12:44 AM'),(425,'2022-77','Login','2022-04-04 11:12:53 AM'),(426,'2022-77','Logout','2022/04/04 11:13:03 AM'),(427,'2022-76','Login','2022-04-04 11:13:11 AM'),(428,'2022-76','Logout','2022/04/04 11:13:24 AM'),(429,'2022-1','Login','2022-04-04 11:13:30 AM'),(430,'2022-1','Logout','2022/04/04 11:13:45 AM'),(431,'2022-76','Login','2022-04-04 01:48:13 PM'),(432,'2022-76','Logout','2022/04/04 01:48:41 PM'),(433,'2022-76','Login','2022-04-04 01:49:16 PM'),(434,'2022-76','Login','2022-04-04 02:19:47 PM'),(435,'2022-76','Logout','2022/04/04 02:20:35 PM'),(436,'2022-1','Login','2022-04-04 02:20:48 PM'),(437,'2022-1','Logout','2022/04/04 02:23:34 PM'),(438,'2022-76','Login','2022-04-04 02:23:45 PM'),(439,'2022-76','Logout','2022/04/04 02:27:33 PM'),(440,'2022-76','Logout','2022/04/04 02:28:12 PM'),(441,'2022-1','Login','2022-04-05 01:45:26 PM'),(442,'2022-1','Logout','2022/04/05 01:48:28 PM'),(443,'2022-1','Login','2022-04-08 04:00:45 PM'),(444,'2022-1','Logout','2022/04/08 04:20:45 PM'),(445,'2022-76','Login','2022-04-08 04:21:22 PM'),(446,'2022-76','Logout','2022/04/08 04:21:44 PM'),(447,'2022-1','Login','2022-04-08 04:21:49 PM'),(448,'2022-1','Logout','2022/04/08 04:26:03 PM'),(449,'2022-76','Login','2022-04-08 04:26:14 PM'),(450,'2022-76','Logout','2022/04/08 04:27:51 PM');
/*!40000 ALTER TABLE `emp_log` ENABLE KEYS */;

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(50) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `cpnumber` varchar(13) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `ratesperDay` varchar(11) DEFAULT NULL,
  `overtime_rate` varchar(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `qrcode` varchar(255) DEFAULT NULL,
  `access` varchar(100) NOT NULL,
  `availability` varchar(100) NOT NULL,
  `isDeleted` tinyint(4) NOT NULL DEFAULT 0,
  `timer` varchar(255) DEFAULT NULL,
  `time` varchar(100) DEFAULT NULL,
  `date` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`empId`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee`
--

/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
INSERT INTO `employee` VALUES (76,'2022-1','Peter','San Juan','09123456799','Blk 12 Lot 2 Engineering St, Quezon City','Officer in Charge','54.00','60.00','von39gaming@gmail.com','2bd0a2ad94c1f4f7371057c92d03d68c','Fq946VMH','employee','Unavailable',0,NULL,'06:12:47pm','2022/03/25'),(77,'2022-76','Luisito','Cruz','09999992989','South Universe Rooks St, Cebu City','Security Guard','50.00','54.00','von39gamingx@gmail.com','2bd0a2ad94c1f4f7371057c92d03d68c','l8bhLc26','employee','Absent',0,NULL,'08:01:33pm','2022/03/25'),(78,'2022-77','Andres','Dela Cruz','09568570501','44-T Tandang Sora, Quezon City','Security Guard','50.00','54.00','vonnedewsalig@gmail.com','2bd0a2ad94c1f4f7371057c92d03d68c','BSQsUCZf','employee','Unavailable',0,NULL,'08:04:30pm','2022/03/25'),(79,'2022-78','Clarisse','Santos','09458522246','Novaliches, Quezon City','Security Guard','55.00','60.00','mapatriciaclarisse.santos2001@gmail.com','6b44b4a6685527c22c3faebc8576154c','3hmfDMuD','employee','Absent',0,NULL,'04:28:19am','2022/03/26'),(80,'2022-79','Francis Albert','Ilacad','09060766219','Blk 3 Lot Carinosa St. Barangay Sta. Monica, Novaliches, Quezon City','Officer in Charge','50.00','55.00','francis.albert.sarcaoga.ilacad@gmail.com','f77064823fb425aae4b33dfb9ab1ee0a','nAf0T1DK','employee','Absent',0,NULL,'04:44:24am','2022/03/26'),(81,'2022-80','Van','Openiano','09521612112','Quezon City','Security Guard','55.00','60.00','ghil.adam@gmail.com','fbc636e5b1020ff6fbb3d64bf43c39ff','dhVG6AW3','employee','Absent',0,NULL,'05:11:29am','2022/03/26'),(84,'2022-83','Jerry','Dela Cruz','09123456787','12-Z Production St, Sangandaan, Quezon City','Security Guard','50.00','54.00','jerrydelacruz766@gmail.com','2bd0a2ad94c1f4f7371057c92d03d68c','ZmN2WxiG','employee','Available',0,NULL,'07:51:12pm','2022/03/27'),(86,'2022-84','Clark James','Jimenez','09452749233','Sampaloc St. Camarin, Caloocan City',NULL,NULL,NULL,'kylaliamera10@gmail.com','ab573b8b1cb47ecb583f56268b9b2702','UsZ49UEz','employee','Available',0,NULL,'01:38:31pm','2022/03/29'),(87,'2022-86','Matthew','Pineda','09684235167','790 everlasting st. evergreen bagumbong caloocan city','Security Guard','50','40','matthewpineda.qcpu@gmail.com','dc0c4fe522e5943ed1de7675512c964e','cUUEDtSx','employee','Unavailable',0,NULL,'08:41:19pm','2022/03/29'),(88,'2022-87','John','Luna','09978675656','Calamansian St. Novaliches, Quezon City',NULL,NULL,NULL,'lunaJ@gmail.com','49db48761226a6aca040c6aaa492da40','vwpJmGn9','employee','Available',0,NULL,'12:49:20am','2022/03/30'),(90,'2022-88','KIko','Lopez','09060766219','Novaliches, Quezon City','Officer in Charge','66','40','zansample3@gmail.com','f6c825ae9723ded5c33fd2a206019b5d','aHzMDb7Q','employee','Unavailable',0,NULL,'01:58:47pm','2022/03/31'),(96,'2022-90','Les','Soriano','09060766219','78 Molave ST. Bahay Toro Quezon City','Inspector','66','00.00','lessoriano951@gmail.com','98857cf716edee26df8e54e61eadedbb','RiJUX90J','employee','Unavailable',0,NULL,'08:24:59pm','2022/04/01'),(97,'2022-96','Julie','Manalo','09685444830','#39 Purok 2 Luzon Avenue Barangay CuliaT Quezon City','Secretary','67.00','00.00','juliemanalo258@gmail.com','c4e6777eb0f61f7677ca59e5bd71bd69','xWMUS1Da','employee','Unavailable',0,NULL,'08:36:26pm','2022/04/01');
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `date_created` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
INSERT INTO `feedback` VALUES (23,'Luisito Cruz','Security Guard','Report Bug','Hindi po ako makapagtime-in ng attendance ko','2022/03/31'),(25,'Perla De Vera','Administrator','Report Bug','System crashes upon clicking the Submit button while creating a new employee','2022/04/01'),(27,'Julie Manalo','Secretary','Report Bug','Walang pdf for 13 month slip','2022/04/08'),(28,'Julie Manalo','Secretary','Suggestion','Improve design','2022/04/08');
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `date_holiday` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,'New Year’s Day ','January 1, 2022 ','Regular Holiday'),(2,'The Day of Valor','April 9, 2022','Regular Holiday'),(3,'Maundy Thursday','April 14, 2022','Regular Holiday'),(4,'Good Friday','April 15, 2022','Regular Holiday'),(5,'Labor Day','May 1, 2022','Regular Holiday'),(6,'Eid’l Fitr','May 3, 2022','Regular Holiday'),(7,'Independence Day','June 12, 2022','Regular Holiday'),(8,'National Heroes’ Day','August 29, 2022','Regular Holiday'),(9,'Bonifacio Day','November 30, 2022','Regular Holiday'),(10,'Christmas Day','December 25, 2022','Regular Holiday'),(11,'Rizal Day','December 30, 2022','Regular Holiday'),(12,'Chinese New Year','February 1, 2022','Special Holiday'),(13,'People Power Revolution','February 25, 2022','Special Holiday'),(14,'Black Saturday','April 16, 2022','Special Holiday'),(15,'Ninoy Aquino Day','August 21, 2022','Special Holiday'),(16,'All Saints’ Day','November 1, 2022','Special Holiday'),(17,'Immaculate Conception of Mary','December 8, 2022','Special Holiday'),(18,'All Souls’ Day','November 2, 2022','Special Holiday'),(19,'Christmas Eve','December 24, 2022','Special Holiday'),(20,'New Year’s Eve','December 31, 2022','Special Holiday');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;

--
-- Table structure for table `inbox`
--

DROP TABLE IF EXISTS `inbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inbox` (
  `id` varchar(255) NOT NULL,
  `empId` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `body` varchar(255) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `filenewname` varchar(255) DEFAULT NULL,
  `date_created` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `empId` (`empId`),
  CONSTRAINT `inbox_ibfk_1` FOREIGN KEY (`empId`) REFERENCES `employee` (`empId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inbox`
--

/*!40000 ALTER TABLE `inbox` DISABLE KEYS */;
INSERT INTO `inbox` VALUES ('6249a76b07e00','2022-76','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/03 09:55:55 PM','Unread'),('6249a76cee997','2022-78','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/03 09:55:56 PM','Unread'),('6249a76ec5e01','2022-80','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/03 09:55:58 PM','Unread'),('624a61cea07c9','2022-1','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/04 11:11:10 AM','Unread'),('624a61d0723cb','2022-77','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/04 11:11:12 AM','Unread'),('624a61d1f0a97','2022-79','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/04 11:11:13 AM','Unread'),('624a61d378e26','2022-86','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/04 11:11:15 AM','Unread'),('624a61d4f2e7f','2022-88','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/04 11:11:16 AM','Unread'),('624a61d6767d2','2022-90','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/04 11:11:18 AM','Unread'),('624a61d7e9760','2022-96','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/04 11:11:19 AM','Unread'),('624bd513c59d9','2022-1','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 01:35:15 PM','Unread'),('624bd51544678','2022-77','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 01:35:17 PM','Unread'),('624bd516c80ed','2022-79','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 01:35:18 PM','Unread'),('624bd5185e054','2022-86','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 01:35:20 PM','Unread'),('624bd519edfe8','2022-88','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 01:35:21 PM','Unread'),('624bd51b764ee','2022-90','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 01:35:23 PM','Unread'),('624bd51d185a5','2022-96','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 01:35:25 PM','Unread'),('624c0a71eaa85','2022-76','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 05:22:57 PM','Unread'),('624c0a73ce0ab','2022-78','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 05:22:59 PM','Unread'),('624c0a7568595','2022-80','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/05 05:23:01 PM','Unread'),('624d035aea641','2022-1','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 11:04:58 AM','Unread'),('624d035c6ffd9','2022-77','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 11:05:00 AM','Unread'),('624d035de3535','2022-79','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 11:05:01 AM','Unread'),('624d035f58160','2022-86','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 11:05:03 AM','Unread'),('624d0360bc2be','2022-88','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 11:05:04 AM','Unread'),('624d036235e1b','2022-90','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 11:05:06 AM','Unread'),('624d03639f50c','2022-96','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 11:05:07 AM','Unread'),('624d6b26efd42','2022-76','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 06:27:50 PM','Unread'),('624d6b28b1653','2022-78','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 06:27:52 PM','Unread'),('624d6b2a1da9d','2022-80','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/06 06:27:54 PM','Unread'),('624eca4d1e7ad','2022-76','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/07 07:26:05 PM','Unread'),('624eca4e9169e','2022-78','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/07 07:26:06 PM','Unread'),('624eca5002100','2022-80','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/07 07:26:08 PM','Unread'),('624feb8c365b6','2022-76','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/08 04:00:12 PM','Unread'),('624feb8e001ed','2022-78','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/08 04:00:14 PM','Unread'),('624feb8f5be87','2022-79','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/08 04:00:15 PM','Unread'),('624feb90b2356','2022-80','Marked as Absent','You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.\r\n                        \r\nIf you think that it is just a mistake, you may comply to our agency to solve this issue.',NULL,NULL,'2022/04/08 04:00:16 PM','Unread');
/*!40000 ALTER TABLE `inbox` ENABLE KEYS */;

--
-- Table structure for table `leave_request`
--

DROP TABLE IF EXISTS `leave_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(50) NOT NULL,
  `days` int(11) NOT NULL,
  `leave_start` varchar(50) NOT NULL,
  `leave_end` varchar(50) NOT NULL,
  `typeOfLeave` varchar(50) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `substitute_by` varchar(50) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `date_created` varchar(50) NOT NULL,
  `date_admin` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empId` (`empId`),
  KEY `substitute_by` (`substitute_by`),
  CONSTRAINT `leave_request_ibfk_1` FOREIGN KEY (`empId`) REFERENCES `employee` (`empId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `leave_request_ibfk_2` FOREIGN KEY (`substitute_by`) REFERENCES `employee` (`empId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_request`
--

/*!40000 ALTER TABLE `leave_request` DISABLE KEYS */;
INSERT INTO `leave_request` VALUES (12,'2022-78',4,'2022-03-27','2022-03-31','Maternity Leave','Maternity','2022-80','approved','2022/03/26','2022-02-23'),(23,'2022-1',3,'2022-04-01','2022-04-04','Emergency Leave','Im sick for 4 days',NULL,'rejected','2022/04/01','2022/04/01');
/*!40000 ALTER TABLE `leave_request` ENABLE KEYS */;

--
-- Table structure for table `maintenance`
--

DROP TABLE IF EXISTS `maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance`
--

/*!40000 ALTER TABLE `maintenance` DISABLE KEYS */;
INSERT INTO `maintenance` VALUES (1,'Head Manager',0),(2,'Secretary',0),(3,'Guards',0);
/*!40000 ALTER TABLE `maintenance` ENABLE KEYS */;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `overtime_rate` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (3,'Mcdonald\'s Sangandaan','Officer in Charge','55.00','64.00'),(4,'Mcdonald\'s Sangandaan','Security Guard','50.00','54.00'),(5,'Mcdonald','Officer in Charge','55.00','60.00'),(6,'Mcdonald','Security Guard','50.00','55.00'),(7,'Molave Drive','Officer in Charge','67.00','70.00'),(8,'Molave Drive','Security Guard','55.00','60.00'),(9,'Molave Drive','Head Finance','60.00','65.00'),(10,'Petron','Officer in Charge','50.00','55.00'),(11,'Petron','Security Guard','40.00','45.00'),(12,'BDO','Officer in Charge','50.00','40.00'),(14,'BDO','Security Guard','40.00','30.00'),(15,'Robinson Novaliches','Officer in Charge','66','40'),(16,'Robinson Novaliches','Security Guard','51','60'),(17,'JTDV Security Agency','Officer in Charge','00.00','0.00'),(18,'JTDV Security Agency','Secretary','67.00','00.00'),(20,'JTDV Security Agency','Head Finance','67.00','00.00'),(21,'Robinson Novaliches','Head Finance','75.00','80.00'),(22,'Clinic','Officer in Charge','25.00','56.00'),(23,'Clinic','Sample Position','23.00','25.00'),(24,'Clinic','New Position','55.00','35.00'),(25,'JTDV Security Agency','Officer Clerk','67.00','00.00'),(26,'JTDV Security Agency','Operation Manager','67.00','00.00'),(27,'JTDV Security Agency','Collector','67.00','00.00'),(28,'Magnolia Place','Officer in Charge','54.00','60.00'),(29,'Magnolia Place','Security Guard','50.00','54.00'),(32,'JTDV Security Agency','Inspector','67.00','00.00');
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;

--
-- Table structure for table `salary_report`
--

DROP TABLE IF EXISTS `salary_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(20) DEFAULT NULL,
  `january` float DEFAULT NULL,
  `february` float DEFAULT NULL,
  `march` float DEFAULT NULL,
  `april` float DEFAULT NULL,
  `may` float DEFAULT NULL,
  `june` float DEFAULT NULL,
  `july` float DEFAULT NULL,
  `august` float DEFAULT NULL,
  `september` float DEFAULT NULL,
  `october` float DEFAULT NULL,
  `november` float DEFAULT NULL,
  `december` float DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=351 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_report`
--

/*!40000 ALTER TABLE `salary_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_report` ENABLE KEYS */;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(100) DEFAULT NULL,
  `company` varchar(255) NOT NULL,
  `scheduleTimeIn` varchar(100) DEFAULT NULL,
  `scheduleTimeOut` varchar(100) DEFAULT NULL,
  `shift` varchar(100) DEFAULT NULL,
  `shift_span` int(11) DEFAULT NULL,
  `expiration_date` varchar(100) DEFAULT NULL,
  `date_assigned` date DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `schedule_ibfk_2` (`empId`),
  CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`company`) REFERENCES `company` (`company_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`empId`) REFERENCES `employee` (`empId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
INSERT INTO `schedule` VALUES (18,'2022-78','Molave Drive','02:00:00 PM','10:00:00 PM','Second Shift',8,'2024-03-25','2022-03-25'),(22,'2022-79','Petron','06:00 am','06:00 pm','Shift1',12,'2024-04-11','2022-03-26'),(25,'2022-80','Molave Drive','02:00:00 PM','10:00:00 PM','Second Shift',8,'2023-05-16','2022-03-28'),(33,'2022-88','Robinson Novaliches','06:00 am','02:00 pm','Shift1',8,'2024-01-11','2022-03-31'),(34,'2022-86','Robinson Novaliches','06:00:00 AM','02:00:00 PM','First Shift',8,'2024-01-11','2022-03-31'),(39,'2022-1','Magnolia Place','06:00 am','02:00 pm','Shift1',8,'2024-03-31','2022-03-31'),(40,'2022-77','Magnolia Place','06:00:00 AM','02:00:00 PM','First Shift',8,'2024-03-31','2022-03-31'),(41,'2022-76','Magnolia Place','02:00:00 PM','10:00:00 PM','Second Shift',8,'2024-03-31','2022-03-31'),(47,'2022-90','JTDV Security Agency','07:00 am','03:00 pm','Shift1',8,'2024-04-01','2022-04-01'),(48,'2022-96','JTDV Security Agency','07:00 am','03:00 pm','Shift1',8,'2024-04-01','2022-04-01');
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;

--
-- Table structure for table `secret_diary`
--

DROP TABLE IF EXISTS `secret_diary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secret_diary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sa_id` varchar(100) NOT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sa_id` (`sa_id`),
  CONSTRAINT `secret_diary_ibfk_1` FOREIGN KEY (`sa_id`) REFERENCES `super_admin` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secret_diary`
--

/*!40000 ALTER TABLE `secret_diary` DISABLE KEYS */;
INSERT INTO `secret_diary` VALUES (8,'deveraperla1@gmail.com',')d&rg%_7'),(9,'vonnesalig39@yahoo.com','r6&i7w1y');
/*!40000 ALTER TABLE `secret_diary` ENABLE KEYS */;

--
-- Table structure for table `secret_diarye`
--

DROP TABLE IF EXISTS `secret_diarye`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secret_diarye` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `e_id` varchar(100) NOT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `e_id` (`e_id`),
  CONSTRAINT `secret_diarye_ibfk_1` FOREIGN KEY (`e_id`) REFERENCES `employee` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secret_diarye`
--

/*!40000 ALTER TABLE `secret_diarye` DISABLE KEYS */;
INSERT INTO `secret_diarye` VALUES (11,'von39gaming@gmail.com','vonnedew'),(12,'von39gamingx@gmail.com','vonnedew'),(13,'vonnedewsalig@gmail.com','vonnedew'),(14,'mapatriciaclarisse.santos2001@gmail.com','isaymaganda'),(15,'francis.albert.sarcaoga.ilacad@gmail.com','drgb9sqk'),(16,'ghil.adam@gmail.com','cez@g^#6'),(19,'jerrydelacruz766@gmail.com','vonnedew'),(21,'kylaliamera10@gmail.com','sweetcorn'),(22,'matthewpineda.qcpu@gmail.com','#g4)bj1c'),(23,'lunaJ@gmail.com','2hjfi+bt'),(25,'zansample3@gmail.com','d82f#c9v'),(31,'lessoriano951@gmail.com','gv)9xd8e'),(32,'juliemanalo258@gmail.com','gqi#cer1');
/*!40000 ALTER TABLE `secret_diarye` ENABLE KEYS */;

--
-- Table structure for table `secret_diarys`
--

DROP TABLE IF EXISTS `secret_diarys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secret_diarys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `se_id` varchar(100) NOT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `se_id` (`se_id`),
  CONSTRAINT `secret_diarys_ibfk_1` FOREIGN KEY (`se_id`) REFERENCES `secretary` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secret_diarys`
--

/*!40000 ALTER TABLE `secret_diarys` DISABLE KEYS */;
INSERT INTO `secret_diarys` VALUES (11,'jm.julie.manalo@gmail.com','#oj%1bfh');
/*!40000 ALTER TABLE `secret_diarys` ENABLE KEYS */;

--
-- Table structure for table `secretary`
--

DROP TABLE IF EXISTS `secretary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secretary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `cpnumber` varchar(13) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `timer` varchar(50) DEFAULT NULL,
  `admin_id` int(11) NOT NULL,
  `access` varchar(100) DEFAULT NULL,
  `isDeleted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `secretary_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `super_admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secretary`
--

/*!40000 ALTER TABLE `secretary` DISABLE KEYS */;
INSERT INTO `secretary` VALUES (24,'Julie Manalo','Female','09685444830','#39 Purok 2 Luzon Avenue Barangay CuliaT Quezon City','jm.julie.manalo@gmail.com','b57e8c4739381403b7065b287505d547',NULL,11,'secretary',0);
/*!40000 ALTER TABLE `secretary` ENABLE KEYS */;

--
-- Table structure for table `secretary_log`
--

DROP TABLE IF EXISTS `secretary_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secretary_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sec_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `time` varchar(100) DEFAULT NULL,
  `date` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sec_id` (`sec_id`),
  CONSTRAINT `secretary_log_ibfk_1` FOREIGN KEY (`sec_id`) REFERENCES `secretary` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=223 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secretary_log`
--

/*!40000 ALTER TABLE `secretary_log` DISABLE KEYS */;
INSERT INTO `secretary_log` VALUES (207,24,'Julie Manalo','login','09:10:54 PM','2022/04/01'),(208,24,'Julie Manalo','Add Deduction','09:30:34 PM','2022/04/01'),(209,24,'Julie Manalo','Add Deduction','09:33:45 PM','2022/04/01'),(210,24,'Julie Manalo','Add Deduction','09:34:06 PM','2022/04/01'),(211,24,'Julie Manalo','Add Deduction','09:34:18 PM','2022/04/01'),(212,24,'Julie Manalo','Add Cash Advance','09:34:41 PM','2022/04/01'),(213,24,'Julie Manalo','Generate 4 Salary','09:35:02 PM','2022/04/01'),(214,24,'Julie Manalo','login','10:23:34 PM','2022/04/05'),(215,24,'Julie Manalo','Delete Deduction','10:23:46 PM','2022/04/05'),(216,24,'Julie Manalo','Delete Deduction','10:23:49 PM','2022/04/05'),(217,24,'Julie Manalo','Delete Deduction','10:23:50 PM','2022/04/05'),(218,24,'Julie Manalo','Add Deduction','10:23:59 PM','2022/04/05'),(219,24,'Julie Manalo','Generate 5 Salary','10:30:05 PM','2022/04/05'),(220,24,'Julie Manalo','login','01:38:53 PM','2022/04/08'),(221,24,'Julie Manalo','Generate 5 Salary','01:57:04 PM','2022/04/08'),(222,24,'Julie Manalo','login','04:11:54 PM','2022/04/08');
/*!40000 ALTER TABLE `secretary_log` ENABLE KEYS */;

--
-- Table structure for table `sss_table`
--

DROP TABLE IF EXISTS `sss_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sss_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salary_from` float NOT NULL,
  `salary_to` float NOT NULL,
  `er` float NOT NULL,
  `ee` float NOT NULL,
  `total` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sss_table`
--

/*!40000 ALTER TABLE `sss_table` DISABLE KEYS */;
INSERT INTO `sss_table` VALUES (1,1,2250,160,80,240),(2,2250,2749.99,200,100,300),(3,2750,3249.99,240,120,360),(4,3250,3749.99,280,140,420),(5,3750,4249.99,320,160,480),(6,4250,4749.99,360,180,540),(7,4750,5249.99,400,200,600),(8,5250,5749.99,440,220,660),(9,5750,6249.99,480,240,720),(10,6250,6749.99,520,260,780),(11,6750,7249.99,560,280,840),(12,7250,7749.99,600,300,900),(13,7750,8249.99,640,320,960),(14,8250,8749.9,680,240,1020),(15,8750,9249.99,720,360,1080),(16,9250,9749.99,760,380,1140),(17,9750,10250,800,400,1200),(18,10250,10750,840,420,1260),(19,10750,11250,880,440,1320),(20,11250,11750,920,460,1380),(21,11750,12250,960,480,1440),(22,12250,12750,1000,500,1500),(23,12750,13250,1040,520,1560),(24,13250,13750,1080,540,1620),(25,13750,14250,1120,560,1680),(26,14250,14750,1160,580,1740),(27,14750,15250,1200,600,1800),(28,15250,15750,1240,620,1860),(29,15750,16250,1280,640,1920),(30,16250,16750,1320,660,1980),(31,16750,17250,1360,680,2040),(32,17250,17750,1400,700,2100),(33,17750,18250,1440,720,2160),(34,18250,18750,1480,740,2220),(35,18750,19250,1520,760,2280),(36,19250,19750,1560,780,2340),(37,19750,50000,1600,800,2400);
/*!40000 ALTER TABLE `sss_table` ENABLE KEYS */;

--
-- Table structure for table `super_admin`
--

DROP TABLE IF EXISTS `super_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `super_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `cpnumber` varchar(13) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `google` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `timer` varchar(100) DEFAULT NULL,
  `access` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_admin`
--

/*!40000 ALTER TABLE `super_admin` DISABLE KEYS */;
INSERT INTO `super_admin` VALUES (11,'Perla','De Vera','41-X Production St, Quezon City','09572812480','deveraperla1@gmail.com','f87e1764ea6d18b3fcb3515414ae0d9c',NULL,NULL,NULL,NULL,NULL,'administrator'),(12,'Vonne Dew','Salig','15-F Hilda Village, Quezon City','09568570501','vonnesalig39@yahoo.com','0626aca4fb940053ce2d42f8dedf7105',NULL,NULL,NULL,NULL,NULL,'administrator');
/*!40000 ALTER TABLE `super_admin` ENABLE KEYS */;

--
-- Table structure for table `system_admin`
--

DROP TABLE IF EXISTS `system_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_admin`
--

/*!40000 ALTER TABLE `system_admin` DISABLE KEYS */;
INSERT INTO `system_admin` VALUES (1,'jtdv_administrator1','vonnedew'),(2,'jtdv_administrator2','admin');
/*!40000 ALTER TABLE `system_admin` ENABLE KEYS */;

--
-- Table structure for table `thirteenmonth`
--

DROP TABLE IF EXISTS `thirteenmonth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thirteenmonth` (
  `log` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(20) NOT NULL,
  `total_gross` float NOT NULL,
  `late` float NOT NULL,
  `amount` float DEFAULT NULL,
  `date_created` varchar(20) NOT NULL,
  PRIMARY KEY (`log`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thirteenmonth`
--

/*!40000 ALTER TABLE `thirteenmonth` DISABLE KEYS */;
/*!40000 ALTER TABLE `thirteenmonth` ENABLE KEYS */;

--
-- Table structure for table `uniform_penalty`
--

DROP TABLE IF EXISTS `uniform_penalty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uniform_penalty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `violation` varchar(50) NOT NULL,
  `amount` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uniform_penalty`
--

/*!40000 ALTER TABLE `uniform_penalty` DISABLE KEYS */;
INSERT INTO `uniform_penalty` VALUES (1,'uniform',100);
/*!40000 ALTER TABLE `uniform_penalty` ENABLE KEYS */;

--
-- Table structure for table `violationsandremarks`
--

DROP TABLE IF EXISTS `violationsandremarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violationsandremarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empId` varchar(100) NOT NULL,
  `violation` varchar(255) NOT NULL,
  `fine` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `date_created` varchar(100) NOT NULL,
  `paid` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empId` (`empId`),
  KEY `violationsandremarks_ibfk_2` (`remark`),
  CONSTRAINT `violationsandremarks_ibfk_1` FOREIGN KEY (`empId`) REFERENCES `employee` (`empId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `violationsandremarks_ibfk_2` FOREIGN KEY (`remark`) REFERENCES `inbox` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violationsandremarks`
--

/*!40000 ALTER TABLE `violationsandremarks` DISABLE KEYS */;
INSERT INTO `violationsandremarks` VALUES (37,'2022-76','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/03',NULL,NULL),(38,'2022-78','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/03',NULL,NULL),(39,'2022-80','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/03',NULL,NULL),(40,'2022-1','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/04',NULL,NULL),(41,'2022-77','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/04',NULL,NULL),(42,'2022-79','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/04',NULL,NULL),(43,'2022-86','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/04',NULL,NULL),(44,'2022-88','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/04',NULL,NULL),(45,'2022-90','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/04',NULL,NULL),(46,'2022-96','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/04',NULL,NULL),(47,'2022-1','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(48,'2022-77','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(49,'2022-79','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(50,'2022-86','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(51,'2022-88','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(52,'2022-90','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(53,'2022-96','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(54,'2022-76','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(55,'2022-78','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(56,'2022-80','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/05',NULL,NULL),(57,'2022-1','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(58,'2022-77','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(59,'2022-79','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(60,'2022-86','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(61,'2022-88','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(62,'2022-90','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(63,'2022-96','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(64,'2022-76','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(65,'2022-78','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(66,'2022-80','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/06',NULL,NULL),(67,'2022-76','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/07',NULL,NULL),(68,'2022-78','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/07',NULL,NULL),(69,'2022-80','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/07',NULL,NULL),(70,'2022-76','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/08',NULL,NULL),(71,'2022-78','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/08',NULL,NULL),(72,'2022-79','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/08',NULL,NULL),(73,'2022-80','Absent Without Official Leave (AWOL)',NULL,NULL,'2022/04/08',NULL,NULL);
/*!40000 ALTER TABLE `violationsandremarks` ENABLE KEYS */;

--
-- Dumping routines for database 'u359933141_payroll'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-04-15  1:36:19
