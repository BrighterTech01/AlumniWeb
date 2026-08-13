<?php
session_start();
include_once '../components/connection.php';

// Create Event Function
if(isset($_POST['create_event'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    // Get form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $schedule = mysqli_real_escape_string($conn, $_POST['schedule']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $user_id = (int)$_SESSION['user_id'];

    // Validate required fields
    if(empty($title) || empty($content) || empty($schedule) || empty($location)) {
        header('location: ../admin/manage_events.php?error=missing_fields');
        exit();
    }
    
    // Handle banner upload
    $banner = 'default.jpg';
    if(isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/events/";
        
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = $_FILES['banner']['name'];
        $file_tmp = $_FILES['banner']['tmp_name'];
        $file_size = $_FILES['banner']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('event_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    $banner = $new_filename;
                }
            }
        }
    }
    
    // Insert into events table
    $query = "INSERT INTO events (
        title, content, schedule, banner, date_created, location, user_id
    ) VALUES (
        '$title', '$content', '$schedule', '$banner', NOW(), '$location', $user_id
    )";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        // Determine redirect based on referer or user role
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
        $is_from_admin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'manage_events.php') !== false;
        
        if($is_admin && $is_from_admin) {
            header('location: ../admin/manage_events.php?success=1');
        } else {
            header('location: ../events.php?success=1');
        }
        exit();
    } else {
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
        $is_from_admin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'manage_events.php') !== false;
        
        if($is_admin && $is_from_admin) {
            header('location: ../admin/manage_events.php?error=event_creation_failed');
        } else {
            header('location: ../events.php?error=event_creation_failed');
        }
        exit();
    }
}

// Update Event Function
elseif (isset($_POST['update_event'])) {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $schedule = mysqli_real_escape_string($conn, $_POST['schedule']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    
    // Get current banner
    $current_banner_query = "SELECT banner FROM events WHERE event_id = '$event_id'";
    $current_banner_result = mysqli_query($conn, $current_banner_query);
    $current_banner_row = mysqli_fetch_assoc($current_banner_result);
    $current_banner = $current_banner_row['banner'];
    
    // Handle banner upload
    $banner = $current_banner; // Keep current banner by default
    if(isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/events/";
        
        $file_name = $_FILES['banner']['name'];
        $file_tmp = $_FILES['banner']['tmp_name'];
        $file_size = $_FILES['banner']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('event_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    // Delete old banner if it's not the default
                    if($current_banner != 'default.jpg' && file_exists($target_dir . $current_banner)) {
                        unlink($target_dir . $current_banner);
                    }
                    $banner = $new_filename;
                }
            }
        }
    }
    
    // Update events table
    $query = "UPDATE events SET 
        title = '$title',
        content = '$content',
        schedule = '$schedule',
        banner = '$banner',
        location = '$location'
        WHERE event_id = '$event_id'";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        header('location: ../admin/manage_events.php?success=2');
        exit();
    } else {
        header('location: ../admin/manage_events.php?error=event_update_failed');
        exit();
    }
}

// Delete Event Function
elseif (isset($_POST['delete_event'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    $event_id = (int)$_POST['event_id'];
    
    // Verify event exists and user has permission
    $check_query = "SELECT e.*, u.role 
                   FROM events e 
                   JOIN users u ON e.user_id = u.user_id 
                   WHERE e.event_id = $event_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $event = mysqli_fetch_assoc($check_result);
        
        // Check if user is admin or the event creator
        if($_SESSION['role'] !== 'Admin' && $event['user_id'] !== $_SESSION['user_id']) {
            header('location: ../admin/manage_events.php?error=unauthorized');
            exit();
        }
        
        // Get the banner filename
        $banner_file = $event['banner'];
        
        // Delete the event record
        $delete_event = "DELETE FROM events WHERE event_id = $event_id";
        $event_deleted = mysqli_query($conn, $delete_event);
        
        if($event_deleted) {
            // Delete the banner file if it's not the default
            if($banner_file != 'default.jpg') {
                $target_dir = "../assets/uploads/events/";
                $file_path = $target_dir . $banner_file;
                
                if(file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            header('location: ../admin/manage_events.php?success=3');
            exit();
        } else {
            header('location: ../admin/manage_events.php?error=event_delete_failed');
            exit();
        }
    } else {
        header('location: ../admin/manage_events.php?error=event_not_found');
        exit();
    }
} 