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

// Construct full name
$fullname = trim($user['firstname'] . ' ' . ($user['middlename'] ? $user['middlename'] . ' ' : '') . $user['lastname']);

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the events query based on filters
$events_query = "SELECT e.*, u.firstname, u.lastname, u.image 
                 FROM events e 
                 LEFT JOIN users u ON e.user_id = u.user_id 
                 WHERE 1=1";

if ($filter === 'upcoming') {
    $events_query .= " AND e.schedule >= CURDATE()";
} elseif ($filter === 'past') {
    $events_query .= " AND e.schedule < CURDATE()";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $events_query .= " AND (e.title LIKE '%$search%' OR e.content LIKE '%$search%' OR e.location LIKE '%$search%')";
}

$events_query .= " ORDER BY e.schedule ASC";
$events_result = mysqli_query($conn, $events_query);

// Fetch recent news
$news_query = "SELECT n.*, CONCAT(u.firstname, ' ', u.lastname) AS username 
               FROM news n 
               LEFT JOIN users u ON n.user_id = u.user_id 
               WHERE n.status = 'Published' 
               ORDER BY n.date_posted DESC 
               LIMIT 3";
$news_result = mysqli_query($conn, $news_query);

// Fetch recent job postings
$jobs_query = "SELECT j.* 
               FROM jobs j 
               WHERE j.application_deadline >= CURDATE() 
               ORDER BY j.created_at DESC 
               LIMIT 3";
$jobs_result = mysqli_query($conn, $jobs_query);
?>

<?php include 'components/userHeader.php'; 

// Handle success/error messages
$success_message = '';
$error_message = '';
if(isset($_GET['success'])) {
    $success_message = 'News created successfully!';
}
if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'missing_fields':
            $error_message = 'Please fill in all required fields.';
            break;
        case 'news_creation_failed':
            $error_message = 'Failed to create news. Please try again.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
}
?>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-white">
   

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="max-w-7xl mx-auto">
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
            <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 mb-6">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 lg:gap-6">
                    <div class="w-full lg:w-auto">
                        <h1 class="text-xl sm:text-2xl font-bold mb-2">News & Updates</h1>
                        <p class="text-sm sm:text-base text-gray-400">Stay updated with the latest news and announcements.</p>
                    </div>
                    <button onclick="openCreateNewsModal()" class="px-4 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> Create News
                    </button>
                    <div class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                        <div class="relative flex-1 sm:flex-none">
                            <input type="search" 
                                   placeholder="Search events..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full sm:w-auto bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-sm">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="?filter=upcoming<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="flex-1 sm:flex-none text-center px-3 sm:px-4 py-2 rounded-lg text-sm <?php echo $filter === 'upcoming' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'bg-light/20 text-gray-400 hover:text-white transition-colors'; ?>">
                                Upcoming
                            </a>
                            <a href="?filter=past<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="flex-1 sm:flex-none text-center px-3 sm:px-4 py-2 rounded-lg text-sm <?php echo $filter === 'past' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'bg-light/20 text-gray-400 hover:text-white transition-colors'; ?>">
                                Past Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- News Feed -->
            <div class="space-y-4 mt-6">
                <?php if (mysqli_num_rows($news_result) > 0): ?>
                    <?php while ($news = mysqli_fetch_assoc($news_result)): 
                        // Get like count and check if current user liked
                        $news_id = (int)$news['news_id'];
                        $like_count = 0;
                        $is_liked = false;
                        
                        // Check if likes table exists
                        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'likes'");
                        if(mysqli_num_rows($table_check) > 0) {
                            $like_count_query = "SELECT COUNT(*) as count FROM likes WHERE entity_type = 'news' AND entity_id = $news_id";
                            $like_result = mysqli_query($conn, $like_count_query);
                            if($like_result && mysqli_num_rows($like_result) > 0) {
                                $like_row = mysqli_fetch_assoc($like_result);
                                $like_count = (int)$like_row['count'];
                            }
                            if(isset($_SESSION['user_id'])) {
                                $user_id = (int)$_SESSION['user_id'];
                                $check_like_query = "SELECT like_id FROM likes WHERE user_id = $user_id AND entity_type = 'news' AND entity_id = $news_id";
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
                                $check_share_query = "SELECT share_id FROM shares WHERE user_id = $user_id AND entity_type = 'news' AND entity_id = $news_id";
                                $check_share_result = mysqli_query($conn, $check_share_query);
                                $is_shared = mysqli_num_rows($check_share_result) > 0;
                            }
                        }
                    ?>
                        <div class="bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 overflow-hidden shadow-lg" data-news-id="<?php echo $news_id; ?>">
                            <!-- News Header - Facebook Style -->
                            <div class="p-3 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <?php 
                                    // Get user info for news author
                                    $news_user_query = "SELECT u.firstname, u.lastname, u.middlename, u.image, u.role FROM users u WHERE u.user_id = " . (int)$news['user_id'];
                                    $news_user_result = mysqli_query($conn, $news_user_query);
                                    $news_user = mysqli_fetch_assoc($news_user_result);
                                    $author_fullname = trim(($news_user['firstname'] ?? '') . ' ' . ($news_user['middlename'] ?? '') . ' ' . ($news_user['lastname'] ?? ''));
                                    $avatarName = htmlspecialchars($author_fullname ?: 'Admin');
                                    $imagePath = !empty($news_user['image']) ? "assets/uploads/alumni/" . $news_user['image'] : null;
                                    $avatarUrl = $imagePath && file_exists($imagePath) ? $imagePath : "https://ui-avatars.com/api/?name=" . urlencode($avatarName) . "&background=random&color=fff";
                                    ?>
                                    <img src="<?php echo $avatarUrl; ?>" 
                                         class="w-10 h-10 rounded-full object-cover border-2 border-primary/30" 
                                         alt="<?php echo $avatarName; ?>'s profile"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($avatarName); ?>&background=random&color=fff'">
                                    <div>
                                        <p class="text-white font-semibold hover:underline cursor-pointer"><?php echo htmlspecialchars($author_fullname ?: 'Admin'); ?></p>
                                        <div class="flex items-center space-x-2 text-xs text-gray-400">
                                            <span><?php echo date('M d', strtotime($news['date_posted'])); ?></span>
                                            <span>•</span>
                                            <span><i class="fas fa-globe-americas"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-light/20 transition">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>

                            <!-- News Content -->
                            <div class="px-3 pb-2">
                                <h3 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($news['title']); ?></h3>
                                <p class="text-gray-300 text-sm leading-relaxed">
                                    <?php 
                                    $content = strip_tags($news['content']);
                                    echo htmlspecialchars($content);
                                    ?>
                                </p>
                            </div>

                            <!-- News Image -->
                            <?php if (!empty($news['image_url'])): ?>
                                <div class="relative bg-black w-full">
                                    <img src="assets/uploads/news/<?php echo htmlspecialchars($news['image_url']); ?>" 
                                         class="w-full max-h-[500px] object-cover cursor-pointer" 
                                         alt="<?php echo htmlspecialchars($news['title']); ?>"
                                         onclick="openNewsModal(<?php echo $news['news_id']; ?>)">
                                </div>
                            <?php endif; ?>

                            <!-- Engagement Bar - Facebook Style -->
                            <div class="px-3 py-2 border-t border-b border-lighter/10 flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-4">
                                    <button onclick="toggleLike('news', <?php echo $news_id; ?>, this)" 
                                            class="like-btn flex items-center space-x-1 transition <?php echo $is_liked ? 'text-blue-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                            data-liked="<?php echo $is_liked ? 'true' : 'false'; ?>"
                                            data-entity-type="news"
                                            data-entity-id="<?php echo $news_id; ?>">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span>Like</span>
                                        <?php if($like_count > 0): ?>
                                            <span class="like-count ml-1"><?php echo $like_count; ?></span>
                                        <?php endif; ?>
                                    </button>
                                    <button onclick="openNewsModal(<?php echo $news['news_id']; ?>)" class="flex items-center space-x-1 text-gray-400 hover:text-blue-400 transition">
                                        <i class="fas fa-comment"></i>
                                        <span>Comment</span>
                                    </button>
                                    <button onclick="shareToAccount('news', <?php echo $news_id; ?>, this)" 
                                            class="share-btn flex items-center space-x-1 transition <?php echo $is_shared ? 'text-green-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                            data-shared="<?php echo $is_shared ? 'true' : 'false'; ?>"
                                            data-entity-type="news"
                                            data-entity-id="<?php echo $news_id; ?>">
                                        <i class="fas fa-share"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                            </div>

                            <!-- News Footer - Comments Preview -->
                            <div class="px-3 py-2">
                                <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                                    <span>
                                        <?php
                                        // Get comment count
                                        $news_id = (int)$news['news_id'];
                                        $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'news' AND entity_id = $news_id";
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
                                    <span class="hover:underline cursor-pointer" onclick="openNewsModal(<?php echo $news['news_id']; ?>)">View all</span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-16 bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 shadow-lg">
                        <i class="fas fa-newspaper text-6xl text-gray-600 mb-4"></i>
                        <h3 class="text-xl font-semibold text-white mb-2">No News Found</h3>
                        <p class="text-gray-400 mb-4">There are no news articles at the moment.</p>
                        <button onclick="openCreateNewsModal()" class="px-6 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition">
                            <i class="fas fa-plus mr-2"></i> Create News
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal container -->
    <div id="newsModalContainer"></div>

    <!-- Create News Modal -->
    <div id="createNewsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Create News</h3>
                <button onclick="closeCreateNewsModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="createNewsForm" action="controllers/newsController.php" method="POST" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label for="create_news_title" class="block text-sm text-gray-400 mb-1">News Title *</label>
                        <input type="text" id="create_news_title" name="title" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="Enter news title">
                    </div>
                    
                    <div>
                        <label for="create_news_content" class="block text-sm text-gray-400 mb-1">News Content *</label>
                        <textarea id="create_news_content" name="content" required rows="4"
                                  class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                  placeholder="Write your news article..."></textarea>
                    </div>
                    
                    <div>
                        <label for="create_news_status" class="block text-sm text-gray-400 mb-1">Status *</label>
                        <select id="create_news_status" name="status" required
                                class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                            <option value="">Select Status</option>
                            <option value="Published">Published</option>
                            <option value="Draft">Draft</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="create_news_image" class="block text-sm text-gray-400 mb-1">News Image</label>
                        <input type="file" id="create_news_image" name="image" accept="image/*"
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        <p class="text-xs text-gray-400 mt-1">Max file size: 5MB. Supported formats: JPG, PNG, GIF</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeCreateNewsModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit" name="create_news" 
                            class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                        Create News
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Add search functionality
        document.querySelector('input[type="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const search = this.value.trim();
                const currentFilter = '<?php echo $filter; ?>';
                window.location.href = `?filter=${currentFilter}${search ? '&search=' + encodeURIComponent(search) : ''}`;
            }
        });

        function openNewsModal(newsId) {
            fetch('forms/alumniModal/modalNews.php?id=' + newsId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('newsModalContainer').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading modal:', error);
                    alert('Failed to load news details. Please try again.');
                });
        }
        
        // Make function globally accessible
        window.openNewsModal = openNewsModal;

        function closeNewsModal() {
            document.getElementById('newsModalContainer').innerHTML = '';
        }

        // Create News Modal Functions
        function openCreateNewsModal() {
            document.getElementById('createNewsModal').classList.remove('hidden');
        }

        function closeCreateNewsModal() {
            document.getElementById('createNewsModal').classList.add('hidden');
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