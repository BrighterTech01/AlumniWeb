<?php
session_start();
include_once '../components/connection.php';

// Check if this is view or delete mode
$isViewMode = isset($_GET['view']) && $_GET['view'] == 'true';
$isDeleteMode = isset($_GET['delete']) && $_GET['delete'] == 'true';

// If editing/viewing/deleting, fetch story data
$story_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT s.*, u.firstname, u.lastname, u.role, u.image 
              FROM stories s 
              LEFT JOIN users u ON s.user_id = u.user_id 
              WHERE s.story_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $story_data = mysqli_fetch_assoc($result);
    }
}
?>

<?php if(!$isViewMode && !$isDeleteMode): ?>
<!-- Add/Edit Story Modal -->
<div id="storyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= isset($story_data) ? 'Edit Story' : 'Share Your Story' ?>
            </h3>
            <button onclick="closeStoryModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="storyForm" action="../controllers/storiesController.php" method="POST" enctype="multipart/form-data">
            <?php if(isset($story_data)): ?>
                <input type="hidden" name="story_id" value="<?= $story_data['story_id'] ?>">
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm text-gray-400 mb-1">Title *</label>
                    <input type="text" id="title" name="title" required
                           class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                           value="<?= $story_data['title'] ?? '' ?>"
                           placeholder="Enter a compelling title for your story">
                </div>
                
                <div>
                    <label for="description" class="block text-sm text-gray-400 mb-1">Story Content *</label>
                    <textarea id="description" name="description" required rows="6"
                              class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                              placeholder="Share your experience, achievements, or journey..."><?= $story_data['description'] ?? '' ?></textarea>
                </div>
                
                <div>
                    <label for="media_type" class="block text-sm text-gray-400 mb-1">Media Type *</label>
                    <select id="media_type" name="media_type" required
                            class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        <option value="image" <?= (isset($story_data) && $story_data['media_type'] == 'image') ? 'selected' : '' ?>>Image</option>
                        <option value="video" <?= (isset($story_data) && $story_data['media_type'] == 'video') ? 'selected' : '' ?>>Video</option>
                    </select>
                </div>
                
                <div>
                    <label for="media" class="block text-sm text-gray-400 mb-1">Media Upload</label>
                    <input type="file" id="media" name="media" accept="image/*,video/*"
                           class="w-full bg-dark-800 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <?php if(isset($story_data) && !empty($story_data['vimage'])): ?>
                        <p class="text-xs text-gray-400 mt-1">Current: <?= $story_data['vimage'] ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-1">
                        Max file size: 5MB for images, 50MB for videos. Supported formats: JPG, PNG, GIF, MP4, MOV
                    </p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeStoryModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="<?= isset($story_data) ? 'update_story' : 'create_story' ?>" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    <?= isset($story_data) ? 'Update Story' : 'Share Story' ?>
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
            <button onclick="closeStoryDeleteModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this story? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" action="../controllers/storiesController.php">
            <input type="hidden" name="story_id" id="deleteStoryId">
            <input type="hidden" name="delete_story" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeStoryDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Delete Story
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if($isViewMode): ?>
<?php
// Fetch comments for this story
$comments = [];
if(isset($story_data)) {
    $story_id = $story_data['story_id'];
    $comments_query = "SELECT c.*, u.firstname, u.lastname, u.image, u.role 
                       FROM comments c 
                       LEFT JOIN users u ON c.user_id = u.user_id 
                       WHERE c.entity_type = 'story' AND c.entity_id = '$story_id' 
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
<!-- View Story Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Story Details
            </h3>
            <button onclick="closeStoryViewModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <?php if(isset($story_data)): ?>
        <div class="space-y-6">
            <!-- Story Media -->
            <?php if(!empty($story_data['vimage'])): ?>
            <div class="relative rounded-xl overflow-hidden shadow-lg bg-dark/50">
                <?php if($story_data['media_type'] == 'image'): ?>
                    <img class="w-full h-auto max-h-96 object-contain" 
                         src="../assets/uploads/stories/<?= htmlspecialchars($story_data['vimage']) ?>" 
                         alt="Story Image" onerror="this.src='../assets/uploads/stories/default.jpg'">
                <?php else: ?>
                    <video controls class="w-full max-h-96" preload="metadata">
                        <source src="../assets/uploads/stories/<?= htmlspecialchars($story_data['vimage']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Story Title -->
            <div class="mt-4">
                <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($story_data['title']) ?></h2>
            </div>

            <!-- Story Information Card -->
            <div class="bg-light/10 rounded-xl p-6 border border-lighter/10">
                <div class="mb-4">
                    <p class="text-xs text-gray-400 mb-2">Story Content</p>
                    <div class="text-white whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($story_data['description']) ?></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-purple/20 flex items-center justify-center">
                            <i class="fas <?= $story_data['media_type'] == 'image' ? 'fa-image' : 'fa-video' ?> text-neon-purple"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Media Type</p>
                            <p class="text-white font-semibold"><?= ucfirst($story_data['media_type']) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-blue/20 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-neon-blue"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Date Created</p>
                            <p class="text-white font-semibold"><?= date('F d, Y', strtotime($story_data['created_at'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-neon-blue">
                            <img src="<?= !empty($story_data['image']) && $story_data['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($story_data['image']) : '../assets/uploads/alumni/default.png' ?>" 
                                 alt="Author" class="w-full h-full object-cover" onerror="this.src='../assets/uploads/alumni/default.png'">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($story_data['firstname'] . ' ' . $story_data['lastname']) ?></p>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                                <?= htmlspecialchars($story_data['role']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('M d, Y h:i A', strtotime($story_data['created_at'])) ?>
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
                    <input type="hidden" name="entity_type" value="story">
                    <input type="hidden" name="entity_id" value="<?= $story_data['story_id'] ?>">
                    <input type="hidden" name="parent_id" value="0">
                    <div class="flex space-x-3">
                        <div class="flex-1">
                            <textarea name="comment_text" rows="2" required
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
                                        <input type="hidden" name="entity_type" value="story">
                                        <input type="hidden" name="entity_id" value="<?= $story_data['story_id'] ?>">
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
            <p class="text-gray-400 text-center py-8">Story details not found.</p>
        <?php endif; ?>
        
        <div class="mt-6 flex justify-end">
            <button onclick="closeStoryViewModal()" class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
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
        const storyId = form.querySelector('input[name="entity_id"]').value;
        openStoryViewModal(storyId);
        
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
