-- MySQL dump 10.13  Distrib 8.0.35, for Linux (x86_64)
--
-- Host: localhost    Database: messenger
-- ------------------------------------------------------
-- Server version	8.0.35-0ubuntu0.22.04.1

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
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat` (
  `chat_id` int NOT NULL AUTO_INCREMENT,
  `chat_type` varchar(10) NOT NULL,
  `chat_name` varchar(30) DEFAULT NULL,
  `chat_creatorid` int DEFAULT NULL,
  PRIMARY KEY (`chat_id`),
  KEY `check_creatorid` (`chat_creatorid`),
  CONSTRAINT `check_creatorid` FOREIGN KEY (`chat_creatorid`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
INSERT INTO `chat` VALUES (84,'dialog',NULL,10),(85,'dialog',NULL,10),(88,'dialog',NULL,10),(92,'dialog',NULL,10),(93,'dialog',NULL,24),(107,'dialog',NULL,24),(112,'dialog',NULL,31),(141,'dialog',NULL,24),(145,'dialog',NULL,24),(149,'dialog',NULL,24),(160,'dialog',NULL,38),(163,'dialog',NULL,38),(166,'dialog',NULL,10);
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `check_chat_type` BEFORE INSERT ON `chat` FOR EACH ROW begin
    IF NEW.chat_type not in ('dialog', 'discussion') then
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'chat_type не равен dialog или discussion';
    END if;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `chat_message`
--

DROP TABLE IF EXISTS `chat_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_message` (
  `chat_message_id` int NOT NULL AUTO_INCREMENT,
  `chat_message_chatid` int DEFAULT NULL,
  `chat_message_text` text NOT NULL,
  `chat_message_creatorid` int DEFAULT NULL,
  `chat_message_time` datetime DEFAULT NULL,
  `chat_message_forward` int DEFAULT '0',
  PRIMARY KEY (`chat_message_id`),
  KEY `check_message_chatid` (`chat_message_chatid`),
  KEY `check_message_creator` (`chat_message_creatorid`),
  CONSTRAINT `check_message_chatid` FOREIGN KEY (`chat_message_chatid`) REFERENCES `chat` (`chat_id`) ON DELETE CASCADE,
  CONSTRAINT `check_message_creator` FOREIGN KEY (`chat_message_creatorid`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=782 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_message`
--

LOCK TABLES `chat_message` WRITE;
/*!40000 ALTER TABLE `chat_message` DISABLE KEYS */;
INSERT INTO `chat_message` VALUES (324,84,'На растрескавшейся земле',10,'2023-12-04 13:42:55',0),(326,84,'Город в дорожной петлей',10,'2023-12-04 13:43:15',0),(383,112,'мне кажется, что мы давно не живы',31,'2023-12-23 11:28:31',0),(598,88,'песен еще не написано?',24,'2023-12-30 05:44:56',0),(599,88,'сколько?',10,'2023-12-30 05:45:05',0),(600,88,'скажи, кукушка',24,'2023-12-30 05:45:12',0),(608,88,'мне кажется, что мы давно не живы',10,'2023-12-30 06:10:32',1),(679,112,'пять',10,'2023-12-31 01:12:31',0),(691,107,'админ барашке',24,'2023-12-31 03:21:35',0),(692,88,'барашке',24,'2023-12-31 03:21:45',1),(701,88,'с праздником',24,'2023-12-31 03:58:26',0),(702,88,'спасибо. тебя тоже',10,'2023-12-31 03:58:34',0),(753,107,'цвет настроения',31,'2024-01-02 21:57:56',0),(754,107,'синий',31,'2024-01-02 21:58:12',0),(760,145,'1',24,'2024-01-02 23:30:40',0),(765,163,'222',32,'2024-01-02 23:53:40',0),(770,163,'раз',38,'2024-01-03 00:03:35',0),(771,107,'мне кажется, что мы давно не живы',31,'2024-01-03 00:33:08',1);
/*!40000 ALTER TABLE `chat_message` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `check_message` BEFORE INSERT ON `chat_message` FOR EACH ROW BEGIN
    if new.chat_message_creatorid not in (select chat_participant_userid
                                          from chat_participant
                                          where chat_participant_chatid = new.chat_message_chatid) then
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'пользователя нет в данном чате';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `chat_participant`
--

DROP TABLE IF EXISTS `chat_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_participant` (
  `chat_participant_chatid` int NOT NULL,
  `chat_participant_userid` int NOT NULL,
  `chat_participant_isnotice` int DEFAULT '1',
  PRIMARY KEY (`chat_participant_chatid`,`chat_participant_userid`),
  KEY `check_participant_userid` (`chat_participant_userid`),
  CONSTRAINT `check_participant_chatid` FOREIGN KEY (`chat_participant_chatid`) REFERENCES `chat` (`chat_id`) ON DELETE CASCADE,
  CONSTRAINT `check_participant_userid` FOREIGN KEY (`chat_participant_userid`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_participant`
--

LOCK TABLES `chat_participant` WRITE;
/*!40000 ALTER TABLE `chat_participant` DISABLE KEYS */;
INSERT INTO `chat_participant` VALUES (84,10,1),(85,10,1),(88,10,1),(88,24,1),(92,10,1),(93,24,1),(107,24,1),(107,31,1),(112,10,1),(112,31,1),(141,24,1),(145,24,1),(145,32,1),(149,24,1),(160,24,1),(160,38,1),(163,32,1),(163,38,1),(166,10,1),(166,32,1);
/*!40000 ALTER TABLE `chat_participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `cnt_id` int NOT NULL AUTO_INCREMENT,
  `cnt_user_id` int NOT NULL,
  `cnt_contact_id` int NOT NULL,
  PRIMARY KEY (`cnt_id`),
  KEY `contacts_fk_userid` (`cnt_user_id`),
  KEY `contacts_fk_contactid` (`cnt_contact_id`),
  CONSTRAINT `contacts_fk_contactid` FOREIGN KEY (`cnt_contact_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `contacts_fk_userid` FOREIGN KEY (`cnt_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (55,10,24),(69,24,31),(72,31,10),(102,24,32),(113,38,24),(115,38,32),(116,10,32);
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `unhidden_emails`
--

DROP TABLE IF EXISTS `unhidden_emails`;
/*!50001 DROP VIEW IF EXISTS `unhidden_emails`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `unhidden_emails` AS SELECT 
 1 AS `user_email`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_email` varchar(100) DEFAULT NULL,
  `user_nickname` varchar(100) DEFAULT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_hash` varchar(255) DEFAULT NULL,
  `user_email_confirmed` tinyint(1) DEFAULT '0',
  `user_hide_email` int DEFAULT '0',
  `user_photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  UNIQUE KEY `user_nickname` (`user_nickname`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (10,'aladser@gmail.com','Aladser','$2y$10$Hh1BOVuvGZqj5dsnk9bO9ebWzOcIgLPLAQ7ckanQfNUbMB4LENnvy',NULL,1,1,'aladser@gmail.com.1.png'),(24,'aladser@mail.ru','Admin','$2y$10$ZYtRBbLJ0l.i5J15KHGefuEWCPjh/m5XbNUPbsThp3tBBT9fHmlFG','a4838203911256e139ca3fe4f0324bcf',1,1,'aladser@mail.ru.1.png'),(31,'senddlyamobille@gmail.com','Barashka','$2y$10$gt//WiMm6fHWt7sfeINhuOhCwhYIt.9AWHxsPTzw7.xfqEjLswwPK','944ecaee2415732e4d8c08e5ba87b8ab',0,1,'senddlyamobille@gmail.com.1.jpg'),(32,'lauxtec@gmail.com','Lauxtec','$2y$10$MNhfn4QS.FZcRGEU8wOyvOpItmU22Kf9xzFlTbfti9sY.5.9ZFLCO','4b2b536558125978eadb54a13d590720',0,1,'lauxtec@gmail.com.2.jpg'),(38,'aladser@yandex.ru','Yandex','$2y$10$Q2.unralb2KAdXE8e0CUEOQyepBaiPRCaPLUE2ZGkE8AySMcoUGNy',NULL,1,1,'aladser@yandex.ru.1.jpg');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `unhidden_emails`
--

/*!50001 DROP VIEW IF EXISTS `unhidden_emails`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `unhidden_emails` AS select `users`.`user_email` AS `user_email` from `users` where (`users`.`user_hide_email` = 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-01-03 10:19:39
