<?php
session_start();
include_once '../components/connection.php';

// Helper function to send JSON response
function json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Check if request is AJAX
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if($is_ajax) {
        json_response(false, 'Invalid request method.');
    }
    header('location: ../login.php');
    exit();
}

// Validate user session
if(!isset($_SESSION['user_id'])) {
    if($is_ajax) {
        json_response(false, 'Please login to share posts.');
    }
    header('location: ../login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$entity_type = isset($_POST['entity_type']) ? mysqli_real_escape_string($conn, $_POST['entity_type']) : '';
$entity_id = isset($_POST['entity_id']) ? (int)$_POST['entity_id'] : 0;
$action = isset($_POST['action']) ? mysqli_real_escape_string($conn, $_POST['action']) : 'share'; // 'share' or 'unshare'

// Validate required fields
if(empty($entity_type) || $entity_id <= 0) {
    json_response(false, 'Invalid request. Missing required fields.');
}

// Validate entity_type
$allowed_types = ['event', 'announcement', 'job', 'news', 'story'];
if(!in_array($entity_type, $allowed_types)) {
    json_response(false, 'Invalid entity type.');
}

// Check if shares table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'shares'");
if(mysqli_num_rows($table_check) === 0) {
    json_response(false, 'Shares table does not exist. Please run the SQL script: db/create_shares_table.sql');
}

// Check if already shared
$check_query = "SELECT share_id FROM shares WHERE user_id = $user_id AND entity_type = '$entity_type' AND entity_id = $entity_id";
$check_result = mysqli_query($conn, $check_query);
$already_shared = mysqli_num_rows($check_result) > 0;

if($action === 'share' && !$already_shared) {
    // Add share
    $query = "INSERT INTO shares (user_id, entity_type, entity_id) VALUES ($user_id, '$entity_type', $entity_id)";
    if(mysqli_query($conn, $query)) {
        // Get updated share count
        $count_query = "SELECT COUNT(*) as count FROM shares WHERE entity_type = '$entity_type' AND entity_id = $entity_id";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        json_response(true, 'Shared to your account!', ['shared' => true, 'count' => (int)$count_row['count']]);
    } else {
        json_response(false, 'Failed to share. Please try again.');
    }
} elseif($action === 'unshare' && $already_shared) {
    // Remove share
    $query = "DELETE FROM shares WHERE user_id = $user_id AND entity_type = '$entity_type' AND entity_id = $entity_id";
    if(mysqli_query($conn, $query)) {
        // Get updated share count
        $count_query = "SELECT COUNT(*) as count FROM shares WHERE entity_type = '$entity_type' AND entity_id = $entity_id";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        json_response(true, 'Removed from your shares!', ['shared' => false, 'count' => (int)$count_row['count']]);
    } else {
        json_response(false, 'Failed to unshare. Please try again.');
    }
} else {
    if($already_shared) {
        json_response(false, 'Already shared.');
    } else {
        json_response(false, 'Not shared yet.');
    }
}







