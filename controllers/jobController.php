<?php
session_start();
include_once '../components/connection.php';

// Create Job Function
if(isset($_POST['create_job'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    // Get form data
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $salary = mysqli_real_escape_string($conn, $_POST['salary']);
    $job_type = mysqli_real_escape_string($conn, $_POST['job_type']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $employee_status = mysqli_real_escape_string($conn, $_POST['employee_status']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $application_deadline = mysqli_real_escape_string($conn, $_POST['application_deadline']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);
    $user_id = (int)$_SESSION['user_id'];

    // Validate required fields
    if(empty($job_title) || empty($description) || empty($salary) || empty($job_type) || 
       empty($company_name) || empty($employee_status) || empty($location) || empty($application_deadline)) {
        header('location: ../admin/manage_jobs.php?error=missing_fields');
        exit();
    }
    
    // Handle job image upload
    $job_image = 'default.jpg';
    if(isset($_FILES['job_image']) && $_FILES['job_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/jobs/";
        
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = $_FILES['job_image']['name'];
        $file_tmp = $_FILES['job_image']['tmp_name'];
        $file_size = $_FILES['job_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('job_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    $job_image = $new_filename;
                }
            }
        }
    }
    
    // Insert into jobs table
    $query = "INSERT INTO jobs (
        job_title, description, salary, job_type, company_name, 
        employee_status, location, application_deadline, url, 
        job_image, user_id, created_at, updated_at
    ) VALUES (
        '$job_title', '$description', $salary, '$job_type', '$company_name',
        '$employee_status', '$location', '$application_deadline', '$url',
        '$job_image', $user_id, NOW(), NOW()
    )";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        header('location: ../admin/manage_jobs.php?success=1');
        exit();
    } else {
        header('location: ../admin/manage_jobs.php?error=job_creation_failed');
        exit();
    }
}

// Update Job Function
elseif (isset($_POST['update_job'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        header('location: ../login.php');
        exit();
    }

    $job_id = (int)$_POST['job_id'];
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $salary = mysqli_real_escape_string($conn, $_POST['salary']);
    $job_type = mysqli_real_escape_string($conn, $_POST['job_type']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $employee_status = mysqli_real_escape_string($conn, $_POST['employee_status']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $application_deadline = mysqli_real_escape_string($conn, $_POST['application_deadline']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);
    
    // Get current job image
    $current_image_query = "SELECT job_image FROM jobs WHERE job_id = $job_id";
    $current_image_result = mysqli_query($conn, $current_image_query);
    $current_image_row = mysqli_fetch_assoc($current_image_result);
    $current_image = $current_image_row['job_image'];
    
    // Handle job image upload
    $job_image = $current_image; // Keep current image by default
    if(isset($_FILES['job_image']) && $_FILES['job_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/jobs/";
        
        $file_name = $_FILES['job_image']['name'];
        $file_tmp = $_FILES['job_image']['tmp_name'];
        $file_size = $_FILES['job_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if(in_array($file_ext, $allowed_ext)) {
            if($file_size <= $max_size) {
                $new_filename = uniqid('job_', true) . '.' . $file_ext;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($file_tmp, $target_file)) {
                    // Delete old image if it's not the default
                    if($current_image != 'default.jpg' && file_exists($target_dir . $current_image)) {
                        unlink($target_dir . $current_image);
                    }
                    $job_image = $new_filename;
                }
            }
        }
    }
    
    // Update jobs table
    $query = "UPDATE jobs SET 
        job_title = '$job_title',
        description = '$description',
        salary = $salary,
        job_type = '$job_type',
        company_name = '$company_name',
        employee_status = '$employee_status',
        location = '$location',
        application_deadline = '$application_deadline',
        url = '$url',
        job_image = '$job_image',
        updated_at = NOW()
        WHERE job_id = $job_id";
    
    $result = mysqli_query($conn, $query);
    
    if($result) {
        header('location: ../admin/manage_jobs.php?success=2');
        exit();
    } else {
        header('location: ../admin/manage_jobs.php?error=job_update_failed');
        exit();
    }
}

// Delete Job Function
elseif (isset($_POST['delete_job'])) {
    // Validate user session and role
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        header('location: ../login.php');
        exit();
    }

    $job_id = (int)$_POST['job_id'];
    
    // Verify job exists
    $check_query = "SELECT job_image FROM jobs WHERE job_id = $job_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $job = mysqli_fetch_assoc($check_result);
        
        // Get the job image filename
        $job_image = $job['job_image'];
        
        // Delete the job record
        $delete_job = "DELETE FROM jobs WHERE job_id = $job_id";
        $job_deleted = mysqli_query($conn, $delete_job);
        
        if($job_deleted) {
            // Delete the job image if it's not the default
            if($job_image != 'default.jpg') {
                $target_dir = "../assets/uploads/jobs/";
                $file_path = $target_dir . $job_image;
                
                if(file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            header('location: ../admin/manage_jobs.php?success=3');
            exit();
        } else {
            header('location: ../admin/manage_jobs.php?error=job_delete_failed');
            exit();
        }
    } else {
        header('location: ../admin/manage_jobs.php?error=job_not_found');
        exit();
    }
} 