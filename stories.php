<?php
session_start();
require_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Alumni') {
    header('Location: login.php');
    exit();
}

// Fetch stories with user details
$query = "SELECT s.*, CONCAT(u.firstname, ' ', u.lastname) AS username, u.role, u.image 
          FROM stories s 
          JOIN users u ON s.user_id = u.user_id 
          ORDER BY s.created_at DESC";
$stories = mysqli_query($conn, $query);

// Handle search
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
if (!empty($search_query)) {
    $query = "SELECT s.*, CONCAT(u.firstname, ' ', u.lastname) AS username, u.role, u.image 
              FROM stories s 
              JOIN users u ON s.user_id = u.user_id 
              WHERE s.title LIKE '%$search_query%' 
              OR s.description LIKE '%$search_query%'
              ORDER BY s.created_at DESC";
    $stories = mysqli_query($conn, $query);
}

include 'components/userHeader.php';

// Handle success/error messages
$success_message = '';
$error_message = '';
if(isset($_GET['success'])) {
    $success_message = 'Story created successfully!';
}
if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'missing_fields':
            $error_message = 'Please fill in all required fields.';
            break;
        case 'story_creation_failed':
            $error_message = 'Failed to create story. Please try again.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
}
?>

<!-- Main content starts here -->
<div class="min-h-screen bg-gray-900">
    <div class="max-w-2xl mx-auto p-4 sm:p-6 mt-16">
        <!-- Success/Error Messages -->
        <?php if($success_message): ?>
        <div id="successMessage" class="mb-4 p-4 bg-green-500/20 border border-green-500/50 text-green-400 rounded-lg">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('successMessage');
                if(msg) msg.remove();
            }, 5000);
        </script>
        <?php endif; ?>
        <?php if($error_message): ?>
        <div id="errorMessage" class="mb-4 p-4 bg-red-500/20 border border-red-500/50 text-red-400 rounded-lg">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('errorMessage');
                if(msg) msg.remove();
            }, 5000);
        </script>
        <?php endif; ?>
        <!-- Header Section -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Alumni Stories</h1>
                <p class="text-gray-400 text-sm">Share your journey, inspire others, and connect with fellow alumni through meaningful stories.</p>
            </div>
            <button onclick="openCreateStoryModal()" class="px-4 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Share Your Story
            </button>
        </div>

      
        <!-- Search Section -->
        <div class="mb-6">
            <form action="" method="GET">
                <div class="relative">
                    <input type="text" name="search" 
                           class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                           placeholder="Search stories..." 
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </form>
        </div>

        <!-- Stories Feed -->
        <div class="space-y-4">
            <?php if ($stories && mysqli_num_rows($stories) > 0): ?>
                <?php while ($story = mysqli_fetch_assoc($stories)): 
                    // Get like count and check if current user liked
                    $story_id = (int)$story['story_id'];
                    $like_count = 0;
                    $is_liked = false;
                    
                    // Check if likes table exists
                    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'likes'");
                    if(mysqli_num_rows($table_check) > 0) {
                        $like_count_query = "SELECT COUNT(*) as count FROM likes WHERE entity_type = 'story' AND entity_id = $story_id";
                        $like_result = mysqli_query($conn, $like_count_query);
                        if($like_result && mysqli_num_rows($like_result) > 0) {
                            $like_row = mysqli_fetch_assoc($like_result);
                            $like_count = (int)$like_row['count'];
                        }
                        if(isset($_SESSION['user_id'])) {
                            $user_id = (int)$_SESSION['user_id'];
                            $check_like_query = "SELECT like_id FROM likes WHERE user_id = $user_id AND entity_type = 'story' AND entity_id = $story_id";
                            $check_like_result = mysqli_query($conn, $check_like_query);
                            $is_liked = mysqli_num_rows($check_like_result) > 0;
                        }
                    }
                    
                    // Check if user has shared this post
                    $is_shared = false;
                    if(isset($_SESSION['user_id'])) {
                        $user_id = (int)$_SESSION['user_id'];
                        $shares_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'shares'");
                        if(mysqli_num_rows($shares_table_check) > 0) {
                            $check_share_query = "SELECT share_id FROM shares WHERE user_id = $user_id AND entity_type = 'story' AND entity_id = $story_id";
                            $check_share_result = mysqli_query($conn, $check_share_query);
                            $is_shared = mysqli_num_rows($check_share_result) > 0;
                        }
                    }
                ?>
                    <div class="bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 overflow-hidden shadow-lg" data-story-id="<?php echo $story_id; ?>">
                        <!-- Story Header - Facebook Style -->
                        <div class="p-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <?php 
                                $avatarName = htmlspecialchars($story['username']);
                                $imagePath = !empty($story['image']) ? "assets/uploads/alumni/" . $story['image'] : null;
                                $avatarUrl = $imagePath && file_exists($imagePath) ? $imagePath : "https://ui-avatars.com/api/?name=" . urlencode($avatarName) . "&background=random&color=fff";
                                ?>
                                <img src="<?php echo $avatarUrl; ?>" 
                                     class="w-10 h-10 rounded-full object-cover border-2 border-primary/30" 
                                     alt="<?php echo $avatarName; ?>'s profile"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($avatarName); ?>&background=random&color=fff'">
                                <div>
                                    <p class="text-white font-semibold hover:underline cursor-pointer"><?php echo htmlspecialchars($story['username']); ?></p>
                                    <div class="flex items-center space-x-2 text-xs text-gray-400">
                                        <span><?php echo date('M d', strtotime($story['created_at'])); ?></span>
                                        <span>•</span>
                                        <span><i class="fas fa-globe-americas"></i></span>
                                    </div>
                                </div>
                            </div>
                            <button class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-light/20 transition">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>

                        <!-- Story Content -->
                        <div class="px-3 pb-2">
                            <h3 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($story['title']); ?></h3>
                            <p class="text-gray-300 text-sm leading-relaxed">
                                <?php 
                                $description = strip_tags($story['description']);
                                echo htmlspecialchars($description);
                                ?>
                            </p>
                        </div>

                        <!-- Story Media -->
                        <?php if ($story['vimage']): ?>
                            <div class="relative bg-black w-full">
                                <?php if ($story['media_type'] === 'image'): ?>
                                    <img src="assets/uploads/stories/<?php echo htmlspecialchars($story['vimage']); ?>" 
                                         class="w-full max-h-[500px] object-cover cursor-pointer" 
                                         alt="Story Image"
                                         onclick="openStoryViewModal(<?php echo $story['story_id']; ?>)">
                                <?php else: ?>
                                    <video class="w-full max-h-[500px] object-contain cursor-pointer" controls onclick="openStoryViewModal(<?php echo $story['story_id']; ?>)">
                                        <source src="assets/uploads/stories/<?php echo htmlspecialchars($story['vimage']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Engagement Bar - Facebook Style -->
                        <div class="px-3 py-2 border-t border-b border-lighter/10 flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-4">
                                <button onclick="toggleLike('story', <?php echo $story_id; ?>, this)" 
                                        class="like-btn flex items-center space-x-1 transition <?php echo $is_liked ? 'text-blue-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                        data-liked="<?php echo $is_liked ? 'true' : 'false'; ?>"
                                        data-entity-type="story"
                                        data-entity-id="<?php echo $story_id; ?>">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>Like</span>
                                    <?php if($like_count > 0): ?>
                                        <span class="like-count ml-1"><?php echo $like_count; ?></span>
                                    <?php endif; ?>
                                </button>
                                <button onclick="openStoryViewModal(<?php echo $story['story_id']; ?>)" class="flex items-center space-x-1 text-gray-400 hover:text-blue-400 transition">
                                    <i class="fas fa-comment"></i>
                                    <span>Comment</span>
                                </button>
                                <button onclick="shareToAccount('story', <?php echo $story_id; ?>, this)" 
                                        class="share-btn flex items-center space-x-1 transition <?php echo $is_shared ? 'text-green-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                        data-shared="<?php echo $is_shared ? 'true' : 'false'; ?>"
                                        data-entity-type="story"
                                        data-entity-id="<?php echo $story_id; ?>">
                                    <i class="fas fa-share"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                        </div>

                        <!-- Story Footer - Comments Preview -->
                        <div class="px-3 py-2">
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                                <span>
                                    <?php
                                    // Get comment count
                                    $story_id = (int)$story['story_id'];
                                    $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'story' AND entity_id = $story_id";
                                    $comment_result = mysqli_query($conn, $comment_count_query);
                                    $comment_count = 0;
                                    if($comment_result && mysqli_num_rows($comment_result) > 0) {
                                        $comment_row = mysqli_fetch_assoc($comment_result);
                                        $comment_count = $comment_row['count'];
                                    }
                                    ?>
                                    <?php if($comment_count > 0): ?>
                                        <i class="fas fa-comments mr-1"></i> <?php echo $comment_count; ?> comment<?php echo $comment_count > 1 ? 's' : ''; ?>
                                    <?php endif; ?>
                                </span>
                                <span class="hover:underline cursor-pointer" onclick="openStoryViewModal(<?php echo $story['story_id']; ?>)">View all</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 shadow-lg">
                    <i class="fas fa-book-open text-6xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">No Stories Found</h3>
                    <p class="text-gray-400 mb-4">Be the first to share your story!</p>
                    <button onclick="openCreateStoryModal()" class="px-6 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-plus mr-2"></i> Share Your Story
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Story Modal Container -->
<div id="storyModalContainer"></div>

<!-- Create Story Modal -->
<div id="createStoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Share Your Story</h3>
            <button onclick="closeCreateStoryModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="createStoryForm" action="controllers/storiesController.php" method="POST" enctype="multipart/form-data">
            <div class="space-y-4">
                <div>
                    <label for="create_title" class="block text-sm text-gray-400 mb-1">Title *</label>
                    <input type="text" id="create_title" name="title" required
                           class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                           placeholder="Enter a compelling title for your story">
                </div>
                
                <div>
                    <label for="create_description" class="block text-sm text-gray-400 mb-1">Story Content *</label>
                    <textarea id="create_description" name="description" required rows="6"
                              class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                              placeholder="Share your experience, achievements, or journey..."></textarea>
                </div>
                
                <div>
                    <label for="create_media_type" class="block text-sm text-gray-400 mb-1">Media Type *</label>
                    <select id="create_media_type" name="media_type" required
                            class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        <option value="">Select Media Type</option>
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                
                <div>
                    <label for="create_media" class="block text-sm text-gray-400 mb-1">Media Upload</label>
                    <input type="file" id="create_media" name="media" accept="image/*,video/*"
                           class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <p class="text-xs text-gray-400 mt-1">
                        Max file size: 5MB for images, 50MB for videos. Supported formats: JPG, PNG, GIF, MP4, MOV
                    </p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeCreateStoryModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="create_story" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    Share Story
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-submit form when search input changes
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }

    // Show success message (removed bootstrap modal)
    <?php if (isset($success_message)): ?>
    // Success message is already displayed in the page
    <?php endif; ?>

    // Update file input accept attribute based on media type
    const mediaTypeSelect = document.querySelector('select[name="media_type"]');
    if (mediaTypeSelect) {
        const fileInput = document.querySelector('input[name="media"]');
        if (fileInput) {
            mediaTypeSelect.addEventListener('change', function() {
                if (this.value === 'image') {
                    fileInput.accept = 'image/*';
                } else {
                    fileInput.accept = 'video/*';
                }
            });
        }
    }

    // Create Story Modal Functions
    function openCreateStoryModal() {
        document.getElementById('createStoryModal').classList.remove('hidden');
    }

    function closeCreateStoryModal() {
        document.getElementById('createStoryModal').classList.add('hidden');
    }

    // Update create form media type select
    const createMediaTypeSelect = document.querySelector('#create_media_type');
    if (createMediaTypeSelect) {
        const createFileInput = document.querySelector('#create_media');
        if (createFileInput) {
            createMediaTypeSelect.addEventListener('change', function() {
                if (this.value === 'image') {
                    createFileInput.accept = 'image/*';
                } else if (this.value === 'video') {
                    createFileInput.accept = 'video/*';
                } else {
                    createFileInput.accept = 'image/*,video/*';
                }
            });
        }
    }

function openStoryViewModal(storyId) {
    fetch('forms/alumniModal/modalStory.php?id=' + storyId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('storyModalContainer').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            if(typeof showToast !== 'undefined') {
                showToast('Error loading story details', 'error');
            }
        });
}

function closeStoryModal() {
    document.getElementById('storyModalContainer').innerHTML = '';
}

    // Like/Unlike functionality
    function toggleLike(entityType, entityId, button) {
        <?php if(!isset($_SESSION['user_id'])): ?>
            alert('Please login to like posts.');
            return;
        <?php endif; ?>
        
        const isLiked = button.getAttribute('data-liked') === 'true';
        const action = isLiked ? 'unlike' : 'like';
        const likeIcon = button.querySelector('i');
        const likeCountSpan = button.querySelector('.like-count');
        
        // Disable button during request
        button.disabled = true;
        
        // Calculate base path
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/') || '';
        
        fetch(basePath + '/controllers/likeController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `entity_type=${entityType}&entity_id=${entityId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Update button state
                button.setAttribute('data-liked', data.data.liked ? 'true' : 'false');
                if(data.data.liked) {
                    button.classList.remove('text-gray-400');
                    button.classList.add('text-blue-400');
                } else {
                    button.classList.remove('text-blue-400');
                    button.classList.add('text-gray-400');
                }
                
                // Update like count
                if(data.data.count > 0) {
                    if(likeCountSpan) {
                        likeCountSpan.textContent = data.data.count;
                    } else {
                        const countSpan = document.createElement('span');
                        countSpan.className = 'like-count ml-1';
                        countSpan.textContent = data.data.count;
                        button.appendChild(countSpan);
                    }
                } else {
                    if(likeCountSpan) {
                        likeCountSpan.remove();
                    }
                }
            } else {
                if(typeof showToast !== 'undefined') {
                    showToast(data.message || 'Failed to update like.', 'error');
                } else {
                    alert(data.message || 'Failed to update like.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if(typeof showToast !== 'undefined') {
                showToast('Failed to update like. Please try again.', 'error');
            } else {
                alert('Failed to update like. Please try again.');
            }
        })
        .finally(() => {
            button.disabled = false;
        });
    }

    // Share to account functionality
    function shareToAccount(entityType, entityId, button) {
        <?php if(!isset($_SESSION['user_id'])): ?>
            alert('Please login to share posts.');
            return;
        <?php endif; ?>
        
        const isShared = button.getAttribute('data-shared') === 'true';
        const action = isShared ? 'unshare' : 'share';
        
        // Disable button during request
        button.disabled = true;
        
        // Calculate base path
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/') || '';
        
        fetch(basePath + '/controllers/shareController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `entity_type=${entityType}&entity_id=${entityId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Update button state
                button.setAttribute('data-shared', data.data.shared ? 'true' : 'false');
                if(data.data.shared) {
                    button.classList.remove('text-gray-400');
                    button.classList.add('text-green-400');
                } else {
                    button.classList.remove('text-green-400');
                    button.classList.add('text-gray-400');
                }
                
                // Show success message
                if(typeof showToast !== 'undefined') {
                    showToast(data.message || 'Shared to your account!', 'success');
                } else {
                    alert(data.message || 'Shared to your account!');
                }
            } else {
                if(typeof showToast !== 'undefined') {
                    showToast(data.message || 'Failed to share.', 'error');
                } else {
                    alert(data.message || 'Failed to share.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if(typeof showToast !== 'undefined') {
                showToast('Failed to share. Please try again.', 'error');
            } else {
                alert('Failed to share. Please try again.');
            }
        })
        .finally(() => {
            button.disabled = false;
        });
    }
</script>
