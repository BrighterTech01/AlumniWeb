<?php
// Only start session if it hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Alumni') {
    header("Location: login.php");
    exit();
}

// Fetch user details from users table
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fetch all announcements
$announcements_query = "SELECT a.*, CONCAT(u.firstname, ' ', u.lastname) AS username 
                        FROM announcements a 
                        LEFT JOIN users u ON a.user_id = u.user_id 
                        WHERE a.status = 'Active' 
                        ORDER BY a.date_posted DESC";
$announcements_result = mysqli_query($conn, $announcements_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMSCST Alumni Portal - Announcements</title>
    <link rel="stylesheet" href="assets/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .neon-glow {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-white">
    <?php include 'components/userHeader.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="max-w-6xl mx-auto">
            <!-- Success/Error Messages -->
            <?php 
            $success_message = '';
            $error_message = '';
            if(isset($_GET['success'])) {
                $success_message = 'Announcement created successfully!';
            }
            if(isset($_GET['error'])) {
                switch($_GET['error']) {
                    case 'missing_fields':
                        $error_message = 'Please fill in all required fields.';
                        break;
                    case 'announcement_creation_failed':
                        $error_message = 'Failed to create announcement. Please try again.';
                        break;
                    default:
                        $error_message = 'An error occurred. Please try again.';
                }
            }
            ?>
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
            <div class="glass rounded-xl p-6 border border-lighter/10 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-2">Announcements</h1>
                    <p class="text-gray-400">Stay updated with the latest announcements from your alma mater.</p>
                </div>
                <button onclick="openCreateAnnouncementModal()" class="px-4 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i> Create Announcement
                </button>
            </div>
            <div class="space-y-4">
                <?php if (mysqli_num_rows($announcements_result) > 0): ?>
                    <?php while ($announcement = mysqli_fetch_assoc($announcements_result)): 
                        // Get like count and check if current user liked
                        $announce_id = (int)$announcement['announce_id'];
                        $like_count = 0;
                        $is_liked = false;
                        
                        // Check if likes table exists
                        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'likes'");
                        if(mysqli_num_rows($table_check) > 0) {
                            $like_count_query = "SELECT COUNT(*) as count FROM likes WHERE entity_type = 'announcement' AND entity_id = $announce_id";
                            $like_result = mysqli_query($conn, $like_count_query);
                            if($like_result && mysqli_num_rows($like_result) > 0) {
                                $like_row = mysqli_fetch_assoc($like_result);
                                $like_count = (int)$like_row['count'];
                            }
                            if(isset($_SESSION['user_id'])) {
                                $user_id = (int)$_SESSION['user_id'];
                                $check_like_query = "SELECT like_id FROM likes WHERE user_id = $user_id AND entity_type = 'announcement' AND entity_id = $announce_id";
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
                                $check_share_query = "SELECT share_id FROM shares WHERE user_id = $user_id AND entity_type = 'announcement' AND entity_id = $announce_id";
                                $check_share_result = mysqli_query($conn, $check_share_query);
                                $is_shared = mysqli_num_rows($check_share_result) > 0;
                            }
                        }
                    ?>
                        <div class="bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 overflow-hidden shadow-lg" data-announce-id="<?php echo $announce_id; ?>">
                            <!-- Announcement Header - Facebook Style -->
                            <div class="p-3 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <?php 
                                    // Get user info for announcement author
                                    $announce_user_query = "SELECT u.firstname, u.lastname, u.middlename, u.image, u.role FROM users u WHERE u.user_id = " . (int)$announcement['user_id'];
                                    $announce_user_result = mysqli_query($conn, $announce_user_query);
                                    $announce_user = mysqli_fetch_assoc($announce_user_result);
                                    $author_fullname = trim(($announce_user['firstname'] ?? '') . ' ' . ($announce_user['middlename'] ?? '') . ' ' . ($announce_user['lastname'] ?? ''));
                                    $avatarName = htmlspecialchars($author_fullname ?: 'Admin');
                                    $imagePath = !empty($announce_user['image']) ? "assets/uploads/alumni/" . $announce_user['image'] : null;
                                    $avatarUrl = $imagePath && file_exists($imagePath) ? $imagePath : "https://ui-avatars.com/api/?name=" . urlencode($avatarName) . "&background=random&color=fff";
                                    ?>
                                    <img src="<?php echo $avatarUrl; ?>" 
                                         class="w-10 h-10 rounded-full object-cover border-2 border-primary/30" 
                                         alt="<?php echo $avatarName; ?>'s profile"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($avatarName); ?>&background=random&color=fff'">
                                    <div>
                                        <p class="text-white font-semibold hover:underline cursor-pointer"><?php echo htmlspecialchars($author_fullname ?: 'Admin'); ?></p>
                                        <div class="flex items-center space-x-2 text-xs text-gray-400">
                                            <span><?php echo date('M d', strtotime($announcement['date_posted'])); ?></span>
                                            <span>•</span>
                                            <span><i class="fas fa-globe-americas"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-light/20 transition">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>

                            <!-- Announcement Content -->
                            <div class="px-3 pb-2">
                                <h3 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                <p class="text-gray-300 text-sm leading-relaxed">
                                    <?php echo htmlspecialchars($announcement['content']); ?>
                                </p>
                                
                                <?php if (!empty($announcement['attachment_url'])): ?>
                                    <div class="mt-3 bg-light/10 rounded-lg p-3 border border-primary/20">
                                        <a href="assets/uploads/announcements/<?php echo htmlspecialchars($announcement['attachment_url']); ?>" 
                                           target="_blank" 
                                           class="flex items-center space-x-2 text-primary hover:text-primary/80 transition">
                                            <i class="fas fa-paperclip text-lg"></i>
                                            <span class="text-sm font-medium">View Attachment</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Engagement Bar - Facebook Style -->
                            <div class="px-3 py-2 border-t border-b border-lighter/10 flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-4">
                                    <button onclick="toggleLike('announcement', <?php echo $announce_id; ?>, this)" 
                                            class="like-btn flex items-center space-x-1 transition <?php echo $is_liked ? 'text-blue-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                            data-liked="<?php echo $is_liked ? 'true' : 'false'; ?>"
                                            data-entity-type="announcement"
                                            data-entity-id="<?php echo $announce_id; ?>">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span>Like</span>
                                        <?php if($like_count > 0): ?>
                                            <span class="like-count ml-1"><?php echo $like_count; ?></span>
                                        <?php endif; ?>
                                    </button>
                                    <button onclick="openAnnouncementModal(<?php echo $announcement['announce_id']; ?>)" class="flex items-center space-x-1 text-gray-400 hover:text-blue-400 transition">
                                        <i class="fas fa-comment"></i>
                                        <span>Comment</span>
                                    </button>
                                    <button onclick="shareToAccount('announcement', <?php echo $announce_id; ?>, this)" 
                                            class="share-btn flex items-center space-x-1 transition <?php echo $is_shared ? 'text-green-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                            data-shared="<?php echo $is_shared ? 'true' : 'false'; ?>"
                                            data-entity-type="announcement"
                                            data-entity-id="<?php echo $announce_id; ?>">
                                        <i class="fas fa-share"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Announcement Footer - Comments Preview -->
                            <div class="px-3 py-2">
                                <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                                    <span>
                                        <?php
                                        // Get comment count
                                        $announce_id = (int)$announcement['announce_id'];
                                        $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'announcement' AND entity_id = $announce_id";
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
                                    <span class="hover:underline cursor-pointer" onclick="openAnnouncementModal(<?php echo $announcement['announce_id']; ?>)">View all</span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-16 bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 shadow-lg">
                        <i class="fas fa-bullhorn text-6xl text-gray-600 mb-4"></i>
                        <h3 class="text-xl font-semibold text-white mb-2">No Announcements Found</h3>
                        <p class="text-gray-400 mb-4">There are no announcements at the moment.</p>
                        <button onclick="openCreateAnnouncementModal()" class="px-6 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition">
                            <i class="fas fa-plus mr-2"></i> Create Announcement
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Modal container -->
    <div id="announcementModalContainer"></div>

    <!-- Create Announcement Modal -->
    <div id="createAnnouncementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Create Announcement</h3>
                <button onclick="closeCreateAnnouncementModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="createAnnouncementForm" action="controllers/announcementController.php" method="POST" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label for="create_announcement_title" class="block text-sm text-gray-400 mb-1">Title *</label>
                        <input type="text" id="create_announcement_title" name="title" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="Enter announcement title">
                    </div>
                    
                    <div>
                        <label for="create_announcement_content" class="block text-sm text-gray-400 mb-1">Content *</label>
                        <textarea id="create_announcement_content" name="content" required rows="6"
                                  class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                  placeholder="Enter announcement content..."></textarea>
                    </div>
                    
                    <div>
                        <label for="create_announcement_status" class="block text-sm text-gray-400 mb-1">Status *</label>
                        <select id="create_announcement_status" name="status" required
                                class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                            <option value="">Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="create_announcement_attachment" class="block text-sm text-gray-400 mb-1">Attachment</label>
                        <input type="file" id="create_announcement_attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        <p class="text-xs text-gray-400 mt-1">Max file size: 5MB. Supported formats: PDF, DOC, DOCX, TXT, JPG, PNG</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeCreateAnnouncementModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit" name="create_announcement" 
                            class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                        Create Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function openAnnouncementModal(announceId) {
        fetch('forms/alumniModal/modalAnnouncements.php?id=' + announceId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('announcementModalContainer').innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading modal:', error);
                alert('Failed to load announcement details. Please try again.');
            });
    }
    
    // Make function globally accessible
    window.openAnnouncementModal = openAnnouncementModal;
    function closeAnnouncementModal() {
        document.getElementById('announcementModalContainer').innerHTML = '';
    }

    // Create Announcement Modal Functions
    function openCreateAnnouncementModal() {
        document.getElementById('createAnnouncementModal').classList.remove('hidden');
    }

    function closeCreateAnnouncementModal() {
        document.getElementById('createAnnouncementModal').classList.add('hidden');
    }

    // Like/Unlike functionality
    function toggleLike(entityType, entityId, button) {
        <?php if(!isset($_SESSION['user_id'])): ?>
            alert('Please login to like posts.');
            return;
        <?php endif; ?>
        
        const isLiked = button.getAttribute('data-liked') === 'true';
        const action = isLiked ? 'unlike' : 'like';
        const likeCountSpan = button.querySelector('.like-count');
        
        button.disabled = true;
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
                button.setAttribute('data-liked', data.data.liked ? 'true' : 'false');
                if(data.data.liked) {
                    button.classList.remove('text-gray-400');
                    button.classList.add('text-blue-400');
                } else {
                    button.classList.remove('text-blue-400');
                    button.classList.add('text-gray-400');
                }
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
                    if(likeCountSpan) likeCountSpan.remove();
                }
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => button.disabled = false);
    }

    function shareToAccount(entityType, entityId, button) {
        <?php if(!isset($_SESSION['user_id'])): ?>
            alert('Please login to share posts.');
            return;
        <?php endif; ?>
        
        const isShared = button.getAttribute('data-shared') === 'true';
        const action = isShared ? 'unshare' : 'share';
        
        button.disabled = true;
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
                button.setAttribute('data-shared', data.data.shared ? 'true' : 'false');
                if(data.data.shared) {
                    button.classList.remove('text-gray-400');
                    button.classList.add('text-green-400');
                } else {
                    button.classList.remove('text-green-400');
                    button.classList.add('text-gray-400');
                }
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
        .finally(() => button.disabled = false);
    }
    </script>
</body>
</html>