<?php
session_start();
include_once '../components/connection.php';

if (isset($_POST['update_testimonial'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }
    $testimonial_id = (int)$_POST['testimonial_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $allowed = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed)) {
        header('location: ../admin/manage_testimonial.php?error=invalid_status');
        exit();
    }
    $sql = "UPDATE testimonials SET status = '$status', updated_at = NOW() WHERE testimonial_id = $testimonial_id";
    if (mysqli_query($conn, $sql)) {
        header('location: ../admin/manage_testimonial.php?success=status_updated');
        exit();
    } else {
        header('location: ../admin/manage_testimonial.php?error=status_update_failed');
        exit();
    }
}

if (isset($_POST['delete_testimonial'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }
    $testimonial_id = (int)$_POST['testimonial_id'];
    $sql = "DELETE FROM testimonials WHERE testimonial_id = $testimonial_id";
    if (mysqli_query($conn, $sql)) {
        header('location: ../admin/manage_testimonial.php?success=deleted');
        exit();
    } else {
        header('location: ../admin/manage_testimonial.php?error=delete_failed');
        exit();
    }
}