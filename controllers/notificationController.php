<?php
session_start();
include_once '../components/connection.php';

// Mark notification as read
if(isset($_POST['mark_read'])) {
    $notification_id = (int)$_POST['notification_id'];
    $user_id = (int)$_SESSION['user_id'];
    
    // Verify notification belongs to user
    $check_query = "SELECT notification_id FROM notifications WHERE notification_id = $notification_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $update_query = "UPDATE notifications SET is_read = 1 WHERE notification_id = $notification_id";
        if(mysqli_query($conn, $update_query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update notification']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Notification not found']);
    }
    exit();
}

// Mark all notifications as read
if(isset($_POST['mark_all_read'])) {
    $user_id = (int)$_SESSION['user_id'];
    
    $update_query = "UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0";
    if(mysqli_query($conn, $update_query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update notifications']);
    }
    exit();
}

// Get notifications (AJAX)
if(isset($_GET['get_notifications'])) {
    $user_id = (int)$_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    // Fetch notifications with entity titles
    $query = "SELECT n.*, 
                     CASE 
                         WHEN n.entity_type = 'event' THEN (SELECT title FROM events WHERE event_id = n.entity_id)
                         WHEN n.entity_type = 'announcement' THEN (SELECT title FROM announcements WHERE announce_id = n.entity_id)
                         WHEN n.entity_type = 'job' THEN (SELECT job_title FROM jobs WHERE job_id = n.entity_id)
                         WHEN n.entity_type = 'news' THEN (SELECT title FROM news WHERE news_id = n.entity_id)
                         WHEN n.entity_type = 'story' THEN (SELECT title FROM stories WHERE story_id = n.entity_id)
                         ELSE NULL
                     END as entity_title
              FROM notifications n 
              WHERE n.user_id = $user_id 
              ORDER BY n.created_at DESC 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $notifications = [];
    
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($notifications);
    exit();
}

// Get unread count (AJAX)
if(isset($_GET['get_unread_count'])) {
    $user_id = (int)$_SESSION['user_id'];
    
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = $user_id AND is_read = 0";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    header('Content-Type: application/json');
    echo json_encode(['count' => (int)$row['count']]);
    exit();
}
?>

