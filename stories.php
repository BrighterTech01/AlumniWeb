<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Alumni') {
    header('Location: login.php');
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM view_user_details WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Construct full name
$full_name = $user['firstname'] . ' ' . 
             ($user['middlename'] ? $user['middlename'] . ' ' : '') . 
             $user['lastname'];

// Handle story submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_story'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $media_type = isset($_POST['media_type']) ? $_POST['media_type'] : 'image';
    
    // Handle media upload
    $vimage = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/stories/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_video_extensions = ['mp4', 'webm', 'ogg'];
        
        if ($media_type === 'image' && in_array($file_extension, $allowed_image_extensions)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['media']['tmp_name'], $upload_path)) {
                $vimage = $upload_path;
            }
        } elseif ($media_type === 'video' && in_array($file_extension, $allowed_video_extensions)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['media']['tmp_name'], $upload_path)) {
                $vimage = $upload_path;
            }
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO stories (user_id, title, description, vimage, media_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("issss", $user_id, $title, $description, $vimage, $media_type);
    
    if ($stmt->execute()) {
        $success_message = "Your story has been shared successfully!";
    } else {
        $error_message = "Error sharing story. Please try again.";
    }
}

// Get stories
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT s.*, u.firstname, u.lastname, u.batch, u.course_name, u.profile_image 
          FROM stories s 
          JOIN view_user_details u ON s.user_id = u.user_id";

if ($search_query) {
    $query .= " WHERE s.title LIKE '%" . mysqli_real_escape_string($conn, $search_query) . "%' 
                OR s.description LIKE '%" . mysqli_real_escape_string($conn, $search_query) . "%'";
}

$query .= " ORDER BY s.created_at DESC";
$stories = $conn->query($query);

// Include header
include 'components/userHeader.php';
?>

<!-- Main content starts here -->
<div class="p-6 mt-16">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Alumni Stories</h1>
            <p class="text-gray-400">Share your journey, inspire others, and connect with fellow alumni through meaningful stories.</p>
        </div>
        <button class="mt-4 md:mt-0 px-6 py-3 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition-opacity flex items-center space-x-2" 
                data-bs-toggle="modal" data-bs-target="#shareStoryModal">
            <i class="fas fa-pen-fancy"></i>
            <span>Share Your Story</span>
        </button>
    </div>

    <!-- Search Section -->
    <div class="mb-8">
        <form action="" method="GET" class="max-w-xl">
            <div class="relative">
                <input type="text" name="search" 
                       class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                       placeholder="Search stories..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </form>
    </div>

    <!-- Stories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if ($stories->num_rows > 0): ?>
            <?php while ($story = $stories->fetch_assoc()): ?>
                <div class="bg-darker/50 backdrop-blur-md rounded-xl border border-lighter/10 overflow-hidden hover:border-primary/30 transition-all duration-300">
                    <?php if ($story['vimage']): ?>
                        <div class="relative h-48">
                            <?php if ($story['media_type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($story['vimage']); ?>" 
                                     class="w-full h-full object-cover" alt="Story Image">
                            <?php else: ?>
                                <video class="w-full h-full object-cover" controls>
                                    <source src="<?php echo htmlspecialchars($story['vimage']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                            <span class="absolute top-4 right-4 px-3 py-1 bg-darker/80 backdrop-blur-sm rounded-full text-xs font-medium text-white">
                                <i class="fas fa-<?php echo $story['media_type'] === 'image' ? 'image' : 'video'; ?> mr-1"></i>
                                <?php echo ucfirst($story['media_type']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-white mb-3"><?php echo htmlspecialchars($story['title']); ?></h3>
                        <p class="text-gray-400 mb-4">
                            <?php 
                            $description = strip_tags($story['description']);
                            echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                            ?>
                        </p>
                        <div class="flex items-center space-x-3 mb-4">
                            <img src="<?php echo $story['profile_image'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($story['firstname'] . ' ' . $story['lastname']) . '&background=random'; ?>" 
                                 class="w-10 h-10 rounded-full" alt="Profile">
                            <div>
                                <p class="text-white font-medium"><?php echo htmlspecialchars($story['firstname'] . ' ' . $story['lastname']); ?></p>
                                <p class="text-sm text-gray-400">
                                    Batch <?php echo htmlspecialchars($story['batch']); ?> • 
                                    <?php echo htmlspecialchars($story['course_name']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-lighter/10">
                            <span class="text-sm text-gray-400">
                                <?php echo date('M d, Y', strtotime($story['created_at'])); ?>
                            </span>
                            <a href="story_details.php?id=<?php echo $story['story_id']; ?>" 
                               class="px-4 py-2 bg-primary/20 text-primary rounded-lg hover:bg-primary/30 transition-colors">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full">
                <div class="text-center py-12">
                    <i class="fas fa-book-open text-5xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">No Stories Found</h3>
                    <p class="text-gray-400">Be the first to share your story!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Share Story Modal -->
<div class="modal fade" id="shareStoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-darker border border-lighter/10">
            <div class="modal-header border-b border-lighter/10">
                <h5 class="modal-title text-white">Share Your Story</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-4"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-4"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Title</label>
                        <input type="text" name="title" 
                               class="w-full bg-light/20 text-white rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50" 
                               required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Story Content</label>
                        <textarea name="description" 
                                  class="w-full bg-light/20 text-white rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50" 
                                  rows="6" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Media Type</label>
                        <select name="media_type" 
                                class="w-full bg-light/20 text-white rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50" 
                                required>
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Media File</label>
                        <input type="file" name="media" 
                               class="w-full bg-light/20 text-white rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50" 
                               accept="image/*,video/*" required>
                        <p class="text-sm text-gray-400 mt-2">
                            For images: JPG, PNG, GIF (max 5MB)<br>
                            For videos: MP4, WebM, OGG (max 50MB)
                        </p>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="submit_story" 
                                class="px-6 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition-opacity">
                            Share Story
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-submit form when search input changes
    document.querySelector('input[name="search"]').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });

    // Show success message in modal
    <?php if (isset($success_message)): ?>
    var shareStoryModal = new bootstrap.Modal(document.getElementById('shareStoryModal'));
    shareStoryModal.show();
    <?php endif; ?>

    // Update file input accept attribute based on media type
    document.querySelector('select[name="media_type"]').addEventListener('change', function() {
        const fileInput = document.querySelector('input[name="media"]');
        if (this.value === 'image') {
            fileInput.accept = 'image/*';
        } else {
            fileInput.accept = 'video/*';
        }
    });
</script>
