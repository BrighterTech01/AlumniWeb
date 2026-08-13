<?php
session_start();
require_once '../components/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if story ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Story ID is required']);
    exit();
}

$story_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch story details with user information
$query = "SELECT s.*, CONCAT(u.firstname, ' ', u.lastname) AS username, u.role, u.image 
          FROM stories s 
          JOIN users u ON s.user_id = u.user_id 
          WHERE s.story_id = '$story_id'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $story = mysqli_fetch_assoc($result);
    echo json_encode($story);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Story not found']);
}
?> 