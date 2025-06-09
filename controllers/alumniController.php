<?php
include_once '../components/connection.php';

// CREATE ALUMNI/ADMIN
if (isset($_POST['create_alumni'])) {
    // Validate and sanitize input
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $graduation_year = mysqli_real_escape_string($conn, $_POST['graduation_year']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = isset($_POST['status']) ? 1 : 0;
    $date_created = date('Y-m-d H:i:s');

    // Validate required fields
    $required_fields = ['firstname', 'lastname', 'gender', 'batch', 'course_id', 'email', 'username', 'password'];
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
    if (!in_array($role, ['Admin', 'Alumni'])) {
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
        if (!move_uploaded_file($file_tmp, '../assets/uploads/alumni/' . $image)) {
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

    // Check if username already exists
    $check_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $_SESSION['error'] = "Username already exists!";
        header("Location: ../admin/manage_alumni.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert into alumnus table
        $alumnus_query = "INSERT INTO alumnus 
                          (firstname, middlename, lastname, gender, batch, course_id, contact, image, status, date_created, address, city, country, graduation_year) 
                          VALUES 
                          ('$firstname', '$middlename', '$lastname', '$gender', '$batch', '$course_id', '$contact', '$image', '$status', '$date_created', '$address', '$city', '$country', '$graduation_year')";

        if (!mysqli_query($conn, $alumnus_query)) {
            throw new Exception("Error creating alumni record: " . mysqli_error($conn));
        }

        $alumnus_id = mysqli_insert_id($conn);

        // Insert into users table
        $user_query = "INSERT INTO users 
                       (username, password, email, role, alumnus_id) 
                       VALUES 
                       ('$username', '$hashed_password', '$email', '$role', '$alumnus_id')";

        if (!mysqli_query($conn, $user_query)) {
            throw new Exception("Error creating user account: " . mysqli_error($conn));
        }

        // Store credentials for display
        $_SESSION['new_user_credentials'] = [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'role' => $role
        ];

        // Commit transaction
        mysqli_commit($conn);

        $_SESSION['success'] = ucfirst($role) . " created successfully!";
        header("Location: ../admin/manage_alumni.php?show_credentials=1");
        exit();

    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);

        // Delete uploaded image if exists
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
    $alumnus_id = mysqli_real_escape_string($conn, $_POST['alumnus_id']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $graduation_year = mysqli_real_escape_string($conn, $_POST['graduation_year']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Get current image
    $current_image_query = mysqli_query($conn, "SELECT image FROM alumnus WHERE alumnus_id = '$alumnus_id'");
    $current_image = mysqli_fetch_assoc($current_image_query)['image'];
    $image = $current_image;
    
    // Handle file upload if new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_ext)) {
            // Delete old image if it's not the default
            if ($current_image != 'default.png' && file_exists('../assets/uploads/alumni/' . $current_image)) {
                unlink('../assets/uploads/alumni/' . $current_image);
            }
            
            $image = uniqid('alumni_', true) . '.' . $file_ext;
            move_uploaded_file($file_tmp, '../assets/uploads/alumni/' . $image);
        }
    }
    
    // Update alumnus record
    $update_query = "UPDATE alumnus SET 
                    firstname = '$firstname',
                    middlename = '$middlename',
                    lastname = '$lastname',
                    gender = '$gender',
                    batch = '$batch',
                    course_id = '$course_id',
                    contact = '$contact',
                    image = '$image',
                    status = '$status',
                    address = '$address',
                    city = '$city',
                    country = '$country',
                    graduation_year = '$graduation_year'
                    WHERE alumnus_id = '$alumnus_id'";
    
    if (mysqli_query($conn, $update_query)) {
        // Update email in users table
        $update_email_query = "UPDATE users SET email = '$email' WHERE alumnus_id = '$alumnus_id'";
        if (mysqli_query($conn, $update_email_query)) {
            $_SESSION['success'] = "Alumni updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating email: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Error updating alumni record: " . mysqli_error($conn);
    }
    
    header("Location: ../admin/alumni.php");
    exit();
}

// UPDATE ACCOUNT (username, role, password)
if (isset($_POST['update_account'])) {
    $alumnus_id = mysqli_real_escape_string($conn, $_POST['alumnus_id']);
    $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
    $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_new_password = mysqli_real_escape_string($conn, $_POST['confirm_new_password']);
    
    // Check if username is being changed and if it's already taken
    if (!empty($new_username)) {
        $check_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$new_username' AND alumnus_id != '$alumnus_id'");
        if (mysqli_num_rows($check_username) > 0) {
            $_SESSION['error'] = "Username already taken!";
            header("Location: ../admin/alumni.php");
            exit();
        }
    }
    
    // Check if passwords match if provided
    if (!empty($new_password) && $new_password !== $confirm_new_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: ../admin/alumni.php");
        exit();
    }
    
    // Build update query
    $update_fields = [];
    if (!empty($new_username)) $update_fields[] = "username = '$new_username'";
    if (!empty($new_role)) $update_fields[] = "role = '$new_role'";
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields[] = "password = '$hashed_password'";
    }
    
    if (!empty($update_fields)) {
        $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE alumnus_id = '$alumnus_id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = "Account updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating account: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "No changes were made.";
    }
    
    header("Location: ../admin/alumni.php");
    exit();
}

// DELETE ALUMNI
if (isset($_POST['delete_alumni'])) {
    $alumnus_id = mysqli_real_escape_string($conn, $_POST['alumnus_id']);
    
    // Get image path before deletion
    $image_query = mysqli_query($conn, "SELECT image FROM alumnus WHERE alumnus_id = '$alumnus_id'");
    $image_data = mysqli_fetch_assoc($image_query);
    $image = $image_data['image'];
    
    // Delete user account first (due to foreign key constraint)
    $delete_user = mysqli_query($conn, "DELETE FROM users WHERE alumnus_id = '$alumnus_id'");
    
    if ($delete_user) {
        // Delete alumni record
        $delete_alumni = mysqli_query($conn, "DELETE FROM alumnus WHERE alumnus_id = '$alumnus_id'");
        
        if ($delete_alumni) {
            // Delete image file if it's not the default
            if ($image != 'default.png' && file_exists('../assets/uploads/alumni/' . $image)) {
                unlink('../assets/uploads/alumni/' . $image);
            }
            $_SESSION['success'] = "Alumni deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting alumni record: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Error deleting user account: " . mysqli_error($conn);
    }
    
    header("Location: ../admin/manage_alumni.php");
    exit();
}
?>