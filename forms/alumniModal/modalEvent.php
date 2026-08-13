<?php
if (!isset($_GET['id'])) {
    echo '<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"><div class="glass rounded-xl p-8"><p class="text-red-500">No event selected.</p><button onclick="closeEventModal()" class="mt-4 px-4 py-2 bg-gray-700 rounded text-white">Close</button></div></div>';
    exit;
}
include_once '../../components/connection.php';
session_start();

$event_id = (int)$_GET['id'];
$query = "SELECT e.*, u.firstname, u.lastname, u.middlename, u.image, u.role 
          FROM events e 
          LEFT JOIN users u ON e.user_id = u.user_id 
          WHERE e.event_id = $event_id LIMIT 1";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"><div class="glass rounded-xl p-8"><p class="text-red-500">Event not found.</p><button onclick="closeEventModal()" class="mt-4 px-4 py-2 bg-gray-700 rounded text-white">Close</button></div></div>';
    exit;
}
$event = mysqli_fetch_assoc($result);

// Fetch comments for this event
$comments = [];
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

// Construct full name
$author_fullname = trim($event['firstname'] . ' ' . ($event['middlename'] ? $event['middlename'] . ' ' : '') . $event['lastname']);
?>
<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="glass rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto mx-4 relative shadow-2xl">
        <button onclick="closeEventModal()" aria-label="Close" class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl z-10">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="space-y-6">
            <!-- Event Banner -->
        <?php if (!empty($event['banner'])): ?>
            <div class="relative rounded-xl overflow-hidden shadow-lg bg-dark/50">
                <img src="assets/uploads/events/<?php echo htmlspecialchars($event['banner']); ?>" 
                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                     class="w-full h-auto max-h-96 object-contain">
            </div>
            <?php endif; ?>
            
            <!-- Event Title -->
            <div>
                <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($event['title']); ?></h2>
            </div>

            <!-- Event Information -->
            <div class="bg-light/10 rounded-xl p-6 border border-lighter/10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-blue/20 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-neon-blue"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Schedule</p>
                            <p class="text-white font-semibold"><?php echo date('F d, Y', strtotime($event['schedule'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-purple/20 flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-neon-purple"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Location</p>
                            <p class="text-white font-semibold"><?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-400 mb-2">Event Description</p>
                    <div class="text-white whitespace-pre-wrap leading-relaxed"><?php echo htmlspecialchars($event['content']); ?></div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-neon-blue">
                            <img src="<?php echo !empty($event['image']) && $event['image'] != 'default.png' ? 'assets/uploads/alumni/' . htmlspecialchars($event['image']) : 'assets/uploads/alumni/default.png'; ?>" 
                                 alt="Author" class="w-full h-full object-cover" onerror="this.src='assets/uploads/alumni/default.png'">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($author_fullname); ?></p>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                                <?php echo htmlspecialchars($event['role']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-light/10 rounded-xl p-6 border border-lighter/10">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-white">
                        <i class="fas fa-comments mr-2 text-neon-blue"></i>
                        Comments (<?php echo count($parent_comments); ?>)
                    </h4>
                </div>

                <!-- Comment Form (for Alumni, Admin, and Staff) -->
                <?php if(isset($_SESSION['role']) && in_array($_SESSION['role'], ['Alumni', 'Admin', 'Staff'])): ?>
                <form id="commentForm" method="POST" action="../../controllers/commentController.php" class="mb-6" onsubmit="return submitComment(event, this, 'event')">
                    <input type="hidden" name="entity_type" value="event">
                    <input type="hidden" name="entity_id" value="<?php echo $event_id; ?>">
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
                                    <img src="<?php echo !empty($comment['image']) && $comment['image'] != 'default.png' ? 'assets/uploads/alumni/' . htmlspecialchars($comment['image']) : 'assets/uploads/alumni/default.png'; ?>" 
                                         alt="User" class="w-full h-full object-cover" onerror="this.src='assets/uploads/alumni/default.png'">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']); ?></p>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?php echo $comment['role'] == 'Admin' ? 'bg-neon-green/20 text-neon-green' : 'bg-neon-blue/20 text-neon-blue'; ?>">
                                            <?php echo htmlspecialchars($comment['role']); ?>
                                        </span>
                                        <span class="text-xs text-gray-400"><?php echo date('M d, Y h:i A', strtotime($comment['created_at'])); ?></span>
                                    </div>
                                    <p class="text-sm text-gray-300 whitespace-pre-wrap"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                    
                                    <!-- Reply Form (for Alumni, Admin, and Staff) -->
                                    <?php if(isset($_SESSION['role']) && in_array($_SESSION['role'], ['Alumni', 'Admin', 'Staff'])): ?>
                                    <form method="POST" action="../../controllers/commentController.php" class="mt-3 flex space-x-2" onsubmit="return submitComment(event, this, 'event')">
                                        <input type="hidden" name="entity_type" value="event">
                                        <input type="hidden" name="entity_id" value="<?php echo $event_id; ?>">
                                        <input type="hidden" name="parent_id" value="<?php echo $comment['comment_id']; ?>">
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
                                                <p class="text-xs font-semibold text-white"><?php echo htmlspecialchars($reply['firstname'] . ' ' . $reply['lastname']); ?></p>
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-neon-green/20 text-neon-green">
                                                    <?php echo htmlspecialchars($reply['role']); ?>
                                                </span>
                                                <span class="text-xs text-gray-400"><?php echo date('M d, Y h:i A', strtotime($reply['created_at'])); ?></span>
                                            </div>
                                            <p class="text-xs text-gray-300 whitespace-pre-wrap"><?php echo htmlspecialchars($reply['comment_text']); ?></p>
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
        
        <div class="mt-6 flex justify-end">
            <button onclick="closeEventModal()" class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function submitComment(event, form, entityType) {
    event.preventDefault();
    
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable button
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Calculate base path - get the directory of the current page
    let basePath = window.location.pathname;
    // Remove filename (everything after last slash)
    const lastSlash = basePath.lastIndexOf('/');
    if (lastSlash > 0) {
        basePath = basePath.substring(0, lastSlash);
    } else {
        basePath = '';
    }
    // Ensure it starts with / and doesn't end with /
    if (!basePath) {
        basePath = '/';
    } else if (!basePath.startsWith('/')) {
        basePath = '/' + basePath;
    }
    
    const controllerUrl = basePath + (basePath === '/' ? '' : '/') + 'controllers/commentController.php';
    
    fetch(controllerUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if(data.success) {
            // Reload the modal to show new comment
            const entityId = form.querySelector('input[name="entity_id"]').value;
            if(entityType === 'event') {
                // Use window to access parent page function
                if(typeof window.openEventModal === 'function') {
                    window.openEventModal(entityId);
                } else if(typeof openEventModal === 'function') {
                    openEventModal(entityId);
                } else {
                    // Fallback: reload the modal container
                    location.reload();
                }
            }
            
            // Show success message
            if(typeof showToast !== 'undefined') {
                showToast(data.message || 'Comment posted successfully!', 'success');
            } else if(typeof window.showToast !== 'undefined') {
                window.showToast(data.message || 'Comment posted successfully!', 'success');
            } else {
                alert(data.message || 'Comment posted successfully!');
            }
        } else {
            // Show error message
            if(typeof showToast !== 'undefined') {
                showToast(data.message || 'Failed to post comment. Please try again.', 'error');
            } else if(typeof window.showToast !== 'undefined') {
                window.showToast(data.message || 'Failed to post comment. Please try again.', 'error');
            } else {
                alert(data.message || 'Failed to post comment. Please try again.');
            }
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if(typeof showToast !== 'undefined') {
            showToast('Failed to post comment. Please try again.', 'error');
        } else if(typeof window.showToast !== 'undefined') {
            window.showToast('Failed to post comment. Please try again.', 'error');
        } else {
            alert('Failed to post comment. Please try again.');
        }
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
    
    return false;
}
</script>
