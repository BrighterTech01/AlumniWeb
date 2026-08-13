<?php
session_start();
include_once '../components/connection.php';

// Check if this is view or delete mode
$isViewMode = isset($_GET['view']) && $_GET['view'] == 'true';
$isDeleteMode = isset($_GET['delete']) && $_GET['delete'] == 'true';

// If editing/viewing/deleting, fetch job data
$job_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT j.*, u.firstname, u.lastname, u.role, u.image 
              FROM jobs j 
              LEFT JOIN users u ON j.user_id = u.user_id 
              WHERE j.job_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $job_data = mysqli_fetch_assoc($result);
    }
}
?>

<?php if(!$isViewMode && !$isDeleteMode): ?>
<!-- Add/Edit Job Modal -->
<div id="jobModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= isset($job_data) ? 'Edit Job' : 'Add New Job' ?>
            </h3>
            <button onclick="closeJobModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="jobForm" action="../controllers/jobController.php" method="POST" enctype="multipart/form-data">
            <?php if(isset($job_data)): ?>
                <input type="hidden" name="job_id" value="<?= $job_data['job_id'] ?>">
            <?php endif; ?>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="job_title" class="block text-sm text-gray-400 mb-1">Job Title *</label>
                        <input type="text" id="job_title" name="job_title" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $job_data['job_title'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="company_name" class="block text-sm text-gray-400 mb-1">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $job_data['company_name'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="salary" class="block text-sm text-gray-400 mb-1">Salary *</label>
                        <input type="number" step="0.01" id="salary" name="salary" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $job_data['salary'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="job_type" class="block text-sm text-gray-400 mb-1">Job Type *</label>
                        <select id="job_type" name="job_type" required
                                class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                            <option value="">Select Job Type</option>
                            <option value="Full-time" <?= ($job_data['job_type'] ?? '') == 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                            <option value="Part-time" <?= ($job_data['job_type'] ?? '') == 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                            <option value="Contract" <?= ($job_data['job_type'] ?? '') == 'Contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="Internship" <?= ($job_data['job_type'] ?? '') == 'Internship' ? 'selected' : '' ?>>Internship</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="employee_status" class="block text-sm text-gray-400 mb-1">Employee Status *</label>
                        <input type="text" id="employee_status" name="employee_status" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $job_data['employee_status'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="location" class="block text-sm text-gray-400 mb-1">Location *</label>
                        <input type="text" id="location" name="location" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $job_data['location'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="application_deadline" class="block text-sm text-gray-400 mb-1">Application Deadline *</label>
                        <input type="date" id="application_deadline" name="application_deadline" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $job_data['application_deadline'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="url" class="block text-sm text-gray-400 mb-1">Application URL</label>
                        <input type="url" id="url" name="url"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $job_data['url'] ?? '' ?>">
                    </div>
                </div>
                
                <div>
                    <label for="description" class="block text-sm text-gray-400 mb-1">Description *</label>
                    <textarea id="description" name="description" required rows="4"
                              class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"><?= $job_data['description'] ?? '' ?></textarea>
                </div>
                
                <div>
                    <label for="job_image" class="block text-sm text-gray-400 mb-1">Job Image</label>
                    <input type="file" id="job_image" name="job_image" accept="image/*"
                           class="w-full bg-dark-800 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <?php if(isset($job_data) && $job_data['job_image'] != 'default.jpg'): ?>
                        <p class="text-xs text-gray-400 mt-1">Current: <?= $job_data['job_image'] ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if(!isset($job_data)): ?>
                    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                <?php endif; ?>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeJobModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="<?= isset($job_data) ? 'update_job' : 'create_job' ?>" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    <?= isset($job_data) ? 'Update Job' : 'Add Job' ?>
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
            <button onclick="closeJobDeleteModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this job? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" action="../controllers/jobController.php">
            <input type="hidden" name="job_id" id="deleteJobId">
            <input type="hidden" name="delete_job" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeJobDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Delete Job
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if($isViewMode): ?>
<?php
// Fetch comments for this job
$comments = [];
if(isset($job_data)) {
    $job_id = $job_data['job_id'];
    $comments_query = "SELECT c.*, u.firstname, u.lastname, u.image, u.role 
                       FROM comments c 
                       LEFT JOIN users u ON c.user_id = u.user_id 
                       WHERE c.entity_type = 'job' AND c.entity_id = '$job_id' 
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
<!-- View Job Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Job Details
            </h3>
            <button onclick="closeJobViewModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <?php if(isset($job_data)): ?>
        <div class="space-y-6">
            <!-- Job Image -->
            <div class="relative rounded-xl overflow-hidden shadow-lg bg-dark/50">
                <img class="w-full h-auto max-h-96 object-contain" 
                     src="<?= !empty($job_data['job_image']) && $job_data['job_image'] != 'default.jpg' ? '../assets/uploads/jobs/' . htmlspecialchars($job_data['job_image']) : '../assets/uploads/jobs/default.jpg' ?>" 
                     alt="Job Image" onerror="this.src='../assets/uploads/jobs/default.jpg'">
            </div>
            
            <!-- Job Title -->
            <div class="mt-4">
                <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($job_data['job_title']) ?></h2>
                <p class="text-gray-400 mt-1"><?= htmlspecialchars($job_data['company_name']) ?></p>
            </div>

            <!-- Job Information Card -->
            <div class="bg-light/10 rounded-xl p-6 border border-lighter/10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-blue/20 flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-neon-blue"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Salary</p>
                            <p class="text-white font-semibold">$<?= number_format($job_data['salary'], 2) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-purple/20 flex items-center justify-center">
                            <i class="fas fa-briefcase text-neon-purple"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Job Type</p>
                            <p class="text-white font-semibold"><?= htmlspecialchars($job_data['job_type']) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-green/20 flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-neon-green"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Location</p>
                            <p class="text-white font-semibold"><?= htmlspecialchars($job_data['location']) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-neon-pink/20 flex items-center justify-center">
                            <i class="fas fa-calendar-times text-neon-pink"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Deadline</p>
                            <p class="text-white font-semibold"><?= date('F d, Y', strtotime($job_data['application_deadline'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-400 mb-2">Job Description</p>
                    <div class="text-white whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($job_data['description']) ?></div>
                </div>

                <?php if(!empty($job_data['url'])): ?>
                <div class="mb-4">
                    <p class="text-xs text-gray-400 mb-2">Application URL</p>
                    <a href="<?= htmlspecialchars($job_data['url']) ?>" target="_blank" 
                       class="text-neon-blue hover:text-neon-purple transition underline">
                        <?= htmlspecialchars($job_data['url']) ?>
                    </a>
                </div>
                <?php endif; ?>

                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-neon-blue">
                            <img src="<?= !empty($job_data['image']) && $job_data['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($job_data['image']) : '../assets/uploads/alumni/default.png' ?>" 
                                 alt="Author" class="w-full h-full object-cover" onerror="this.src='../assets/uploads/alumni/default.png'">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($job_data['firstname'] . ' ' . $job_data['lastname']) ?></p>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                                <?= htmlspecialchars($job_data['role']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('M d, Y h:i A', strtotime($job_data['created_at'])) ?>
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
                    <input type="hidden" name="entity_type" value="job">
                    <input type="hidden" name="entity_id" value="<?= $job_data['job_id'] ?>">
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
                                        <input type="hidden" name="entity_type" value="job">
                                        <input type="hidden" name="entity_id" value="<?= $job_data['job_id'] ?>">
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
            <p class="text-gray-400 text-center py-8">Job details not found.</p>
        <?php endif; ?>
        
        <div class="mt-6 flex justify-end">
            <button onclick="closeJobViewModal()" class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
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
        const jobId = form.querySelector('input[name="entity_id"]').value;
        openJobViewModal(jobId);
        
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
