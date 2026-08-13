# Likes System Setup

## Database Setup

To enable the like functionality, you need to create the likes table in your database.

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database (usually `nmsc`)
3. Go to the SQL tab
4. Copy and paste the contents of `create_likes_table.sql`
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
```bash
mysql -u root -p nmsc < create_likes_table.sql
```

### Option 3: Manual Execution
Run the SQL in `create_likes_table.sql` directly in your MySQL client.

## SQL Command (Quick Copy)

```sql
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
```

## Features

- **Like/Unlike Posts**: Users can like and unlike stories, events, jobs, news, and announcements
- **Like Count Display**: Shows the number of likes on each post
- **Visual Feedback**: Liked posts are highlighted in blue
- **Prevent Duplicates**: Unique constraint prevents users from liking the same post twice
- **Auto-cleanup**: Likes are automatically deleted when a user is deleted (CASCADE)

## How It Works

1. When a user clicks the Like button, an AJAX request is sent to `likeController.php`
2. The controller checks if the user has already liked the post
3. If not liked, it adds a like record; if already liked, it removes it
4. The like count and button state are updated in real-time
5. The page will work even if the table doesn't exist (graceful degradation)







