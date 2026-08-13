<?php
session_start();
include_once '../components/connection.php';

// Check if this is view or delete mode
$isViewMode = isset($_GET['view']) && $_GET['view'] == 'true';
$isDeleteMode = isset($_GET['delete']) && $_GET['delete'] == 'true';

// If editing/viewing/deleting, fetch event data
$event_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT e.*, u.firstname, u.lastname, u.role, u.image 
              FROM events e 
              LEFT JOIN users u ON e.user_id = u.user_id 
              WHERE e.event_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $event_data = mysqli_fetch_assoc($result);
    }
}
?>

<?php if(!$isViewMode && !$isDeleteMode): ?>

<!-- Add/Edit Event Modal -->
<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= isset($event_data) ? 'Edit Event' : 'Add New Event' ?>
            </h3>
            <button onclick="closeEventModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="eventForm" action="../controllers/eventController.php" method="POST" enctype="multipart/form-data">
            <?php if(isset($event_data)): ?>
                <input type="hidden" name="event_id" value="<?= $event_data['event_id'] ?>">
            <?php endif; ?>
            
            <div class="space-y-4">
                <!-- Event Information -->
                <div>
                    <label for="title" class="block text-sm text-gray-400 mb-1">Event Title</label>
                    <input type="text" id="title" name="title" required
                           class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                           value="<?= $event_data['title'] ?? '' ?>">
                </div>
                
                <div>
                    <label for="content" class="block text-sm text-gray-400 mb-1">Event Content</label>
                    <textarea id="content" name="content" required rows="4"
                              class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"><?= $event_data['content'] ?? '' ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="schedule" class="block text-sm text-gray-400 mb-1">Schedule</label>
                        <input type="date" id="schedule" name="schedule" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $event_data['schedule'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="location" class="block text-sm text-gray-400 mb-1">Location</label>
                        <input type="text" id="location" name="location" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $event_data['location'] ?? '' ?>">
                    </div>
                </div>
                
                <div>
                    <label for="banner" class="block text-sm text-gray-400 mb-1">Event Banner</label>
                    <input type="file" id="banner" name="banner" accept="image/*"
                           class="w-full bg-dark-800 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <?php if(isset($event_data) && $event_data['banner'] != 'default.jpg'): ?>
                        <p class="text-xs text-gray-400 mt-1">Current: <?= $event_data['banner'] ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if(!isset($event_data)): ?>
                    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                <?php endif; ?>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeEventModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="<?= isset($event_data) ? 'update_event' : 'create_event' ?>" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    <?= isset($event_data) ? 'Update Event' : 'Add Event' ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if($isDeleteMode): ?>
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-red-500">Confirm Deletion</h3>
            <button onclick="closeEventDeleteModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this event? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" action="../controllers/eventController.php">
            <input type="hidden" name="event_id" id="deleteEventId">
            <input type="hidden" name="delete_event" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEventDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Delete Event
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if($isViewMode): ?>
<?php
// Fetch comments for this event
$comments = [];
if(isset($event_data)) {
    $event_id = $event_data['event_id'];
    $comments_query = "SELECT c.*, u.firstname, u.lastname, u.image, u.role 
                       FROM comments c 
                       LEFT JOIN users u ON c.user_id = u.user_id 
                       WHERE c.entity_type = 'event' AND c.entity_id = '$event_id' 
                       ORDER BY c.created_at ASC";
    $comments_result = mysqli_query($conn, $comments_query);
    if($comments_result) {
        while($row = mysqli_fetch_assoc($comments_result)) {
            $comments[] = $row;
        }
    }
}

// Organize comments into parent and replies
$parent_comments = [];
$replies = [];
foreach($comments as $comment) {
    if($comment['parent_id'] == 0 || $comment['parent_id'] == null) {
        $parent_comments[] = $comment;
    } else {
        if(!isset($replies[$comment['parent_id']])) {
            $replies[$comment['parent_id']] = [];
        }
        $replies[$comment['parent_id']][] = $comment;
    }
}
?>
<!-- View Event Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Event Details
            </h3>
            <button onclick="closeEventViewModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <?php if(isset($event_data)): ?>
        <div class="space-y-6">
            <!-- Event Banner -->
            <div class="relative rounded-xl overflow-hidden shadow-lg bg-dark/50">
                <img class="w-full h-auto max-h-96 object-contain" 
                     src="<?= !empty($event_data['banner']) && $event_data['banner'] != 'default.jpg' ? '../assets/uploads/events/' . htmlspecialchars($event_data['banner']) : '../assets/uploads/events/default.jpg' ?>" 
                     alt="Event Banner" onerror="this.src='../assets/uploads/events/default.jpg'">
            </div>
            
            <!-- Event Title -->
            <div class="mt-4">
                <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($event_data['title']) ?></h2>
            </div>

            <!-- Event Information Card -->
            <div class="bg-light/10 rounded-xl p-6 border border-lighter/10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-blue/20 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-neon-blue"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Schedule</p>
                            <p class="text-white font-semibold"><?= date('F d, Y', strtotime($event_data['schedule'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-purple/20 flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-neon-purple"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Location</p>
                            <p class="text-white font-semibold"><?= htmlspecialchars($event_data['location']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-400 mb-2">Event Description</p>
                    <div class="text-white whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($event_data['content']) ?></div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-neon-blue">
                            <img src="<?= !empty($event_data['image']) && $event_data['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($event_data['image']) : '../assets/uploads/alumni/default.png' ?>" 
                                 alt="Author" class="w-full h-full object-cover" onerror="this.src='../assets/uploads/alumni/default.png'">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($event_data['firstname'] . ' ' . $event_data['lastname']) ?></p>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                                <?= htmlspecialchars($event_data['role']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('M d, Y h:i A', strtotime($event_data['date_created'])) ?>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-light/10 rounded-xl p-6 border border-lighter/10">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-white">
                        <i class="fas fa-comments mr-2 text-neon-blue"></i>
                        Comments (<?= count($parent_comments) ?>)
                    </h4>
                </div>

                <!-- Comment Form (for Alumni) -->
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'Alumni'): ?>
                <form id="commentForm" method="POST" action="../controllers/commentController.php" class="mb-6" onsubmit="return submitComment(event, this)">
                    <input type="hidden" name="entity_type" value="event">
                    <input type="hidden" name="entity_id" value="<?= $event_data['event_id'] ?>">
                    <input type="hidden" name="parent_id" value="0">
                    <div class="flex space-x-3">
                        <div class="flex-1">
                            <textarea name="comment_text" id="comment_text" rows="2" required
                                      class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-blue resize-none"
                                      placeholder="Write a comment..."></textarea>
                        </div>
                        <button type="submit" name="add_comment" 
                                class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition h-fit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <?php endif; ?>

                <!-- Comments List -->
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php if(empty($parent_comments)): ?>
                        <p class="text-gray-400 text-center py-8">No comments yet. Be the first to comment!</p>
                    <?php else: ?>
                        <?php foreach($parent_comments as $comment): ?>
                        <div class="bg-dark/50 rounded-lg p-4 border border-lighter/10">
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-neon-blue flex-shrink-0">
                                    <img src="<?= !empty($comment['image']) && $comment['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($comment['image']) : '../assets/uploads/alumni/default.png' ?>" 
                                         alt="User" class="w-full h-full object-cover" onerror="this.src='../assets/uploads/alumni/default.png'">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <p class="text-sm font-semibold text-white"><?= htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']) ?></p>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $comment['role'] == 'Admin' ? 'bg-neon-green/20 text-neon-green' : 'bg-neon-blue/20 text-neon-blue' ?>">
                                            <?= htmlspecialchars($comment['role']) ?>
                                        </span>
                                        <span class="text-xs text-gray-400"><?= date('M d, Y h:i A', strtotime($comment['created_at'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-300 whitespace-pre-wrap"><?= htmlspecialchars($comment['comment_text']) ?></p>
                                    
                                    <!-- Reply Form (for Admin) -->
                                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'Admin'): ?>
                                    <form method="POST" action="../controllers/commentController.php" class="mt-3 flex space-x-2" onsubmit="return submitComment(event, this)">
                                        <input type="hidden" name="entity_type" value="event">
                                        <input type="hidden" name="entity_id" value="<?= $event_data['event_id'] ?>">
                                        <input type="hidden" name="parent_id" value="<?= $comment['comment_id'] ?>">
                                        <input type="text" name="comment_text" required
                                               class="flex-1 bg-light/20 border border-lighter/10 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-green"
                                               placeholder="Reply to this comment...">
                                        <button type="submit" name="add_comment" 
                                                class="px-4 py-2 rounded-lg bg-neon-green/20 text-neon-green hover:bg-neon-green/30 transition text-sm">
                                            <i class="fas fa-reply mr-1"></i> Reply
                                        </button>
                                    </form>
                                    <?php endif; ?>

                                    <!-- Replies -->
                                    <?php if(isset($replies[$comment['comment_id']])): ?>
                                    <div class="mt-3 ml-8 space-y-3 border-l-2 border-neon-green/30 pl-4">
                                        <?php foreach($replies[$comment['comment_id']] as $reply): ?>
                                        <div class="bg-dark/30 rounded-lg p-3">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <p class="text-xs font-semibold text-white"><?= htmlspecialchars($reply['firstname'] . ' ' . $reply['lastname']) ?></p>
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-neon-green/20 text-neon-green">
                                                    <?= htmlspecialchars($reply['role']) ?>
                                                </span>
                                                <span class="text-xs text-gray-400"><?= date('M d, Y h:i A', strtotime($reply['created_at'])) ?></span>
                                            </div>
                                            <p class="text-xs text-gray-300 whitespace-pre-wrap"><?= htmlspecialchars($reply['comment_text']) ?></p>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
            <p class="text-gray-400 text-center py-8">Event details not found.</p>
        <?php endif; ?>
        
        <div class="mt-6 flex justify-end">
            <button onclick="closeEventViewModal()" class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function submitComment(event, form) {
    event.preventDefault();
    
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable button
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('../controllers/commentController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Reload the modal to show new comment
        const eventId = form.querySelector('input[name="entity_id"]').value;
        openEventViewModal(eventId);
        
        // Show success message
        if(typeof showToast !== 'undefined') {
            showToast('Comment posted successfully!', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if(typeof showToast !== 'undefined') {
            showToast('Failed to post comment. Please try again.', 'error');
        }
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
    
    return false;
}
</script>
<?php endif; ?> 