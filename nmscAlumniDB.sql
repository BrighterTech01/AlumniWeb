CREATE DATABASE  IF NOT EXISTS `nmsc` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `nmsc`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: nmsc
-- ------------------------------------------------------
-- Server version	8.0.42

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
-- Table structure for table `alumnus`
--

DROP TABLE IF EXISTS `alumnus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumnus` (
  `alumnus_id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(200) NOT NULL,
  `middlename` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `batch` year NOT NULL,
  `course_id` int DEFAULT NULL,
  `contact` varchar(225) NOT NULL,
  `image` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0= Unverified, 1= Verified',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `graduation_year` year DEFAULT NULL,
  PRIMARY KEY (`alumnus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnus`
--

LOCK TABLES `alumnus` WRITE;
/*!40000 ALTER TABLE `alumnus` DISABLE KEYS */;
INSERT INTO `alumnus` VALUES (57,'bryan','epong','barrientos','Male',2000,6,'09923622221','alumni_685426124489f7.36056000.jpg',1,'2025-06-19 17:00:34','mantic','tangub','philippines',2010),(58,'reynald','sd','abacial','Male',2000,6,'09922355545','alumni_68847d928a47d5.96871740.png',0,'2025-07-26 09:02:42','miss','mantic','philippines',2010);
/*!40000 ALTER TABLE `alumnus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `announce_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `attachment_url` varchar(255) DEFAULT NULL,
  `date_posted` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`announce_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (5,'asdad','adasdas','announcement_6857f8a8c9be61.50272467.jpg','2025-06-22 20:35:52','Active',47);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (6,'Bachelor of Science in Information Technology');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `schedule` date NOT NULL,
  `banner` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `location` varchar(45) NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (30,'asdasd','adasdas','2025-07-01','event_6854272eab9513.67875719.jpg','2025-06-19 15:05:18','sdada',47);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `job_id` int NOT NULL AUTO_INCREMENT,
  `job_title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `job_type` enum('Full-time','Part-time','Contract','Internship') DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `employee_status` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `application_deadline` date NOT NULL,
  `url` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `job_image` varchar(255) NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`job_id`),
  KEY `fk_jobs_user` (`user_id`),
  CONSTRAINT `fk_jobs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (15,'dasdasd','asdadasdasd',25000.00,'Part-time','asdasd','dasdasd','sdada','2025-07-03','http://localhost/alumni/admin/manage_jobs.php','2025-06-19 15:06:15','2025-06-19 15:06:15','job_6854276700a4c4.42268042.jpg',47);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `news_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `date_posted` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Published','Draft') DEFAULT 'Draft',
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`news_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `news_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (7,'sdasdas','asdasdas','news_6857f62aa61eb9.92859758.jpg','2025-06-22 20:25:14','Published',47);
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stories`
--

DROP TABLE IF EXISTS `stories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stories` (
  `story_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `vimage` varchar(255) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  PRIMARY KEY (`story_id`),
  KEY `fk_stories_user` (`user_id`),
  CONSTRAINT `fk_stories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stories`
--

LOCK TABLES `stories` WRITE;
/*!40000 ALTER TABLE `stories` DISABLE KEYS */;
INSERT INTO `stories` VALUES (13,47,'story_6857f63f1218a7.23098097.jpg','dasdasd','asdasdasd','2025-06-22 12:25:35','2025-06-22 12:25:35','image');
/*!40000 ALTER TABLE `stories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `testimonial_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `testimonial_text` text NOT NULL,
  `rating` tinyint DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`testimonial_id`),
  KEY `fk_user` (`user_id`),
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,1,'bryan epong barrientos','dasdasdasd',5,'rejected','2025-06-19 15:12:50','2025-06-22 12:22:38'),(2,1,'bryan epong barrientos','dasdasdasd',5,'approved','2025-06-19 15:17:49','2025-06-22 12:22:27'),(3,1,'bryan epong barrientos','dasdasdasd',5,'approved','2025-06-19 15:18:02','2025-06-22 12:22:23');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(200) NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `role` enum('Admin','Alumni') NOT NULL,
  `alumnus_id` int DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `fk_user_alumnus` (`alumnus_id`),
  CONSTRAINT `fk_user_alumnus` FOREIGN KEY (`alumnus_id`) REFERENCES `alumnus` (`alumnus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'bbbbb','$2y$10$kwKel5rIW0VjXixyfW.TtOK2bEmD5IQHLxCErXKIf9rt78mVy1dQa','bjay@gmail.com','Alumni',57),(47,'bryanjaja','$2y$10$kwKel5rIW0VjXixyfW.TtOK2bEmD5IQHLxCErXKIf9rt78mVy1dQa','bryan@gmail.com','Admin',57),(48,'onat123','$2y$10$RYlx.dEzzavaXgenqqhy0ukm0D6t8JV4zUCeU.6cYKWnut5Pdo7Oy','rizz21@gmail.com','Alumni',58);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_announcement_details`
--

DROP TABLE IF EXISTS `view_announcement_details`;
/*!50001 DROP VIEW IF EXISTS `view_announcement_details`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_announcement_details` AS SELECT 
 1 AS `announce_id`,
 1 AS `title`,
 1 AS `content`,
 1 AS `attachment_url`,
 1 AS `date_posted`,
 1 AS `status`,
 1 AS `user_id`,
 1 AS `username`,
 1 AS `user_email`,
 1 AS `role`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_event_alumni_details`
--

DROP TABLE IF EXISTS `view_event_alumni_details`;
/*!50001 DROP VIEW IF EXISTS `view_event_alumni_details`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_event_alumni_details` AS SELECT 
 1 AS `event_id`,
 1 AS `title`,
 1 AS `content`,
 1 AS `schedule`,
 1 AS `banner`,
 1 AS `event_created`,
 1 AS `location`,
 1 AS `user_id`,
 1 AS `username`,
 1 AS `user_email`,
 1 AS `role`,
 1 AS `alumnus_id`,
 1 AS `firstname`,
 1 AS `middlename`,
 1 AS `lastname`,
 1 AS `gender`,
 1 AS `batch`,
 1 AS `course_id`,
 1 AS `contact`,
 1 AS `image`,
 1 AS `status`,
 1 AS `alumnus_created`,
 1 AS `address`,
 1 AS `city`,
 1 AS `country`,
 1 AS `graduation_year`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_job_postings`
--

DROP TABLE IF EXISTS `view_job_postings`;
/*!50001 DROP VIEW IF EXISTS `view_job_postings`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_job_postings` AS SELECT 
 1 AS `job_id`,
 1 AS `job_title`,
 1 AS `description`,
 1 AS `salary`,
 1 AS `job_type`,
 1 AS `company_name`,
 1 AS `employee_status`,
 1 AS `location`,
 1 AS `application_deadline`,
 1 AS `url`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `job_image`,
 1 AS `user_id`,
 1 AS `username`,
 1 AS `user_email`,
 1 AS `role`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_news_details`
--

DROP TABLE IF EXISTS `view_news_details`;
/*!50001 DROP VIEW IF EXISTS `view_news_details`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_news_details` AS SELECT 
 1 AS `news_id`,
 1 AS `title`,
 1 AS `content`,
 1 AS `image_url`,
 1 AS `date_posted`,
 1 AS `status`,
 1 AS `user_id`,
 1 AS `username`,
 1 AS `user_email`,
 1 AS `role`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_stories_with_users`
--

DROP TABLE IF EXISTS `view_stories_with_users`;
/*!50001 DROP VIEW IF EXISTS `view_stories_with_users`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_stories_with_users` AS SELECT 
 1 AS `story_id`,
 1 AS `user_id`,
 1 AS `username`,
 1 AS `email`,
 1 AS `role`,
 1 AS `alumnus_id`,
 1 AS `vimage`,
 1 AS `title`,
 1 AS `description`,
 1 AS `media_type`,
 1 AS `created_at`,
 1 AS `updated_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_testimonials_full`
--

DROP TABLE IF EXISTS `view_testimonials_full`;
/*!50001 DROP VIEW IF EXISTS `view_testimonials_full`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_testimonials_full` AS SELECT 
 1 AS `testimonial_id`,
 1 AS `user_id`,
 1 AS `username`,
 1 AS `email`,
 1 AS `role`,
 1 AS `alumnus_id`,
 1 AS `full_name`,
 1 AS `gender`,
 1 AS `batch`,
 1 AS `contact`,
 1 AS `profile_image`,
 1 AS `address`,
 1 AS `city`,
 1 AS `country`,
 1 AS `graduation_year`,
 1 AS `course_name`,
 1 AS `testimonial_text`,
 1 AS `rating`,
 1 AS `testimonial_status`,
 1 AS `created_at`,
 1 AS `updated_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_user_details`
--

DROP TABLE IF EXISTS `view_user_details`;
/*!50001 DROP VIEW IF EXISTS `view_user_details`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_user_details` AS SELECT 
 1 AS `user_id`,
 1 AS `username`,
 1 AS `password`,
 1 AS `email`,
 1 AS `role`,
 1 AS `alumnus_id`,
 1 AS `firstname`,
 1 AS `middlename`,
 1 AS `lastname`,
 1 AS `gender`,
 1 AS `batch`,
 1 AS `graduation_year`,
 1 AS `contact`,
 1 AS `image`,
 1 AS `status`,
 1 AS `date_created`,
 1 AS `address`,
 1 AS `city`,
 1 AS `country`,
 1 AS `course_id`,
 1 AS `course_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `view_announcement_details`
--

/*!50001 DROP VIEW IF EXISTS `view_announcement_details`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_announcement_details` AS select `a`.`announce_id` AS `announce_id`,`a`.`title` AS `title`,`a`.`content` AS `content`,`a`.`attachment_url` AS `attachment_url`,`a`.`date_posted` AS `date_posted`,`a`.`status` AS `status`,`a`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `user_email`,`u`.`role` AS `role` from (`announcements` `a` left join `users` `u` on((`a`.`user_id` = `u`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_event_alumni_details`
--

/*!50001 DROP VIEW IF EXISTS `view_event_alumni_details`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_event_alumni_details` AS select `e`.`event_id` AS `event_id`,`e`.`title` AS `title`,`e`.`content` AS `content`,`e`.`schedule` AS `schedule`,`e`.`banner` AS `banner`,`e`.`date_created` AS `event_created`,`e`.`location` AS `location`,`u`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `user_email`,`u`.`role` AS `role`,`a`.`alumnus_id` AS `alumnus_id`,`a`.`firstname` AS `firstname`,`a`.`middlename` AS `middlename`,`a`.`lastname` AS `lastname`,`a`.`gender` AS `gender`,`a`.`batch` AS `batch`,`a`.`course_id` AS `course_id`,`a`.`contact` AS `contact`,`a`.`image` AS `image`,`a`.`status` AS `status`,`a`.`date_created` AS `alumnus_created`,`a`.`address` AS `address`,`a`.`city` AS `city`,`a`.`country` AS `country`,`a`.`graduation_year` AS `graduation_year` from ((`events` `e` left join `users` `u` on((`e`.`user_id` = `u`.`user_id`))) left join `alumnus` `a` on((`u`.`alumnus_id` = `a`.`alumnus_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_job_postings`
--

/*!50001 DROP VIEW IF EXISTS `view_job_postings`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_job_postings` AS select `j`.`job_id` AS `job_id`,`j`.`job_title` AS `job_title`,`j`.`description` AS `description`,`j`.`salary` AS `salary`,`j`.`job_type` AS `job_type`,`j`.`company_name` AS `company_name`,`j`.`employee_status` AS `employee_status`,`j`.`location` AS `location`,`j`.`application_deadline` AS `application_deadline`,`j`.`url` AS `url`,`j`.`created_at` AS `created_at`,`j`.`updated_at` AS `updated_at`,`j`.`job_image` AS `job_image`,`j`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `user_email`,`u`.`role` AS `role` from (`jobs` `j` left join `users` `u` on((`j`.`user_id` = `u`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_news_details`
--

/*!50001 DROP VIEW IF EXISTS `view_news_details`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_news_details` AS select `n`.`news_id` AS `news_id`,`n`.`title` AS `title`,`n`.`content` AS `content`,`n`.`image_url` AS `image_url`,`n`.`date_posted` AS `date_posted`,`n`.`status` AS `status`,`n`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `user_email`,`u`.`role` AS `role` from (`news` `n` left join `users` `u` on((`n`.`user_id` = `u`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_stories_with_users`
--

/*!50001 DROP VIEW IF EXISTS `view_stories_with_users`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_stories_with_users` AS select `s`.`story_id` AS `story_id`,`s`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`role` AS `role`,`u`.`alumnus_id` AS `alumnus_id`,`s`.`vimage` AS `vimage`,`s`.`title` AS `title`,`s`.`description` AS `description`,`s`.`media_type` AS `media_type`,`s`.`created_at` AS `created_at`,`s`.`updated_at` AS `updated_at` from (`stories` `s` join `users` `u` on((`s`.`user_id` = `u`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_testimonials_full`
--

/*!50001 DROP VIEW IF EXISTS `view_testimonials_full`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_testimonials_full` AS select `t`.`testimonial_id` AS `testimonial_id`,`t`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`role` AS `role`,`a`.`alumnus_id` AS `alumnus_id`,concat(`a`.`firstname`,' ',`a`.`middlename`,' ',`a`.`lastname`) AS `full_name`,`a`.`gender` AS `gender`,`a`.`batch` AS `batch`,`a`.`contact` AS `contact`,`a`.`image` AS `profile_image`,`a`.`address` AS `address`,`a`.`city` AS `city`,`a`.`country` AS `country`,`a`.`graduation_year` AS `graduation_year`,`c`.`name` AS `course_name`,`t`.`testimonial_text` AS `testimonial_text`,`t`.`rating` AS `rating`,`t`.`status` AS `testimonial_status`,`t`.`created_at` AS `created_at`,`t`.`updated_at` AS `updated_at` from (((`testimonials` `t` join `users` `u` on((`t`.`user_id` = `u`.`user_id`))) left join `alumnus` `a` on((`u`.`alumnus_id` = `a`.`alumnus_id`))) left join `courses` `c` on((`a`.`course_id` = `c`.`course_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_user_details`
--

/*!50001 DROP VIEW IF EXISTS `view_user_details`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_user_details` AS select `u`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`password` AS `password`,`u`.`email` AS `email`,`u`.`role` AS `role`,`a`.`alumnus_id` AS `alumnus_id`,`a`.`firstname` AS `firstname`,`a`.`middlename` AS `middlename`,`a`.`lastname` AS `lastname`,`a`.`gender` AS `gender`,`a`.`batch` AS `batch`,`a`.`graduation_year` AS `graduation_year`,`a`.`contact` AS `contact`,`a`.`image` AS `image`,`a`.`status` AS `status`,`a`.`date_created` AS `date_created`,`a`.`address` AS `address`,`a`.`city` AS `city`,`a`.`country` AS `country`,`c`.`course_id` AS `course_id`,`c`.`name` AS `course_name` from ((`users` `u` left join `alumnus` `a` on((`u`.`alumnus_id` = `a`.`alumnus_id`))) left join `courses` `c` on((`a`.`course_id` = `c`.`course_id`))) */;
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

-- Dump completed on 2025-08-17 13:08:18
