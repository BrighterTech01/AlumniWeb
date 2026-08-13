<?php
session_start();
include_once '../components/connection.php';

// Create News Function
if(isset($_POST['create_news'])) {
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
        header('location: ../admin/manage_news.php?error=missing_fields');
        exit();
    }
    
    // Handle image upload
    $image_url = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/news/";
        
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('news_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    $image_url = $new_filename;
                }
            }
        }
    }
    
    // Insert into news table
    $query = "INSERT INTO news (
        title, content, image_url, date_posted, status, user_id
    ) VALUES (
        '$title', '$content', " . ($image_url ? "'$image_url'" : "NULL") . ", 
        NOW(), '$status', $user_id
    )";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        // Determine redirect based on referer or user role
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
        $is_from_admin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'manage_news.php') !== false;
        
        if($is_admin && $is_from_admin) {
            header('location: ../admin/manage_news.php?success=1');
        } else {
            header('location: ../news.php?success=1');
        }
        exit();
    } else {
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
        $is_from_admin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'manage_news.php') !== false;
        
        if($is_admin && $is_from_admin) {
            header('location: ../admin/manage_news.php?error=news_creation_failed');
        } else {
            header('location: ../news.php?error=news_creation_failed');
        }
        exit();
    }
}

// Update News Function
elseif (isset($_POST['update_news'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    $news_id = (int)$_POST['news_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Get current image
    $current_image_query = "SELECT image_url FROM news WHERE news_id = $news_id";
    $current_image_result = mysqli_query($conn, $current_image_query);
    $current_image_row = mysqli_fetch_assoc($current_image_result);
    $current_image = $current_image_row['image_url'];
    
    // Handle image upload
    $image_url = $current_image; // Keep current image by default
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/news/";
        
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('news_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    // Delete old image if it exists
                    if($current_image && file_exists($target_dir . $current_image)) {
                        unlink($target_dir . $current_image);
                    }
                    $image_url = $new_filename;
                }
            }
        }
    }
    
    // Update news table
    $query = "UPDATE news SET 
        title = '$title',
        content = '$content',
        image_url = " . ($image_url ? "'$image_url'" : "NULL") . ",
        status = '$status'
        WHERE news_id = $news_id";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        header('location: ../admin/manage_news.php?success=2');
        exit();
    } else {
        header('location: ../admin/manage_news.php?error=news_update_failed');
        exit();
    }
}

// Delete News Function
elseif (isset($_POST['delete_news'])) {
    // Validate user session and role
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        header('location: ../login.php');
        exit();
    }

    $news_id = (int)$_POST['news_id'];
    
    // Verify news exists
    $check_query = "SELECT image_url FROM news WHERE news_id = $news_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $news = mysqli_fetch_assoc($check_result);
        
        // Get the image filename
        $image_url = $news['image_url'];
        
        // Delete the news record
        $delete_news = "DELETE FROM news WHERE news_id = $news_id";
        $news_deleted = mysqli_query($conn, $delete_news);
        
        if($news_deleted) {
            // Delete the image if it exists
            if($image_url) {
                $target_dir = "../assets/uploads/news/";
                $file_path = $target_dir . $image_url;
                
                if(file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            header('location: ../admin/manage_news.php?success=3');
            exit();
        } else {
            header('location: ../admin/manage_news.php?error=news_delete_failed');
            exit();
        }
    } else {
        header('location: ../admin/manage_news.php?error=news_not_found');
        exit();
    }
} 