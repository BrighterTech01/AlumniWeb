# Shares System Setup

## Database Setup

To enable the share functionality, you need to create the shares table in your database.

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database (usually `nmsc`)
3. Go to the SQL tab
4. Copy and paste the contents of `create_shares_table.sql`
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
```bash
mysql -u root -p nmsc < create_shares_table.sql
```

### Option 3: Manual Execution
Run the SQL in `create_shares_table.sql` directly in your MySQL client.

## SQL Command (Quick Copy)

```sql
CREATE TABLE IF NOT EXISTS `shares` (
  `share_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'The user who shared',
  `entity_type` varchar(50) NOT NULL COMMENT 'event, announcement, job, news, story',
  `entity_id` int NOT NULL COMMENT 'ID of the entity being shared',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`share_id`),
  UNIQUE KEY `unique_share` (`user_id`, `entity_type`, `entity_id`),
  KEY `entity_type_id` (`entity_type`, `entity_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `shares_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

## Features

- **Share to Account**: Users can share posts to their own account
- **Visual Feedback**: Shared posts show the share button in green
- **Unshare**: Users can unshare posts they've previously shared
- **Prevent Duplicates**: Unique constraint prevents users from sharing the same post twice
- **Auto-cleanup**: Shares are automatically deleted when a user is deleted (CASCADE)

## How It Works

1. When a user clicks the Share button, an AJAX request is sent to `shareController.php`
2. The controller checks if the user has already shared the post
3. If not shared, it adds a share record; if already shared, it removes it
4. The share button state is updated in real-time (green if shared, gray if not)
5. The page will work even if the table doesn't exist (graceful degradation)







