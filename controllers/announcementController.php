<?php
session_start();
include_once '../components/connection.php';

// Create Announcement Function
if(isset($_POST['create_announcement'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    // Get form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $user_id = (int)$_SESSION['user_id'];

    // Validate required fields
    if(empty($title) || empty($content) || empty($status)) {
        header('location: ../admin/announcements.php?error=missing_fields');
        exit();
    }
    
    // Handle attachment upload
    $attachment_url = null;
    if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/announcements/";
        
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = $_FILES['attachment']['name'];
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_size = $_FILES['attachment']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png');
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('announcement_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    $attachment_url = $new_filename;
                }
            }
        }
    }
    
    // Insert into announcements table
    $query = "INSERT INTO announcements (
        title, content, attachment_url, date_posted, status, user_id
    ) VALUES (
        '$title', '$content', " . ($attachment_url ? "'$attachment_url'" : "NULL") . ", 
        NOW(), '$status', $user_id
    )";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        // Determine redirect based on referer or user role
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
        $is_from_admin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/announcements.php') !== false;
        
        if($is_admin && $is_from_admin) {
            header('location: ../admin/announcements.php?success=1');
        } else {
            header('location: ../announcements.php?success=1');
        }
        exit();
    } else {
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
        $is_from_admin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/announcements.php') !== false;
        
        if($is_admin && $is_from_admin) {
            header('location: ../admin/announcements.php?error=announcement_creation_failed');
        } else {
            header('location: ../announcements.php?error=announcement_creation_failed');
        }
        exit();
    }
}

// Update Announcement Function
elseif (isset($_POST['update_announcement'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    $announce_id = (int)$_POST['announce_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Get current attachment
    $current_attachment_query = "SELECT attachment_url FROM announcements WHERE announce_id = $announce_id";
    $current_attachment_result = mysqli_query($conn, $current_attachment_query);
    $current_attachment_row = mysqli_fetch_assoc($current_attachment_result);
    $current_attachment = $current_attachment_row['attachment_url'];
    
    // Handle attachment upload
    $attachment_url = $current_attachment; // Keep current attachment by default
    if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/announcements/";
        
        $file_name = $_FILES['attachment']['name'];
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_size = $_FILES['attachment']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png');
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('announcement_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    // Delete old attachment if it exists
                    if($current_attachment && file_exists($target_dir . $current_attachment)) {
                        unlink($target_dir . $current_attachment);
                    }
                    $attachment_url = $new_filename;
                }
            }
        }
    }
    
    // Update announcements table
    $query = "UPDATE announcements SET 
        title = '$title',
        content = '$content',
        attachment_url = " . ($attachment_url ? "'$attachment_url'" : "NULL") . ",
        status = '$status'
        WHERE announce_id = $announce_id";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        header('location: ../admin/announcements.php?success=2');
        exit();
    } else {
        header('location: ../admin/announcements.php?error=announcement_update_failed');
        exit();
    }
}

// Delete Announcement Function
elseif (isset($_POST['delete_announcement'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    $announce_id = (int)$_POST['announce_id'];
    
    // Verify announcement exists and user has permission
    $check_query = "SELECT a.*, u.role 
                   FROM announcements a 
                   JOIN users u ON a.user_id = u.user_id 
                   WHERE a.announce_id = $announce_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $announcement = mysqli_fetch_assoc($check_result);
        
        // Check if user is admin or the announcement poster
        if($_SESSION['role'] !== 'Admin' && $announcement['user_id'] !== $_SESSION['user_id']) {
            header('location: ../admin/announcements.php?error=unauthorized');
            exit();
        }
        
        // Get the attachment filename
        $attachment_url = $announcement['attachment_url'];
        
        // Delete the announcement record
        $delete_announcement = "DELETE FROM announcements WHERE announce_id = $announce_id";
        $announcement_deleted = mysqli_query($conn, $delete_announcement);
        
        if($announcement_deleted) {
            // Delete the attachment if it exists
            if($attachment_url) {
                $target_dir = "../assets/uploads/announcements/";
                $file_path = $target_dir . $attachment_url;
                
                if(file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            header('location: ../admin/announcements.php?success=3');
            exit();
        } else {
            header('location: ../admin/announcements.php?error=announcement_delete_failed');
            exit();
        }
    } else {
        header('location: ../admin/announcements.php?error=announcement_not_found');
        exit();
    }
} 