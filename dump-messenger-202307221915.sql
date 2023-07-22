-- MySQL dump 10.13  Distrib 8.0.11, for Win64 (x86_64)
--
-- Host: localhost    Database: messenger
-- ------------------------------------------------------
-- Server version	8.0.11

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 SET NAMES utf8mb4 ;
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
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chat` (
  `chat_id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_type` varchar(10) NOT NULL,
  `chat_name` varchar(30) DEFAULT NULL,
  `chat_creatorid` int(11) DEFAULT NULL,
  PRIMARY KEY (`chat_id`),
  KEY `check_creatorid` (`chat_creatorid`),
  CONSTRAINT `check_creatorid` FOREIGN KEY (`chat_creatorid`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
INSERT INTO `chat` VALUES (1,'dialog',NULL,1),(2,'dialog',NULL,1),(3,'discussion','Групповой чат 11',1),(4,'dialog',NULL,1),(5,'dialog',NULL,2),(6,'dialog',NULL,3),(7,'discussion','Групповой чат 12',1);
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_message`
--

DROP TABLE IF EXISTS `chat_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chat_message` (
  `chat_message_id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_message_chatid` int(11) DEFAULT NULL,
  `chat_message_text` text NOT NULL,
  `chat_message_creatorid` int(11) DEFAULT NULL,
  `chat_message_time` datetime DEFAULT NULL,
  `chat_message_forward` int(1) DEFAULT '0',
  PRIMARY KEY (`chat_message_id`),
  KEY `check_message_chatid` (`chat_message_chatid`),
  KEY `check_message_creator` (`chat_message_creatorid`),
  CONSTRAINT `check_message_chatid` FOREIGN KEY (`chat_message_chatid`) REFERENCES `chat` (`chat_id`) ON DELETE CASCADE,
  CONSTRAINT `check_message_creator` FOREIGN KEY (`chat_message_creatorid`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_message`
--

LOCK TABLES `chat_message` WRITE;
/*!40000 ALTER TABLE `chat_message` DISABLE KEYS */;
INSERT INTO `chat_message` VALUES (3,2,'два',2,'2023-07-22 09:41:35',0),(5,3,'аладсер',2,'2023-07-22 10:04:44',0),(6,3,'админ',1,'2023-07-22 10:04:54',0),(9,1,'от Аладсера Админу',2,'2023-07-22 11:42:58',0),(10,1,'от Админа Аладсеру',1,'2023-07-22 11:43:26',0),(11,5,'от Аласдера яндексу',2,'2023-07-22 11:43:45',0),(18,4,'от админа лаутексу',1,'2023-07-22 12:16:22',0),(19,4,'от лаутекса админу',3,'2023-07-22 12:16:45',0),(20,6,'от админа лаутексу',3,'2023-07-22 12:17:03',1),(21,6,'от лаутекса админу',3,'2023-07-22 12:17:06',1),(22,2,'от Аладсера Админу',1,'2023-07-22 12:17:22',1),(23,2,'от админа лаутексу',1,'2023-07-22 12:17:25',1);
/*!40000 ALTER TABLE `chat_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_participant`
--

DROP TABLE IF EXISTS `chat_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chat_participant` (
  `chat_participant_chatid` int(11) NOT NULL,
  `chat_participant_userid` int(11) NOT NULL,
  `chat_participant_isnotice` int(1) DEFAULT '1',
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
INSERT INTO `chat_participant` VALUES (1,1,1),(1,2,1),(2,1,0),(2,33,1),(3,1,0),(3,2,1),(4,1,1),(4,3,1),(5,2,1),(5,33,1),(6,3,1),(6,4,1),(7,1,1),(7,2,1),(7,3,1),(7,33,1);
/*!40000 ALTER TABLE `chat_participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connections`
--

DROP TABLE IF EXISTS `connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `connections` (
  `connection_ws_id` int(11) NOT NULL,
  `connection_userid` int(11) DEFAULT NULL,
  PRIMARY KEY (`connection_ws_id`),
  UNIQUE KEY `connection_userid` (`connection_userid`),
  CONSTRAINT `fk_userid` FOREIGN KEY (`connection_userid`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connections`
--

LOCK TABLES `connections` WRITE;
/*!40000 ALTER TABLE `connections` DISABLE KEYS */;
/*!40000 ALTER TABLE `connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `contacts` (
  `cnt_id` int(11) NOT NULL AUTO_INCREMENT,
  `cnt_user_id` int(11) NOT NULL,
  `cnt_contact_id` int(11) NOT NULL,
  PRIMARY KEY (`cnt_id`),
  KEY `contacts_fk_userid` (`cnt_user_id`),
  KEY `contacts_fk_contactid` (`cnt_contact_id`),
  CONSTRAINT `contacts_fk_contactid` FOREIGN KEY (`cnt_contact_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `contacts_fk_userid` FOREIGN KEY (`cnt_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (1,1,2),(2,1,33),(3,1,3),(4,2,33),(5,3,4);
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `extended_chat`
--

DROP TABLE IF EXISTS `extended_chat`;
/*!50001 DROP VIEW IF EXISTS `extended_chat`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `extended_chat` AS SELECT 
 1 AS `chat_id`,
 1 AS `chat_type`,
 1 AS `chat_participant_userid`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `unhidden_emails`
--

DROP TABLE IF EXISTS `unhidden_emails`;
/*!50001 DROP VIEW IF EXISTS `unhidden_emails`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `unhidden_emails` AS SELECT 
 1 AS `user_email`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(100) DEFAULT NULL,
  `user_nickname` varchar(100) DEFAULT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_hash` varchar(255) DEFAULT NULL,
  `user_email_confirmed` tinyint(1) DEFAULT '0',
  `user_hide_email` int(1) DEFAULT '0',
  `user_photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  UNIQUE KEY `user_nickname` (`user_nickname`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'aladser@mail.ru','Admin','$2y$10$DkDtoYVWxAEt/ey/PcniT.58/N2BwI6CkTmmMMP2r7/uw0DkHvZgi',NULL,1,1,'aladser@mail.ru.1.jpg'),(2,'aladser@gmail.com','Aladser','$2y$10$K0n3aKnEGuxi1gyly1tBLebxpUFVHnqfG0KD2a8hen.XgB/EcPmZ6',NULL,1,1,'aladser@gmail.com.1.png'),(3,'lauxtec@gmail.com','Lauxtec','$2y$10$P1Gq37vuwOOQIh2t2zqvtuMEaa9FCRlLVczpAnJZ0wg0PTW1LNp/2',NULL,1,1,'lauxtec@gmail.com.1.jpeg'),(4,'sendlyamobile@gmail.com','Evgensha','$2y$10$io9DqPPtXKSDgADMw4tBge5YYzywhMc/rTMDiMhmo04yxgoyaEmGW',NULL,1,0,'sendlyamobile@gmail.com.2.jpg'),(5,'denisdyo17@gmail.com','','$2y$10$5ujTi/UqGu4atpGR2yflWub07R/V1kKHe86JwWZMwdVmH7XtLLy4i',NULL,1,0,'ava_profile.png'),(33,'aladser@yandex.ru',NULL,'$2y$10$g/XJNVvdA.ktTP2Ccq/.EeoazGyjPqAyeBqHoiFTwRlcLQQOkb2O6','cc9800a9f8b14a2c19058f89e9f7872f',1,0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'messenger'
--
/*!50003 DROP FUNCTION IF EXISTS `getPublicUserName` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `getPublicUserName`( email varchar(100), nickname varchar(100), hide_email int(1) ) RETURNS varchar(100) CHARSET utf8mb4
    DETERMINISTIC
begin
	IF hide_email = 1 THEN
		return nickname;
	ELSE
	   	return email;
	END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_forwarded_message` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `add_forwarded_message`(
	in msg_creatorid int,
	in message_id int,
	in chat_id int,
	in msg_time datetime,
	out new_msg_id  int
)
begin
	# копируем сообщение
	insert into chat_message(chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time)
	select chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time from chat_message
	where chat_message_id = message_id;

	select last_insert_id() into new_msg_id;

	# обновляем чат и время строки
	update chat_message set chat_message_chatid = chat_id, chat_message_time = msg_time, chat_message_creatorid = msg_creatorid, chat_message_forward = 1 where chat_message_id = new_msg_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `create_dialog` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `create_dialog`(
	in user1 int,
	in user2 int,
	out chatid int
)
begin
	insert into chat(chat_type, chat_creatorid) values('dialog', user1);
	select last_insert_id() into chatid;
	insert into chat_participant(chat_participant_chatid, chat_participant_userid) values(chatid, user1), (chatid, user2);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `create_discussion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `create_discussion`(
	in userhost int,
	out chatid int
)
begin
	insert into chat(chat_type, chat_creatorid) values('discussion', userhost);
	select last_insert_id() into chatid;	
	insert into chat_participant(chat_participant_chatid, chat_participant_userid) values(chatid, userhost); 		 # пользователи чата
	select count(*) into @count from chat where chat_creatorid = userhost and chat_type = 'discussion';						  	# номер групповго чата пользователя
	update chat set chat_name = concat('Групповой чат ', userhost, @count) where chat_id = chatid; # название группового чата
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `extended_chat`
--

/*!50001 DROP VIEW IF EXISTS `extended_chat`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `extended_chat` AS select `chat`.`chat_id` AS `chat_id`,`chat`.`chat_type` AS `chat_type`,`chat_participant`.`chat_participant_userid` AS `chat_participant_userid` from (`chat` join `chat_participant` on((`chat`.`chat_id` = `chat_participant`.`chat_participant_chatid`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

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
/*!50013 DEFINER=`admin`@`%` SQL SECURITY DEFINER */
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

-- Dump completed on 2023-07-22 19:15:35
