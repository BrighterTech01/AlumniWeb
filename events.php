<?php
session_start();
require_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Alumni') {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build the events query based on filters
$events_query = "SELECT e.*, u.firstname, u.lastname, u.middlename, u.image, u.role 
                 FROM events e 
                 LEFT JOIN users u ON e.user_id = u.user_id 
                 WHERE 1=1";

if ($filter === 'upcoming') {
    $events_query .= " AND e.schedule >= CURDATE()";
} elseif ($filter === 'past') {
    $events_query .= " AND e.schedule < CURDATE()";
}

if (!empty($search_query)) {
    $events_query .= " AND (e.title LIKE '%$search_query%' OR e.content LIKE '%$search_query%' OR e.location LIKE '%$search_query%')";
}

$events_query .= " ORDER BY e.schedule ASC";
$events_result = mysqli_query($conn, $events_query);

include 'components/userHeader.php';

// Handle success/error messages
$success_message = '';
$error_message = '';
if(isset($_GET['success'])) {
    $success_message = 'Event created successfully!';
}
if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'missing_fields':
            $error_message = 'Please fill in all required fields.';
            break;
        case 'event_creation_failed':
            $error_message = 'Failed to create event. Please try again.';
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
                <h1 class="text-2xl font-bold text-white mb-2">Alumni Events</h1>
                <p class="text-gray-400 text-sm">Stay connected with your alma mater through these events.</p>
            </div>
            <button onclick="openCreateEventModal()" class="px-4 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Create Event
            </button>
        </div>

        <!-- Filter and Search Section -->
        <div class="mb-6 space-y-4">
            <!-- Search -->
            <form action="" method="GET" class="relative">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                <input type="text" name="search" 
                       class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                       placeholder="Search events..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </form>
            
            <!-- Filter Buttons -->
            <div class="flex gap-2">
                <a href="?filter=upcoming<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="flex-1 px-4 py-2 rounded-lg text-center text-sm transition-colors <?php echo $filter === 'upcoming' ? 'bg-primary/20 text-primary border border-primary/30' : 'bg-light/20 text-gray-400 hover:text-white'; ?>">
                    Upcoming
                </a>
                <a href="?filter=past<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="flex-1 px-4 py-2 rounded-lg text-center text-sm transition-colors <?php echo $filter === 'past' ? 'bg-primary/20 text-primary border border-primary/30' : 'bg-light/20 text-gray-400 hover:text-white'; ?>">
                    Past Events
                </a>
            </div>
        </div>

        <!-- Events Feed -->
        <div class="space-y-4">
            <?php if ($events_result && mysqli_num_rows($events_result) > 0): ?>
                <?php while ($event = mysqli_fetch_assoc($events_result)): 
                    // Get like count and check if current user liked
                    $event_id = (int)$event['event_id'];
                    $like_count = 0;
                    $is_liked = false;
                    
                    // Check if likes table exists
                    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'likes'");
                    if(mysqli_num_rows($table_check) > 0) {
                        $like_count_query = "SELECT COUNT(*) as count FROM likes WHERE entity_type = 'event' AND entity_id = $event_id";
                        $like_result = mysqli_query($conn, $like_count_query);
                        if($like_result && mysqli_num_rows($like_result) > 0) {
                            $like_row = mysqli_fetch_assoc($like_result);
                            $like_count = (int)$like_row['count'];
                        }
                        if(isset($_SESSION['user_id'])) {
                            $user_id = (int)$_SESSION['user_id'];
                            $check_like_query = "SELECT like_id FROM likes WHERE user_id = $user_id AND entity_type = 'event' AND entity_id = $event_id";
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
                            $check_share_query = "SELECT share_id FROM shares WHERE user_id = $user_id AND entity_type = 'event' AND entity_id = $event_id";
                            $check_share_result = mysqli_query($conn, $check_share_query);
                            $is_shared = mysqli_num_rows($check_share_result) > 0;
                        }
                    }
                ?>
                    <div class="bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 overflow-hidden shadow-lg" data-event-id="<?php echo $event_id; ?>">
                        <!-- Event Header - Facebook Style -->
                        <div class="p-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <?php 
                                $author_fullname = trim($event['firstname'] . ' ' . ($event['middlename'] ? $event['middlename'] . ' ' : '') . $event['lastname']);
                                $avatarName = htmlspecialchars($author_fullname);
                                $imagePath = !empty($event['image']) ? "assets/uploads/alumni/" . $event['image'] : null;
                                $avatarUrl = $imagePath && file_exists($imagePath) ? $imagePath : "https://ui-avatars.com/api/?name=" . urlencode($avatarName) . "&background=random&color=fff";
                                ?>
                                <img src="<?php echo $avatarUrl; ?>" 
                                     class="w-10 h-10 rounded-full object-cover border-2 border-primary/30" 
                                     alt="<?php echo $avatarName; ?>'s profile"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($avatarName); ?>&background=random&color=fff'">
                                <div>
                                    <p class="text-white font-semibold hover:underline cursor-pointer"><?php echo htmlspecialchars($author_fullname); ?></p>
                                    <div class="flex items-center space-x-2 text-xs text-gray-400">
                                        <span><?php echo date('M d', strtotime($event['schedule'])); ?></span>
                                        <span>•</span>
                                        <span><i class="fas fa-globe-americas"></i></span>
                                    </div>
                                </div>
                            </div>
                            <button class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-light/20 transition">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>

                        <!-- Event Content -->
                        <div class="px-3 pb-2">
                            <h3 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="text-gray-300 text-sm leading-relaxed mb-3">
                                <?php 
                                $content = strip_tags($event['content']);
                                echo htmlspecialchars($content);
                                ?>
                            </p>
                            
                            <!-- Event Details -->
                            <div class="bg-light/10 rounded-lg p-3 space-y-2">
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-calendar-alt w-4 mr-2 text-primary"></i>
                                    <span><?php echo date('F d, Y', strtotime($event['schedule'])); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-clock w-4 mr-2 text-primary"></i>
                                    <span><?php echo date('h:i A', strtotime($event['schedule'])); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-map-marker-alt w-4 mr-2 text-primary"></i>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Event Banner -->
                        <?php if (!empty($event['banner'])): ?>
                            <div class="relative bg-black w-full">
                                <img src="assets/uploads/events/<?php echo htmlspecialchars($event['banner']); ?>" 
                                     class="w-full max-h-[500px] object-cover cursor-pointer" 
                                     alt="Event Banner"
                                     onclick="openEventModal(<?php echo $event['event_id']; ?>)">
                                <span class="absolute top-4 right-4 px-3 py-1 bg-darker/90 backdrop-blur-sm rounded-full text-xs font-medium text-white <?php echo strtotime($event['schedule']) >= strtotime('today') ? 'border border-neon-green/50' : 'border border-gray-500/50'; ?>">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    <?php echo strtotime($event['schedule']) >= strtotime('today') ? 'Upcoming' : 'Past Event'; ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Engagement Bar - Facebook Style -->
                        <div class="px-3 py-2 border-t border-b border-lighter/10 flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-4">
                                <button onclick="toggleLike('event', <?php echo $event_id; ?>, this)" 
                                        class="like-btn flex items-center space-x-1 transition <?php echo $is_liked ? 'text-blue-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                        data-liked="<?php echo $is_liked ? 'true' : 'false'; ?>"
                                        data-entity-type="event"
                                        data-entity-id="<?php echo $event_id; ?>">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>Like</span>
                                    <?php if($like_count > 0): ?>
                                        <span class="like-count ml-1"><?php echo $like_count; ?></span>
                                    <?php endif; ?>
                                </button>
                                <button onclick="openEventModal(<?php echo $event['event_id']; ?>)" class="flex items-center space-x-1 text-gray-400 hover:text-blue-400 transition">
                                    <i class="fas fa-comment"></i>
                                    <span>Comment</span>
                                </button>
                                <button onclick="shareToAccount('event', <?php echo $event_id; ?>, this)" 
                                        class="share-btn flex items-center space-x-1 transition <?php echo $is_shared ? 'text-green-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                        data-shared="<?php echo $is_shared ? 'true' : 'false'; ?>"
                                        data-entity-type="event"
                                        data-entity-id="<?php echo $event_id; ?>">
                                    <i class="fas fa-share"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                        </div>

                        <!-- Event Footer - Comments Preview -->
                        <div class="px-3 py-2">
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                                <span>
                                    <?php
                                    // Get comment count
                                    $event_id = (int)$event['event_id'];
                                    $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'event' AND entity_id = $event_id";
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
                                <span class="hover:underline cursor-pointer" onclick="openEventModal(<?php echo $event['event_id']; ?>)">View all</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 shadow-lg">
                    <i class="fas fa-calendar-times text-6xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">No Events Found</h3>
                    <p class="text-gray-400 mb-4">There are no events matching your criteria at the moment.</p>
                    <button onclick="openCreateEventModal()" class="px-6 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-plus mr-2"></i> Create Event
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Event Modal Container -->
<div id="eventModalContainer"></div>

<!-- Create Event Modal -->
<div id="createEventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Create Event</h3>
            <button onclick="closeCreateEventModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="createEventForm" action="controllers/eventController.php" method="POST" enctype="multipart/form-data">
            <div class="space-y-4">
                <div>
                    <label for="create_event_title" class="block text-sm text-gray-400 mb-1">Event Title *</label>
                    <input type="text" id="create_event_title" name="title" required
                           class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                           placeholder="Enter event title">
                </div>
                
                <div>
                    <label for="create_event_content" class="block text-sm text-gray-400 mb-1">Event Description *</label>
                    <textarea id="create_event_content" name="content" required rows="4"
                              class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                              placeholder="Describe your event..."></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="create_event_schedule" class="block text-sm text-gray-400 mb-1">Schedule *</label>
                        <input type="date" id="create_event_schedule" name="schedule" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    
                    <div>
                        <label for="create_event_location" class="block text-sm text-gray-400 mb-1">Location *</label>
                        <input type="text" id="create_event_location" name="location" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="Event location">
                    </div>
                </div>
                
                <div>
                    <label for="create_event_banner" class="block text-sm text-gray-400 mb-1">Event Banner</label>
                    <input type="file" id="create_event_banner" name="banner" accept="image/*"
                           class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <p class="text-xs text-gray-400 mt-1">Max file size: 2MB. Supported formats: JPG, PNG, GIF</p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeCreateEventModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="create_event" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-submit form when search input changes
    document.querySelector('input[name="search"]')?.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });

    function openEventModal(eventId) {
        fetch('forms/alumniModal/modalEvent.php?id=' + eventId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('eventModalContainer').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                if(typeof showToast !== 'undefined') {
                    showToast('Error loading event details', 'error');
                } else {
                    alert('Failed to load event details. Please try again.');
                }
            });
    }
    
    // Make function globally accessible
    window.openEventModal = openEventModal;

    function closeEventModal() {
        document.getElementById('eventModalContainer').innerHTML = '';
    }

    // Create Event Modal Functions
    function openCreateEventModal() {
        document.getElementById('createEventModal').classList.remove('hidden');
    }

    function closeCreateEventModal() {
        document.getElementById('createEventModal').classList.add('hidden');
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
