<?php
session_start();
include_once '../components/connection.php';

if(isset($_POST['update_admin_profile'])) {
    $admin_id = $_SESSION['user_id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current admin data
    $query = "SELECT * FROM users WHERE user_id = $admin_id";
    $result = mysqli_query($conn, $query);
    $admin = mysqli_fetch_assoc($result);
    
    // Verify current password if provided
    if(!empty($current_password)) {
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
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $password_update = ", password = '$hashed_password'";
    } else {
        $password_update = "";
    }
    
    // Update profile
    $query = "UPDATE users SET 
              username = '$username',
              email = '$email'
              $password_update
              WHERE user_id = $admin_id";
              
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Profile updated successfully";
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
    } else {
        $_SESSION['error'] = "Failed to update profile";
    }
    
    header('location: ../admin/adminProfile.php');
    exit();
}
?> 