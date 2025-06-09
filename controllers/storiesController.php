<?php
session_start();
include_once '../components/connection.php';

// Create Story Function
if(isset($_POST['create_story'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    // Get form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $media_type = mysqli_real_escape_string($conn, $_POST['media_type']);
    $user_id = (int)$_SESSION['user_id'];

    // Validate required fields
    if(empty($title) || empty($description) || empty($media_type)) {
        header('location: ../stories.php?error=missing_fields');
        exit();
    }
    
    // Handle media upload
    $vimage = null;
    if(isset($_FILES['media']) && $_FILES['media']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/stories/";
        
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = $_FILES['media']['name'];
        $file_tmp = $_FILES['media']['tmp_name'];
        $file_size = $_FILES['media']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'ogg');
        $max_size = $media_type === 'image' ? 5 * 1024 * 1024 : 50 * 1024 * 1024; // 5MB for images, 50MB for videos
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('story_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    $vimage = $new_filename;
                }
            }
        }
    }
    
    // Insert into stories table
    $query = "INSERT INTO stories (
        user_id, title, description, vimage, media_type, created_at, updated_at
    ) VALUES (
        $user_id, '$title', '$description', " . ($vimage ? "'$vimage'" : "NULL") . ", 
        '$media_type', NOW(), NOW()
    )";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        header('location: ../stories.php?success=1');
        exit();
    } else {
        header('location: ../stories.php?error=story_creation_failed');
        exit();
    }
}

// Update Story Function
elseif (isset($_POST['update_story'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    $story_id = (int)$_POST['story_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $media_type = mysqli_real_escape_string($conn, $_POST['media_type']);
    
    // Get current media
    $current_media_query = "SELECT vimage FROM stories WHERE story_id = $story_id";
    $current_media_result = mysqli_query($conn, $current_media_query);
    $current_media_row = mysqli_fetch_assoc($current_media_result);
    $current_media = $current_media_row['vimage'];
    
    // Handle media upload
    $vimage = $current_media; // Keep current media by default
    if(isset($_FILES['media']) && $_FILES['media']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/stories/";
        
        $file_name = $_FILES['media']['name'];
        $file_tmp = $_FILES['media']['tmp_name'];
        $file_size = $_FILES['media']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'ogg');
        $max_size = $media_type === 'image' ? 5 * 1024 * 1024 : 50 * 1024 * 1024;
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('story_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    // Delete old media if it exists
                    if($current_media && file_exists($target_dir . $current_media)) {
                        unlink($target_dir . $current_media);
                    }
                    $vimage = $new_filename;
                }
            }
        }
    }
    
    // Update stories table
    $query = "UPDATE stories SET 
        title = '$title',
        description = '$description',
        vimage = " . ($vimage ? "'$vimage'" : "NULL") . ",
        media_type = '$media_type',
        updated_at = NOW()
        WHERE story_id = $story_id";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        header('location: ../stories.php?success=2');
        exit();
    } else {
        header('location: ../stories.php?error=story_update_failed');
        exit();
    }
}

// Delete Story Function
elseif (isset($_POST['delete_story'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    $story_id = (int)$_POST['story_id'];
    
    // Verify story exists and belongs to user or user is admin
    $check_query = "SELECT vimage, user_id FROM stories WHERE story_id = $story_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $story = mysqli_fetch_assoc($check_result);
        
        // Check if user owns the story or is admin
        if($story['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] === 'Admin') {
            // Get the media filename
            $vimage = $story['vimage'];
            
            // Delete the story record
            $delete_story = "DELETE FROM stories WHERE story_id = $story_id";
            $story_deleted = mysqli_query($conn, $delete_story);
            
            if($story_deleted) {
                // Delete the media if it exists
                if($vimage) {
                    $target_dir = "../assets/uploads/stories/";
                    $file_path = $target_dir . $vimage;
                    
                    if(file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                header('location: ../stories.php?success=3');
                exit();
            } else {
                header('location: ../stories.php?error=story_delete_failed');
                exit();
            }
        } else {
            header('location: ../stories.php?error=unauthorized');
            exit();
        }
    } else {
        header('location: ../stories.php?error=story_not_found');
        exit();
    }
} 