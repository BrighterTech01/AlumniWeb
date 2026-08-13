<?php
session_start();
require_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Alumni') {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build the jobs query based on filters
$jobs_query = "SELECT j.*, u.firstname, u.lastname, u.middlename, u.image, u.role 
               FROM jobs j 
               LEFT JOIN users u ON j.user_id = u.user_id 
               WHERE 1=1";

if ($filter === 'active') {
    $jobs_query .= " AND j.application_deadline >= CURDATE()";
} elseif ($filter === 'expired') {
    $jobs_query .= " AND j.application_deadline < CURDATE()";
}

if (!empty($search_query)) {
    $jobs_query .= " AND (j.job_title LIKE '%$search_query%' OR j.company_name LIKE '%$search_query%' OR j.location LIKE '%$search_query%' OR j.description LIKE '%$search_query%')";
}

$jobs_query .= " ORDER BY j.created_at DESC";
$jobs_result = mysqli_query($conn, $jobs_query);

include 'components/userHeader.php';

// Handle success/error messages
$success_message = '';
$error_message = '';
if(isset($_GET['success'])) {
    $success_message = 'Job posted successfully!';
}
if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'missing_fields':
            $error_message = 'Please fill in all required fields.';
            break;
        case 'job_creation_failed':
            $error_message = 'Failed to post job. Please try again.';
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
                <h1 class="text-2xl font-bold text-white mb-2">Job Board</h1>
                <p class="text-gray-400 text-sm">Find your next career opportunity from our curated job listings.</p>
            </div>
            <button onclick="openCreateJobModal()" class="px-4 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Post a Job
            </button>
        </div>

        <!-- Filter and Search Section -->
        <div class="mb-6 space-y-4">
            <!-- Search -->
            <form action="" method="GET" class="relative">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                <input type="text" name="search" 
                       class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                       placeholder="Search jobs..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </form>
            
            <!-- Filter Buttons -->
            <div class="flex gap-2">
                <a href="?filter=active<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="flex-1 px-4 py-2 rounded-lg text-center text-sm transition-colors <?php echo $filter === 'active' ? 'bg-primary/20 text-primary border border-primary/30' : 'bg-light/20 text-gray-400 hover:text-white'; ?>">
                    Active Jobs
                </a>
                <a href="?filter=expired<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="flex-1 px-4 py-2 rounded-lg text-center text-sm transition-colors <?php echo $filter === 'expired' ? 'bg-primary/20 text-primary border border-primary/30' : 'bg-light/20 text-gray-400 hover:text-white'; ?>">
                    Expired Jobs
                </a>
            </div>
        </div>

        <!-- Jobs Feed -->
        <div class="space-y-4">
            <?php if ($jobs_result && mysqli_num_rows($jobs_result) > 0): ?>
                <?php while ($job = mysqli_fetch_assoc($jobs_result)): 
                    // Get like count and check if current user liked
                    $job_id = (int)$job['job_id'];
                    $like_count = 0;
                    $is_liked = false;
                    
                    // Check if likes table exists
                    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'likes'");
                    if(mysqli_num_rows($table_check) > 0) {
                        $like_count_query = "SELECT COUNT(*) as count FROM likes WHERE entity_type = 'job' AND entity_id = $job_id";
                        $like_result = mysqli_query($conn, $like_count_query);
                        if($like_result && mysqli_num_rows($like_result) > 0) {
                            $like_row = mysqli_fetch_assoc($like_result);
                            $like_count = (int)$like_row['count'];
                        }
                        if(isset($_SESSION['user_id'])) {
                            $user_id = (int)$_SESSION['user_id'];
                            $check_like_query = "SELECT like_id FROM likes WHERE user_id = $user_id AND entity_type = 'job' AND entity_id = $job_id";
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
                            $check_share_query = "SELECT share_id FROM shares WHERE user_id = $user_id AND entity_type = 'job' AND entity_id = $job_id";
                            $check_share_result = mysqli_query($conn, $check_share_query);
                            $is_shared = mysqli_num_rows($check_share_result) > 0;
                        }
                    }
                ?>
                    <div class="bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 overflow-hidden shadow-lg" data-job-id="<?php echo $job_id; ?>">
                        <!-- Job Header - Facebook Style -->
                        <div class="p-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <?php 
                                $author_fullname = trim($job['firstname'] . ' ' . ($job['middlename'] ? $job['middlename'] . ' ' : '') . $job['lastname']);
                                $avatarName = htmlspecialchars($author_fullname ?: 'Admin');
                                $imagePath = !empty($job['image']) ? "assets/uploads/alumni/" . $job['image'] : null;
                                $avatarUrl = $imagePath && file_exists($imagePath) ? $imagePath : "https://ui-avatars.com/api/?name=" . urlencode($avatarName) . "&background=random&color=fff";
                                ?>
                                <img src="<?php echo $avatarUrl; ?>" 
                                     class="w-10 h-10 rounded-full object-cover border-2 border-primary/30" 
                                     alt="<?php echo $avatarName; ?>'s profile"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($avatarName); ?>&background=random&color=fff'">
                                <div>
                                    <p class="text-white font-semibold hover:underline cursor-pointer"><?php echo htmlspecialchars($author_fullname ?: 'Admin'); ?></p>
                                    <div class="flex items-center space-x-2 text-xs text-gray-400">
                                        <span><?php echo date('M d', strtotime($job['created_at'])); ?></span>
                                        <span>•</span>
                                        <span><i class="fas fa-globe-americas"></i></span>
                                    </div>
                                </div>
                            </div>
                            <button class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-light/20 transition">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>

                        <!-- Job Content -->
                        <div class="px-3 pb-2">
                            <h3 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                            <p class="text-gray-300 text-sm leading-relaxed mb-3">
                                <?php 
                                $description = strip_tags($job['description']);
                                echo htmlspecialchars($description);
                                ?>
                            </p>
                            
                            <!-- Job Details -->
                            <div class="bg-light/10 rounded-lg p-3 space-y-2">
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-building w-4 mr-2 text-primary"></i>
                                    <span><?php echo htmlspecialchars($job['company_name']); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-map-marker-alt w-4 mr-2 text-primary"></i>
                                    <span><?php echo htmlspecialchars($job['location']); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-briefcase w-4 mr-2 text-primary"></i>
                                    <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                                </div>
                                <?php if (!empty($job['salary'])): ?>
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-money-bill-wave w-4 mr-2 text-primary"></i>
                                    <span>₱<?php echo number_format($job['salary'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="flex items-center text-sm text-gray-300">
                                    <i class="fas fa-calendar-alt w-4 mr-2 text-primary"></i>
                                    <span>Apply before <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Job Image -->
                        <?php if (!empty($job['job_image'])): ?>
                            <div class="relative bg-black w-full">
                                <img src="assets/uploads/jobs/<?php echo htmlspecialchars($job['job_image']); ?>" 
                                     class="w-full max-h-[500px] object-cover cursor-pointer" 
                                     alt="Job Image"
                                     onclick="openJobModal(<?php echo $job['job_id']; ?>)">
                                <span class="absolute top-4 right-4 px-3 py-1 bg-darker/90 backdrop-blur-sm rounded-full text-xs font-medium text-white <?php echo strtotime($job['application_deadline']) >= strtotime('today') ? 'border border-neon-green/50' : 'border border-gray-500/50'; ?>">
                                    <i class="fas fa-briefcase mr-1"></i>
                                    <?php echo strtotime($job['application_deadline']) >= strtotime('today') ? 'Active' : 'Expired'; ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Engagement Bar - Facebook Style -->
                        <div class="px-3 py-2 border-t border-b border-lighter/10 flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-4">
                                <button onclick="toggleLike('job', <?php echo $job_id; ?>, this)" 
                                        class="like-btn flex items-center space-x-1 transition <?php echo $is_liked ? 'text-blue-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                        data-liked="<?php echo $is_liked ? 'true' : 'false'; ?>"
                                        data-entity-type="job"
                                        data-entity-id="<?php echo $job_id; ?>">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>Like</span>
                                    <?php if($like_count > 0): ?>
                                        <span class="like-count ml-1"><?php echo $like_count; ?></span>
                                    <?php endif; ?>
                                </button>
                                <button onclick="openJobModal(<?php echo $job['job_id']; ?>)" class="flex items-center space-x-1 text-gray-400 hover:text-blue-400 transition">
                                    <i class="fas fa-comment"></i>
                                    <span>Comment</span>
                                </button>
                                <button onclick="shareToAccount('job', <?php echo $job_id; ?>, this)" 
                                        class="share-btn flex items-center space-x-1 transition <?php echo $is_shared ? 'text-green-400' : 'text-gray-400 hover:text-blue-400'; ?>"
                                        data-shared="<?php echo $is_shared ? 'true' : 'false'; ?>"
                                        data-entity-type="job"
                                        data-entity-id="<?php echo $job_id; ?>">
                                    <i class="fas fa-share"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                        </div>

                        <!-- Job Footer - Comments Preview -->
                        <div class="px-3 py-2">
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                                <span>
                                    <?php
                                    // Get comment count
                                    $job_id = (int)$job['job_id'];
                                    $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'job' AND entity_id = $job_id";
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
                                <span class="hover:underline cursor-pointer" onclick="openJobModal(<?php echo $job['job_id']; ?>)">View all</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-darker/80 backdrop-blur-md rounded-lg border border-lighter/20 shadow-lg">
                    <i class="fas fa-briefcase text-6xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">No Jobs Found</h3>
                    <p class="text-gray-400 mb-4">There are no job listings matching your criteria at the moment.</p>
                    <button onclick="openCreateJobModal()" class="px-6 py-2 bg-gradient-to-r from-neon-blue to-neon-purple text-white rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-plus mr-2"></i> Post a Job
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Job Modal Container -->
<div id="jobModalContainer"></div>

<!-- Create Job Modal -->
<div id="createJobModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Post a Job</h3>
            <button onclick="closeCreateJobModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="createJobForm" action="controllers/jobController.php" method="POST" enctype="multipart/form-data">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="create_job_title" class="block text-sm text-gray-400 mb-1">Job Title *</label>
                        <input type="text" id="create_job_title" name="job_title" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="e.g., Software Engineer">
                    </div>
                    
                    <div>
                        <label for="create_company_name" class="block text-sm text-gray-400 mb-1">Company Name *</label>
                        <input type="text" id="create_company_name" name="company_name" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="Company name">
                    </div>
                    
                    <div>
                        <label for="create_salary" class="block text-sm text-gray-400 mb-1">Salary *</label>
                        <input type="number" step="0.01" id="create_salary" name="salary" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="0.00">
                    </div>
                    
                    <div>
                        <label for="create_job_type" class="block text-sm text-gray-400 mb-1">Job Type *</label>
                        <select id="create_job_type" name="job_type" required
                                class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                            <option value="">Select Job Type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="create_employee_status" class="block text-sm text-gray-400 mb-1">Employee Status *</label>
                        <input type="text" id="create_employee_status" name="employee_status" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="e.g., Regular, Probationary">
                    </div>
                    
                    <div>
                        <label for="create_location" class="block text-sm text-gray-400 mb-1">Location *</label>
                        <input type="text" id="create_location" name="location" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="Job location">
                    </div>
                    
                    <div>
                        <label for="create_application_deadline" class="block text-sm text-gray-400 mb-1">Application Deadline *</label>
                        <input type="date" id="create_application_deadline" name="application_deadline" required
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    
                    <div>
                        <label for="create_url" class="block text-sm text-gray-400 mb-1">Application URL</label>
                        <input type="url" id="create_url" name="url"
                               class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               placeholder="https://...">
                    </div>
                </div>
                
                <div>
                    <label for="create_description" class="block text-sm text-gray-400 mb-1">Job Description *</label>
                    <textarea id="create_description" name="description" required rows="4"
                              class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"
                              placeholder="Describe the job requirements, responsibilities..."></textarea>
                </div>
                
                <div>
                    <label for="create_job_image" class="block text-sm text-gray-400 mb-1">Job Image</label>
                    <input type="file" id="create_job_image" name="job_image" accept="image/*"
                           class="w-full bg-light/20 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <p class="text-xs text-gray-400 mt-1">Max file size: 2MB. Supported formats: JPG, PNG, GIF</p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeCreateJobModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="create_job" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    Post Job
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

    function openJobModal(jobId) {
        fetch('forms/alumniModal/modalJob.php?id=' + jobId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('jobModalContainer').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                if(typeof showToast !== 'undefined') {
                    showToast('Error loading job details', 'error');
                } else {
                    alert('Failed to load job details. Please try again.');
                }
            });
    }
    
    // Make function globally accessible
    window.openJobModal = openJobModal;

    function closeJobModal() {
        document.getElementById('jobModalContainer').innerHTML = '';
    }

    // Create Job Modal Functions
    function openCreateJobModal() {
        document.getElementById('createJobModal').classList.remove('hidden');
    }

    function closeCreateJobModal() {
        document.getElementById('createJobModal').classList.add('hidden');
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
