<?php
session_start();
include_once '../components/connection.php';

// Check if this is view or delete mode
$isViewMode = isset($_GET['view']) && $_GET['view'] == 'true';
$isDeleteMode = isset($_GET['delete']) && $_GET['delete'] == 'true';

// If editing/viewing/deleting, fetch announcement data
$announcement_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT a.*, u.firstname, u.lastname, u.role, u.image 
              FROM announcements a 
              LEFT JOIN users u ON a.user_id = u.user_id 
              WHERE a.announce_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $announcement_data = mysqli_fetch_assoc($result);
    }
}
?>

<?php if(!$isViewMode && !$isDeleteMode): ?>
<!-- Add/Edit Announcement Modal -->
<div id="announcementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= isset($announcement_data) ? 'Edit Announcement' : 'Add New Announcement' ?>
            </h3>
            <button onclick="closeAnnouncementModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="announcementForm" action="../controllers/announcementController.php" method="POST" enctype="multipart/form-data">
            <?php if(isset($announcement_data)): ?>
                <input type="hidden" name="announce_id" value="<?= $announcement_data['announce_id'] ?>">
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm text-gray-400 mb-1">Title *</label>
                    <input type="text" id="title" name="title" required
                           class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                           value="<?= $announcement_data['title'] ?? '' ?>"
                           placeholder="Enter announcement title">
                </div>
                
                <div>
                    <label for="content" class="block text-sm text-gray-400 mb-1">Content *</label>
                    <textarea id="content" name="content" required rows="6"
                              class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                              placeholder="Enter announcement content..."><?= $announcement_data['content'] ?? '' ?></textarea>
                </div>
                
                <div>
                    <label for="status" class="block text-sm text-gray-400 mb-1">Status *</label>
                    <select id="status" name="status" required
                            class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        <option value="">Select Status</option>
                        <option value="Active" <?= (isset($announcement_data) && $announcement_data['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= (isset($announcement_data) && $announcement_data['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label for="attachment" class="block text-sm text-gray-400 mb-1">Attachment</label>
                    <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                           class="w-full bg-dark-800 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <?php if(isset($announcement_data) && !empty($announcement_data['attachment_url'])): ?>
                        <p class="text-xs text-gray-400 mt-1">Current: <?= htmlspecialchars($announcement_data['attachment_url']) ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-1">
                        Max file size: 5MB. Supported formats: PDF, DOC, DOCX, TXT, JPG, PNG
                    </p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeAnnouncementModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="<?= isset($announcement_data) ? 'update_announcement' : 'create_announcement' ?>" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    <?= isset($announcement_data) ? 'Update Announcement' : 'Add Announcement' ?>
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
            <button onclick="closeAnnouncementDeleteModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this announcement? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" action="../controllers/announcementController.php">
            <input type="hidden" name="announce_id" id="deleteAnnounceId">
            <input type="hidden" name="delete_announcement" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAnnouncementDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Delete Announcement
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if($isViewMode): ?>
<?php
// Fetch comments for this announcement
$comments = [];
if(isset($announcement_data)) {
    $announce_id = $announcement_data['announce_id'];
    $comments_query = "SELECT c.*, u.firstname, u.lastname, u.image, u.role 
                       FROM comments c 
                       LEFT JOIN users u ON c.user_id = u.user_id 
                       WHERE c.entity_type = 'announcement' AND c.entity_id = '$announce_id' 
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

// Helper function to display comments recursively
function displayComment($comment, $replies, $announce_id, $user_role) {
    $html = '<div class="bg-dark/50 rounded-lg p-4 border border-lighter/10">';
    $html .= '<div class="flex items-start space-x-3">';
    $html .= '<div class="w-10 h-10 rounded-full overflow-hidden border-2 border-neon-blue flex-shrink-0">';
    $html .= '<img src="' . (!empty($comment['image']) && $comment['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($comment['image']) : '../assets/uploads/alumni/default.png') . '" ';
    $html .= 'alt="User" class="w-full h-full object-cover" onerror="this.src=\'../assets/uploads/alumni/default.png\'">';
    $html .= '</div>';
    $html .= '<div class="flex-1 min-w-0">';
    $html .= '<div class="flex items-center space-x-2 mb-1">';
    $html .= '<p class="text-sm font-semibold text-white">' . htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']) . '</p>';
    $html .= '<span class="px-2 py-0.5 rounded-full text-xs font-semibold ' . ($comment['role'] == 'Admin' ? 'bg-neon-green/20 text-neon-green' : 'bg-neon-blue/20 text-neon-blue') . '">';
    $html .= htmlspecialchars($comment['role']) . '</span>';
    $html .= '<span class="text-xs text-gray-400">' . date('M d, Y h:i A', strtotime($comment['created_at'])) . '</span>';
    $html .= '</div>';
    $html .= '<p class="text-sm text-gray-300 whitespace-pre-wrap">' . htmlspecialchars($comment['comment_text']) . '</p>';
    
    // Reply Form (for Admin)
    if($user_role == 'Admin') {
        $html .= '<form method="POST" action="../controllers/commentController.php" class="mt-3 flex space-x-2" onsubmit="return submitComment(event, this)">';
        $html .= '<input type="hidden" name="entity_type" value="announcement">';
        $html .= '<input type="hidden" name="entity_id" value="' . $announce_id . '">';
        $html .= '<input type="hidden" name="parent_id" value="' . $comment['comment_id'] . '">';
        $html .= '<input type="text" name="comment_text" required class="flex-1 bg-light/20 border border-lighter/10 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-green" placeholder="Reply to this comment...">';
        $html .= '<button type="submit" name="add_comment" class="px-4 py-2 rounded-lg bg-neon-green/20 text-neon-green hover:bg-neon-green/30 transition text-sm">';
        $html .= '<i class="fas fa-reply mr-1"></i> Reply</button>';
        $html .= '</form>';
    }
    
    // Display replies
    if(isset($replies[$comment['comment_id']])) {
        $html .= '<div class="mt-3 ml-8 space-y-3 border-l-2 border-neon-green/30 pl-4">';
        foreach($replies[$comment['comment_id']] as $reply) {
            $html .= '<div class="bg-dark/30 rounded-lg p-3">';
            $html .= '<div class="flex items-center space-x-2 mb-1">';
            $html .= '<p class="text-xs font-semibold text-white">' . htmlspecialchars($reply['firstname'] . ' ' . $reply['lastname']) . '</p>';
            $html .= '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-neon-green/20 text-neon-green">' . htmlspecialchars($reply['role']) . '</span>';
            $html .= '<span class="text-xs text-gray-400">' . date('M d, Y h:i A', strtotime($reply['created_at'])) . '</span>';
            $html .= '</div>';
            $html .= '<p class="text-xs text-gray-300 whitespace-pre-wrap">' . htmlspecialchars($reply['comment_text']) . '</p>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    
    $html .= '</div></div></div>';
    return $html;
}
?>
<!-- View Announcement Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Announcement Details
            </h3>
            <button onclick="closeAnnouncementViewModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <?php if(isset($announcement_data)): ?>
        <div class="space-y-6">
            <!-- Announcement Title -->
            <div>
                <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($announcement_data['title']) ?></h2>
            </div>

            <!-- Announcement Information Card -->
            <div class="bg-light/10 rounded-xl p-6 border border-lighter/10">
                <div class="mb-4">
                    <p class="text-xs text-gray-400 mb-2">Announcement Content</p>
                    <div class="text-white whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($announcement_data['content']) ?></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-green/20 flex items-center justify-center">
                            <i class="fas fa-info-circle text-neon-green"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Status</p>
                            <p class="text-white font-semibold">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $announcement_data['status'] == 'Active' ? 'bg-neon-green/20 text-neon-green' : 'bg-red-500/20 text-red-500' ?>">
                                    <?= htmlspecialchars($announcement_data['status']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-blue/20 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-neon-blue"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Date Posted</p>
                            <p class="text-white font-semibold"><?= date('F d, Y', strtotime($announcement_data['date_posted'])) ?></p>
                        </div>
                    </div>
                </div>

                <?php if(!empty($announcement_data['attachment_url'])): ?>
                <div class="mb-4 p-4 bg-dark/30 rounded-lg border border-lighter/10">
                    <p class="text-xs text-gray-400 mb-2">Attachment</p>
                    <a href="../assets/uploads/announcements/<?= htmlspecialchars($announcement_data['attachment_url']) ?>" 
                       target="_blank" 
                       class="text-neon-blue hover:text-neon-purple transition flex items-center space-x-2">
                        <i class="fas fa-file-download"></i>
                        <span><?= htmlspecialchars($announcement_data['attachment_url']) ?></span>
                    </a>
                </div>
                <?php endif; ?>

                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-neon-blue">
                            <img src="<?= !empty($announcement_data['image']) && $announcement_data['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($announcement_data['image']) : '../assets/uploads/alumni/default.png' ?>" 
                                 alt="Author" class="w-full h-full object-cover" onerror="this.src='../assets/uploads/alumni/default.png'">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($announcement_data['firstname'] . ' ' . $announcement_data['lastname']) ?></p>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                                <?= htmlspecialchars($announcement_data['role']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('M d, Y h:i A', strtotime($announcement_data['date_posted'])) ?>
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
                    <input type="hidden" name="entity_type" value="announcement">
                    <input type="hidden" name="entity_id" value="<?= $announcement_data['announce_id'] ?>">
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
                            <?= displayComment($comment, $replies, $announcement_data['announce_id'], $_SESSION['role'] ?? null) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
            <p class="text-gray-400 text-center py-8">Announcement details not found.</p>
        <?php endif; ?>
        
        <div class="mt-6 flex justify-end">
            <button onclick="closeAnnouncementViewModal()" class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
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
        const announceId = form.querySelector('input[name="entity_id"]').value;
        openAnnouncementViewModal(announceId);
        
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
