<?php
session_start();
include_once '../components/connection.php';

// Helper function to send JSON response
function json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Detect AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Add Comment Function
// Check for add_comment button OR if comment_text, entity_type, and entity_id are present (for AJAX)
if(isset($_POST['add_comment']) || (isset($_POST['comment_text']) && isset($_POST['entity_type']) && isset($_POST['entity_id']))) {
    // Validate user session - allow all logged-in users
    if(!isset($_SESSION['user_id'])) {
        if($is_ajax) {
            json_response(false, 'Please login to comment.');
        }
        header('location: ../login.php');
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];
    $entity_type = mysqli_real_escape_string($conn, $_POST['entity_type']);
    $entity_id = (int)$_POST['entity_id'];
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $comment_text = mysqli_real_escape_string($conn, trim($_POST['comment_text']));

    // Validate required fields
    if(empty($comment_text)) {
        if($is_ajax) {
            json_response(false, 'Comment cannot be empty.');
        }
        $_SESSION['error'] = "Comment cannot be empty.";
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validate entity_type
    $allowed_types = ['event', 'announcement', 'story', 'job', 'news'];
    if(!in_array($entity_type, $allowed_types)) {
        if($is_ajax) {
            json_response(false, 'Invalid entity type.');
        }
        $_SESSION['error'] = "Invalid entity type.";
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Insert comment
    $query = "INSERT INTO comments (user_id, entity_type, entity_id, parent_id, comment_text, created_at, updated_at) 
              VALUES ($user_id, '$entity_type', $entity_id, $parent_id, '$comment_text', NOW(), NOW())";
    
    if(mysqli_query($conn, $query)) {
        $comment_id = mysqli_insert_id($conn);
        $success_message = $parent_id > 0 ? "Reply posted successfully!" : "Comment posted successfully!";
        
        // If AJAX, return success immediately
        if($is_ajax) {
            json_response(true, $success_message, ['comment_id' => $comment_id]);
        }
        
        $_SESSION['success'] = $success_message;
        
        // Create notifications for all entity types
        $entity_config = [
            'event' => ['table' => 'events', 'id_field' => 'event_id', 'title_field' => 'title', 'name' => 'event'],
            'announcement' => ['table' => 'announcements', 'id_field' => 'announce_id', 'title_field' => 'title', 'name' => 'announcement'],
            'job' => ['table' => 'jobs', 'id_field' => 'job_id', 'title_field' => 'job_title', 'name' => 'job'],
            'news' => ['table' => 'news', 'id_field' => 'news_id', 'title_field' => 'title', 'name' => 'news'],
            'story' => ['table' => 'stories', 'id_field' => 'story_id', 'title_field' => 'title', 'name' => 'story']
        ];
        
        if(isset($entity_config[$entity_type])) {
            $config = $entity_config[$entity_type];
            $table = $config['table'];
            $id_field = $config['id_field'];
            $title_field = $config['title_field'];
            $entity_name = $config['name'];
            
            // Get entity author and title
            $entity_query = "SELECT user_id, $title_field FROM $table WHERE $id_field = $entity_id";
            $entity_result = mysqli_query($conn, $entity_query);
            
            if($entity_result && mysqli_num_rows($entity_result) > 0) {
                $entity_data = mysqli_fetch_assoc($entity_result);
                $entity_author_id = (int)$entity_data['user_id'];
                $entity_title = $entity_data[$title_field];
                
                // Get commenter name
                $commenter_query = "SELECT firstname, lastname FROM users WHERE user_id = $user_id";
                $commenter_result = mysqli_query($conn, $commenter_query);
                $commenter_name = 'Someone';
                if($commenter_result && mysqli_num_rows($commenter_result) > 0) {
                    $commenter_data = mysqli_fetch_assoc($commenter_result);
                    $commenter_name = $commenter_data['firstname'] . ' ' . $commenter_data['lastname'];
                }
                
                // Only notify if commenter is not the entity author
                if($entity_author_id != $user_id && $entity_author_id > 0) {
                    if($parent_id > 0) {
                        // This is a reply - notify the parent comment author
                        $parent_query = "SELECT user_id, firstname, lastname FROM comments c 
                                        JOIN users u ON c.user_id = u.user_id 
                                        WHERE c.comment_id = $parent_id";
                        $parent_result = mysqli_query($conn, $parent_query);
                        
                        if($parent_result && mysqli_num_rows($parent_result) > 0) {
                            $parent_data = mysqli_fetch_assoc($parent_result);
                            $parent_author_id = (int)$parent_data['user_id'];
                            
                            // Notify parent comment author if they're not the one replying
                            if($parent_author_id != $user_id && $parent_author_id > 0) {
                                $notification_message = mysqli_real_escape_string($conn, "$commenter_name replied to your comment on $entity_name: " . substr($entity_title, 0, 50));
                                $notify_query = "INSERT INTO notifications (user_id, entity_type, entity_id, comment_id, notification_type, message) 
                                                VALUES ($parent_author_id, '$entity_type', $entity_id, $comment_id, 'reply', '$notification_message')";
                                mysqli_query($conn, $notify_query);
                            }
                        }
                        
                        // Also notify entity author if they're not the parent comment author
                        if($entity_author_id != $parent_author_id && $entity_author_id > 0) {
                            $notification_message = mysqli_real_escape_string($conn, "$commenter_name replied to a comment on your $entity_name: " . substr($entity_title, 0, 50));
                            $notify_query = "INSERT INTO notifications (user_id, entity_type, entity_id, comment_id, notification_type, message) 
                                            VALUES ($entity_author_id, '$entity_type', $entity_id, $comment_id, 'reply', '$notification_message')";
                            mysqli_query($conn, $notify_query);
                        }
                    } else {
                        // This is a new comment - notify entity author
                        $notification_message = mysqli_real_escape_string($conn, "$commenter_name commented on your $entity_name: " . substr($entity_title, 0, 50));
                        $notify_query = "INSERT INTO notifications (user_id, entity_type, entity_id, comment_id, notification_type, message) 
                                        VALUES ($entity_author_id, '$entity_type', $entity_id, $comment_id, 'comment', '$notification_message')";
                        mysqli_query($conn, $notify_query);
                    }
                }
            }
        }
    } else {
        $error_message = "Failed to post comment. Please try again.";
        if($is_ajax) {
            json_response(false, $error_message);
        }
        $_SESSION['error'] = $error_message;
    }
    
    // Redirect back to the page (only for non-AJAX requests)
    if(!$is_ajax) {
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Delete Comment Function
if(isset($_POST['delete_comment'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        if($is_ajax) {
            json_response(false, 'Please login to delete comments.');
        }
        header('location: ../login.php');
        exit();
    }

    $comment_id = (int)$_POST['comment_id'];
    $user_id = (int)$_SESSION['user_id'];
    $user_role = $_SESSION['role'] ?? '';

    // Check if user owns the comment or is an admin
    $check_query = "SELECT user_id FROM comments WHERE comment_id = $comment_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $comment_data = mysqli_fetch_assoc($check_result);
        
        // Allow deletion if user owns the comment or is admin
        if($comment_data['user_id'] == $user_id || $user_role == 'Admin') {
            // Delete replies first
            $delete_replies = "DELETE FROM comments WHERE parent_id = $comment_id";
            mysqli_query($conn, $delete_replies);
            
            // Delete the comment
            $delete_query = "DELETE FROM comments WHERE comment_id = $comment_id";
            if(mysqli_query($conn, $delete_query)) {
                if($is_ajax) {
                    json_response(true, 'Comment deleted successfully!');
                }
                $_SESSION['success'] = "Comment deleted successfully!";
            } else {
                if($is_ajax) {
                    json_response(false, 'Failed to delete comment.');
                }
                $_SESSION['error'] = "Failed to delete comment.";
            }
        } else {
            if($is_ajax) {
                json_response(false, 'You don\'t have permission to delete this comment.');
            }
            $_SESSION['error'] = "You don't have permission to delete this comment.";
        }
    } else {
        if($is_ajax) {
            json_response(false, 'Comment not found.');
        }
        $_SESSION['error'] = "Comment not found.";
    }
    
    if(!$is_ajax) {
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

<?php
session_start();
include_once '../components/connection.php';

// Helper function to send JSON response
function json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Detect AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Add Comment Function
// Check for add_comment button OR if comment_text, entity_type, and entity_id are present (for AJAX)
if(isset($_POST['add_comment']) || (isset($_POST['comment_text']) && isset($_POST['entity_type']) && isset($_POST['entity_id']))) {
    // Validate user session - allow all logged-in users
    if(!isset($_SESSION['user_id'])) {
        if($is_ajax) {
            json_response(false, 'Please login to comment.');
        }
        header('location: ../login.php');
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];
    $entity_type = mysqli_real_escape_string($conn, $_POST['entity_type']);
    $entity_id = (int)$_POST['entity_id'];
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $comment_text = mysqli_real_escape_string($conn, trim($_POST['comment_text']));

    // Validate required fields
    if(empty($comment_text)) {
        if($is_ajax) {
            json_response(false, 'Comment cannot be empty.');
        }
        $_SESSION['error'] = "Comment cannot be empty.";
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validate entity_type
    $allowed_types = ['event', 'announcement', 'story', 'job', 'news'];
    if(!in_array($entity_type, $allowed_types)) {
        if($is_ajax) {
            json_response(false, 'Invalid entity type.');
        }
        $_SESSION['error'] = "Invalid entity type.";
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Insert comment
    $query = "INSERT INTO comments (user_id, entity_type, entity_id, parent_id, comment_text, created_at, updated_at) 
              VALUES ($user_id, '$entity_type', $entity_id, $parent_id, '$comment_text', NOW(), NOW())";
    
    if(mysqli_query($conn, $query)) {
        $comment_id = mysqli_insert_id($conn);
        $success_message = $parent_id > 0 ? "Reply posted successfully!" : "Comment posted successfully!";
        
        // If AJAX, return success immediately
        if($is_ajax) {
            json_response(true, $success_message, ['comment_id' => $comment_id]);
        }
        
        $_SESSION['success'] = $success_message;
        
        // Create notifications for all entity types
        $entity_config = [
            'event' => ['table' => 'events', 'id_field' => 'event_id', 'title_field' => 'title', 'name' => 'event'],
            'announcement' => ['table' => 'announcements', 'id_field' => 'announce_id', 'title_field' => 'title', 'name' => 'announcement'],
            'job' => ['table' => 'jobs', 'id_field' => 'job_id', 'title_field' => 'job_title', 'name' => 'job'],
            'news' => ['table' => 'news', 'id_field' => 'news_id', 'title_field' => 'title', 'name' => 'news'],
            'story' => ['table' => 'stories', 'id_field' => 'story_id', 'title_field' => 'title', 'name' => 'story']
        ];
        
        if(isset($entity_config[$entity_type])) {
            $config = $entity_config[$entity_type];
            $table = $config['table'];
            $id_field = $config['id_field'];
            $title_field = $config['title_field'];
            $entity_name = $config['name'];
            
            // Get entity author and title
            $entity_query = "SELECT user_id, $title_field FROM $table WHERE $id_field = $entity_id";
            $entity_result = mysqli_query($conn, $entity_query);
            
            if($entity_result && mysqli_num_rows($entity_result) > 0) {
                $entity_data = mysqli_fetch_assoc($entity_result);
                $entity_author_id = (int)$entity_data['user_id'];
                $entity_title = $entity_data[$title_field];
                
                // Get commenter name
                $commenter_query = "SELECT firstname, lastname FROM users WHERE user_id = $user_id";
                $commenter_result = mysqli_query($conn, $commenter_query);
                $commenter_name = 'Someone';
                if($commenter_result && mysqli_num_rows($commenter_result) > 0) {
                    $commenter_data = mysqli_fetch_assoc($commenter_result);
                    $commenter_name = $commenter_data['firstname'] . ' ' . $commenter_data['lastname'];
                }
                
                // Only notify if commenter is not the entity author
                if($entity_author_id != $user_id && $entity_author_id > 0) {
                    if($parent_id > 0) {
                        // This is a reply - notify the parent comment author
                        $parent_query = "SELECT user_id, firstname, lastname FROM comments c 
                                        JOIN users u ON c.user_id = u.user_id 
                                        WHERE c.comment_id = $parent_id";
                        $parent_result = mysqli_query($conn, $parent_query);
                        
                        if($parent_result && mysqli_num_rows($parent_result) > 0) {
                            $parent_data = mysqli_fetch_assoc($parent_result);
                            $parent_author_id = (int)$parent_data['user_id'];
                            
                            // Notify parent comment author if they're not the one replying
                            if($parent_author_id != $user_id && $parent_author_id > 0) {
                                $notification_message = mysqli_real_escape_string($conn, "$commenter_name replied to your comment on $entity_name: " . substr($entity_title, 0, 50));
                                $notify_query = "INSERT INTO notifications (user_id, entity_type, entity_id, comment_id, notification_type, message) 
                                                VALUES ($parent_author_id, '$entity_type', $entity_id, $comment_id, 'reply', '$notification_message')";
                                mysqli_query($conn, $notify_query);
                            }
                        }
                        
                        // Also notify entity author if they're not the parent comment author
                        if($entity_author_id != $parent_author_id && $entity_author_id > 0) {
                            $notification_message = mysqli_real_escape_string($conn, "$commenter_name replied to a comment on your $entity_name: " . substr($entity_title, 0, 50));
                            $notify_query = "INSERT INTO notifications (user_id, entity_type, entity_id, comment_id, notification_type, message) 
                                            VALUES ($entity_author_id, '$entity_type', $entity_id, $comment_id, 'reply', '$notification_message')";
                            mysqli_query($conn, $notify_query);
                        }
                    } else {
                        // This is a new comment - notify entity author
                        $notification_message = mysqli_real_escape_string($conn, "$commenter_name commented on your $entity_name: " . substr($entity_title, 0, 50));
                        $notify_query = "INSERT INTO notifications (user_id, entity_type, entity_id, comment_id, notification_type, message) 
                                        VALUES ($entity_author_id, '$entity_type', $entity_id, $comment_id, 'comment', '$notification_message')";
                        mysqli_query($conn, $notify_query);
                    }
                }
            }
        }
    } else {
        $error_message = "Failed to post comment. Please try again.";
        if($is_ajax) {
            json_response(false, $error_message);
        }
        $_SESSION['error'] = $error_message;
    }
    
    // Redirect back to the page (only for non-AJAX requests)
    if(!$is_ajax) {
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Delete Comment Function
if(isset($_POST['delete_comment'])) {
    // Validate user session
    if(!isset($_SESSION['user_id'])) {
        if($is_ajax) {
            json_response(false, 'Please login to delete comments.');
        }
        header('location: ../login.php');
        exit();
    }

    $comment_id = (int)$_POST['comment_id'];
    $user_id = (int)$_SESSION['user_id'];
    $user_role = $_SESSION['role'] ?? '';

    // Check if user owns the comment or is an admin
    $check_query = "SELECT user_id FROM comments WHERE comment_id = $comment_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $comment_data = mysqli_fetch_assoc($check_result);
        
        // Allow deletion if user owns the comment or is admin
        if($comment_data['user_id'] == $user_id || $user_role == 'Admin') {
            // Delete replies first
            $delete_replies = "DELETE FROM comments WHERE parent_id = $comment_id";
            mysqli_query($conn, $delete_replies);
            
            // Delete the comment
            $delete_query = "DELETE FROM comments WHERE comment_id = $comment_id";
            if(mysqli_query($conn, $delete_query)) {
                if($is_ajax) {
                    json_response(true, 'Comment deleted successfully!');
                }
                $_SESSION['success'] = "Comment deleted successfully!";
            } else {
                if($is_ajax) {
                    json_response(false, 'Failed to delete comment.');
                }
                $_SESSION['error'] = "Failed to delete comment.";
            }
        } else {
            if($is_ajax) {
                json_response(false, 'You don\'t have permission to delete this comment.');
            }
            $_SESSION['error'] = "You don't have permission to delete this comment.";
        }
    } else {
        if($is_ajax) {
            json_response(false, 'Comment not found.');
        }
        $_SESSION['error'] = "Comment not found.";
    }
    
    if(!$is_ajax) {
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

