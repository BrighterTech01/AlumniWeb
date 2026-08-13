# Notifications System Setup

## Database Setup

To enable the notification system, you need to create the notifications table in your database.

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database (usually `nmsc`)
3. Go to the SQL tab
4. Copy and paste the contents of `create_notifications_table.sql`
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
```bash
mysql -u root -p nmsc < create_notifications_table.sql
```

### Option 3: Manual Execution
Run the SQL in `create_notifications_table.sql` directly in your MySQL client.

## Features

- **Comment Notifications**: Admins receive notifications when someone comments on their events
- **Reply Notifications**: Admins receive notifications when someone replies to comments on their events
- **Real-time Badge**: Unread notification count displayed in the header
- **Mark as Read**: Click on a notification to mark it as read and view the event
- **Mark All as Read**: Option to mark all notifications as read at once

## How It Works

1. When an Alumni comments on an event, the event author (Admin) receives a notification
2. When someone replies to a comment, both the parent comment author and event author receive notifications
3. Notifications are displayed in a dropdown in the admin header
4. Clicking a notification marks it as read and opens the event view modal

