-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'The user who should receive this notification',
  `entity_type` varchar(50) NOT NULL COMMENT 'event, announcement, job, news, story',
  `entity_id` int NOT NULL COMMENT 'ID of the entity (event_id, etc.)',
  `comment_id` int DEFAULT NULL COMMENT 'ID of the comment that triggered this notification',
  `notification_type` enum('comment','reply') NOT NULL COMMENT 'Type of notification',
  `message` text NOT NULL COMMENT 'Notification message',
  `is_read` tinyint(1) DEFAULT 0 COMMENT '0 = unread, 1 = read',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `entity_type_id` (`entity_type`, `entity_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

