-- Create likes table
CREATE TABLE IF NOT EXISTS `likes` (
  `like_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'The user who liked',
  `entity_type` varchar(50) NOT NULL COMMENT 'event, announcement, job, news, story',
  `entity_id` int NOT NULL COMMENT 'ID of the entity (event_id, story_id, etc.)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`like_id`),
  UNIQUE KEY `unique_like` (`user_id`, `entity_type`, `entity_id`),
  KEY `entity_type_id` (`entity_type`, `entity_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;







