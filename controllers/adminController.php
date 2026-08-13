<?php
session_start();
include_once '../components/connection.php';

if(isset($_POST['update_admin_profile'])) {
    $admin_id = $_SESSION['user_id'];
    
    // Get form data
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname'] ?? '');
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename'] ?? '');
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname'] ?? '');
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    $contact = mysqli_real_escape_string($conn, $_POST['contact'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $province = mysqli_real_escape_string($conn, $_POST['province'] ?? '');
    $country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');
    
    // Handle profile image upload
    $image_update = "";
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/alumni/";
        
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = $_FILES['profile_image']['name'];
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_size = $_FILES['profile_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                // Get current image to delete it later
                $current_query = "SELECT image FROM users WHERE user_id = $admin_id";
                $current_result = mysqli_query($conn, $current_query);
                $current_row = mysqli_fetch_assoc($current_result);
                $current_image = $current_row['image'];
                
                $new_filename = uniqid('admin_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    // Delete old image if it exists and is not default
                    if($current_image && $current_image != 'default.png' && file_exists($target_dir . $current_image)) {
                        unlink($target_dir . $current_image);
                    }
                    $image_update = ", image = '$new_filename'";
                }
            }
        }
    }
    
    // Update profile
    $query = "UPDATE users SET 
              firstname = '$firstname',
              middlename = '$middlename',
              lastname = '$lastname',
              gender = '$gender',
              contact = '$contact',
              email = '$email',
              district = '$district',
              city = '$city',
              province = '$province',
              country = '$country'
              $image_update
              WHERE user_id = $admin_id";
              
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Profile updated successfully";
        $_SESSION['email'] = $email;
    } else {
        $_SESSION['error'] = "Failed to update profile: " . mysqli_error($conn);
    }
    
    header('location: ../admin/adminProfile.php');
    exit();
}

if(isset($_POST['change_password'])) {
    $admin_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All password fields are required";
        header('location: ../admin/adminProfile.php');
        exit();
    }
    
    // Get current admin data
    $query = "SELECT password FROM users WHERE user_id = $admin_id";
    $result = mysqli_query($conn, $query);
    $admin = mysqli_fetch_assoc($result);
    
    // Verify current password
    if(!password_verify($current_password, $admin['password'])) {
        $_SESSION['error'] = "Current password is incorrect";
        header('location: ../admin/adminProfile.php');
        exit();
    }
    
    // Check if new passwords match
    if($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
        header('location: ../admin/adminProfile.php');
        exit();
    }
    
    // Validate password length
    if(strlen($new_password) < 8) {
        $_SESSION['error'] = "New password must be at least 8 characters long";
        header('location: ../admin/adminProfile.php');
        exit();
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET password = '$hashed_password' WHERE user_id = $admin_id";
              
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Password changed successfully";
    } else {
        $_SESSION['error'] = "Failed to change password: " . mysqli_error($conn);
    }
    
    header('location: ../admin/adminProfile.php');
    exit();
}
?>
