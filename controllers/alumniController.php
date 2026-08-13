<?php
include_once '../components/connection.php';

// CREATE USER (Admin, Staff, Alumni)
if (isset($_POST['create_user'])) {

    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $graduation_year = mysqli_real_escape_string($conn, $_POST['graduation_year']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = isset($_POST['status']) ? 1 : 0;
    $date_created = date('Y-m-d H:i:s');

    // Validate required fields
    $required_fields = ['firstname', 'lastname', 'gender', 'batch', 'course_id', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
    }

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Validate password length
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Validate role
    if (!in_array($role, ['Admin', 'Staff', 'Alumni'])) {
        $_SESSION['error'] = "Invalid role selected!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Handle file upload
    $image = 'default.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_ext)) {
            $_SESSION['error'] = "Invalid file type. Allowed types: " . implode(', ', $allowed_ext);
            header("Location: ../admin/manage_alumni.php");
            exit();
        }

        // Check file size (max 5MB)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "File size too large. Maximum size is 5MB.";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }

        $image = uniqid('alumni_', true) . '.' . $file_ext;
        $upload_path = '../assets/uploads/alumni/' . $image;
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
    }

    // Check if email already exists
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert into users table
        $user_query = "INSERT INTO users 
                      (password, email, role, firstname, middlename, lastname, gender, batch, course_id, contact, image, status, date_created, district, city, province, country, graduation_year) 
                      VALUES 
                      ('$hashed_password', '$email', '$role', '$firstname', '$middlename', '$lastname', '$gender', '$batch', '$course_id', '$contact', '$image', '$status', '$date_created', '$district', '$city', '$province', '$country', '$graduation_year')";

        if (!mysqli_query($conn, $user_query)) {
            throw new Exception("Error creating user: " . mysqli_error($conn));
        }

        $user_id = mysqli_insert_id($conn);

        // Store credentials for display
        $_SESSION['new_user_credentials'] = [
            'email' => $email,
            'password' => $password,
            'role' => $role
        ];

        $_SESSION['success'] = ucfirst($role) . " created successfully!";
        header("Location: ../admin/manage_alumni.php?show_credentials=1");
        exit();

    } catch (Exception $e) {
        // Delete uploaded image if exists
        if ($image != 'default.png' && file_exists('../assets/uploads/alumni/' . $image)) {
            unlink('../assets/uploads/alumni/' . $image);
        }

        $_SESSION['error'] = $e->getMessage();
        header("Location: ../admin/manage_alumni.php");
        exit();
    }
}

// CREATE ALUMNI (Specific for alumni form)
if (isset($_POST['create_alumni'])) {
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $province = mysqli_real_escape_string($conn, $_POST['province'] ?? '');
    $country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');
    $graduation_year = mysqli_real_escape_string($conn, $_POST['graduation_year']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $role = 'Alumni'; // Force role to Alumni
    $status = isset($_POST['status']) ? 1 : 0;
    $date_created = date('Y-m-d H:i:s');

    // Validate required fields
    $required_fields = ['firstname', 'lastname', 'gender', 'batch', 'course_id', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
    }

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Validate password length
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Handle file upload
    $image = 'default.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_ext)) {
            $_SESSION['error'] = "Invalid file type. Allowed types: " . implode(', ', $allowed_ext);
            header("Location: ../admin/manage_alumni.php");
            exit();
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "File size too large. Maximum size is 5MB.";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }

        $image = uniqid('alumni_', true) . '.' . $file_ext;
        $upload_path = '../assets/uploads/alumni/' . $image;
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
    }

    // Check if email already exists
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert into users table
        $user_query = "INSERT INTO users 
                      (password, email, role, firstname, middlename, lastname, gender, batch, course_id, contact, image, status, date_created, district, city, province, country, graduation_year) 
                      VALUES 
                      ('$hashed_password', '$email', '$role', '$firstname', '$middlename', '$lastname', '$gender', '$batch', '$course_id', '$contact', '$image', '$status', '$date_created', '$district', '$city', '$province', '$country', '$graduation_year')";

        if (!mysqli_query($conn, $user_query)) {
            throw new Exception("Error creating alumni: " . mysqli_error($conn));
        }

        $_SESSION['success'] = "Alumni created successfully!";
        header("Location: ../admin/manage_alumni.php");
        exit();

    } catch (Exception $e) {
        if ($image != 'default.png' && file_exists('../assets/uploads/alumni/' . $image)) {
            unlink('../assets/uploads/alumni/' . $image);
        }
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../admin/manage_alumni.php");
        exit();
    }
}

// UPDATE ALUMNI
if (isset($_POST['update_alumni'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $province = mysqli_real_escape_string($conn, $_POST['province'] ?? '');
    $country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');
    $graduation_year = mysqli_real_escape_string($conn, $_POST['graduation_year']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Handle password change if provided
    $update_password = false;
    $new_password = '';
    $confirm_new_password = '';
    
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
        $confirm_new_password = mysqli_real_escape_string($conn, $_POST['confirm_new_password'] ?? '');
        
        // Validate password change
        if (empty($confirm_new_password)) {
            $_SESSION['error'] = "Please confirm the new password.";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
        
        if ($new_password !== $confirm_new_password) {
            $_SESSION['error'] = "New passwords do not match!";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
        
        if (strlen($new_password) < 8) {
            $_SESSION['error'] = "New password must be at least 8 characters long!";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
        
        $update_password = true;
    }
    
    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($gender) || empty($email)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Check if email already exists (excluding current user)
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND user_id != '$user_id'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }
    
    // Get current image
    $current_image_query = mysqli_query($conn, "SELECT image FROM users WHERE user_id = '$user_id'");
    $current_image_data = mysqli_fetch_assoc($current_image_query);
    $current_image = $current_image_data ? $current_image_data['image'] : 'default.png';
    $image = $current_image;
    
    // Handle file upload if new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_ext)) {
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $_SESSION['error'] = "File size too large. Maximum size is 5MB.";
                header("Location: ../admin/manage_alumni.php");
                exit();
            }
            
            if ($current_image != 'default.png' && file_exists('../assets/uploads/alumni/' . $current_image)) {
                unlink('../assets/uploads/alumni/' . $current_image);
            }
            
            $image = uniqid('alumni_', true) . '.' . $file_ext;
            $upload_path = '../assets/uploads/alumni/' . $image;
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $_SESSION['error'] = "Failed to upload image.";
                header("Location: ../admin/manage_alumni.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed types: jpg, jpeg, png, gif";
            header("Location: ../admin/manage_alumni.php");
            exit();
        }
    }
    
    // Update user record
    $password_update = '';
    if ($update_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $password_update = ", password = '$hashed_password'";
    }
    
    $update_query = "UPDATE users SET 
                    firstname = '$firstname',
                    middlename = '$middlename',
                    lastname = '$lastname',
                    gender = '$gender',
                    batch = '$batch',
                    course_id = '$course_id',
                    contact = '$contact',
                    email = '$email',
                    image = '$image',
                    status = '$status',
                    district = '$district',
                    city = '$city',
                    province = '$province',
                    country = '$country',
                    graduation_year = '$graduation_year'
                    $password_update
                    WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success_msg = "Alumni updated successfully!";
        if ($update_password) {
            $success_msg .= " Password has been changed.";
        }
        $_SESSION['success'] = $success_msg;
    } else {
        $_SESSION['error'] = "Error updating alumni record: " . mysqli_error($conn);
    }
    
    header("Location: ../admin/manage_alumni.php");
    exit();
}

// DELETE USER/ALUMNI
if (isset($_POST['delete_user'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Prevent deletion of current user
    if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }
    
    // Get image path before deletion
    $image_query = mysqli_query($conn, "SELECT image, email FROM users WHERE user_id = '$user_id'");
    $user_data = mysqli_fetch_assoc($image_query);
    
    if (!$user_data) {
        $_SESSION['error'] = "User not found!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }
    
    $image = $user_data['image'];
    $email = $user_data['email'];
    
    // Delete user record
    $delete_user = mysqli_query($conn, "DELETE FROM users WHERE user_id = '$user_id'");
    
    if ($delete_user && mysqli_affected_rows($conn) > 0) {
        // Delete image file if it's not the default
        if ($image != 'default.png' && file_exists('../assets/uploads/alumni/' . $image)) {
            unlink('../assets/uploads/alumni/' . $image);
        }
        $_SESSION['success'] = "User '{$email}' deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting user: " . mysqli_error($conn);
    }
    
    header("Location: ../admin/manage_alumni.php");
    exit();
}

// UPDATE USER STATUS (Activate/Deactivate)
if (isset($_POST['update_status'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE users SET status = '$status' WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $status_text = $status == 1 ? 'activated' : 'deactivated';
        $_SESSION['success'] = "User {$status_text} successfully!";
    } else {
        $_SESSION['error'] = "Error updating user status: " . mysqli_error($conn);
    }
    
    header("Location: ../admin/manage_alumni.php");
    exit();
}

// BULK ACTIONS (Delete multiple users)
if (isset($_POST['bulk_action'])) {
    $bulk_action = mysqli_real_escape_string($conn, $_POST['bulk_action']);
    $user_ids = isset($_POST['user_ids']) ? $_POST['user_ids'] : [];
    
    if (empty($user_ids)) {
        $_SESSION['error'] = "No users selected!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }
    
    $user_ids_clean = array_map(function($id) use ($conn) {
        return mysqli_real_escape_string($conn, $id);
    }, $user_ids);
    
    $user_ids_string = "'" . implode("','", $user_ids_clean) . "'";
    
    if ($bulk_action == 'delete') {
        // Get images of users to be deleted
        $images_query = mysqli_query($conn, "SELECT image FROM users WHERE user_id IN ($user_ids_string)");
        $images_to_delete = [];
        while ($row = mysqli_fetch_assoc($images_query)) {
            if ($row['image'] != 'default.png') {
                $images_to_delete[] = $row['image'];
            }
        }
        
        // Delete users
        $delete_query = "DELETE FROM users WHERE user_id IN ($user_ids_string)";
        if (mysqli_query($conn, $delete_query)) {
            // Delete image files
            foreach ($images_to_delete as $image) {
                if (file_exists('../assets/uploads/alumni/' . $image)) {
                    unlink('../assets/uploads/alumni/' . $image);
                }
            }
            $_SESSION['success'] = count($user_ids) . " user(s) deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting users: " . mysqli_error($conn);
        }
    } elseif ($bulk_action == 'activate') {
        $update_query = "UPDATE users SET status = 1 WHERE user_id IN ($user_ids_string)";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = count($user_ids) . " user(s) activated successfully!";
        } else {
            $_SESSION['error'] = "Error activating users: " . mysqli_error($conn);
        }
    } elseif ($bulk_action == 'deactivate') {
        $update_query = "UPDATE users SET status = 0 WHERE user_id IN ($user_ids_string)";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = count($user_ids) . " user(s) deactivated successfully!";
        } else {
            $_SESSION['error'] = "Error deactivating users: " . mysqli_error($conn);
        }
    }
    
    header("Location: ../admin/manage_alumni.php");
    exit();
}
?>