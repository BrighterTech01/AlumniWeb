<?php
include_once '../components/connection.php';

// Pagination settings
$limit = 10; // Number of jobs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $limit;

// Get total count of jobs
$count_query = "SELECT COUNT(*) as total FROM jobs";
$count_result = mysqli_query($conn, $count_query);
$total_jobs = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_jobs / $limit);

// Query to fetch jobs with user details (with pagination)
$query = "SELECT j.*, u.firstname, u.lastname, u.role 
          FROM jobs j 
          LEFT JOIN users u ON j.user_id = u.user_id 
          ORDER BY j.created_at DESC 
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$jobs = [];

// Check if query was successful and has results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get comment and reply counts for this job
        $job_id = $row['job_id'];
        $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'job' AND entity_id = $job_id AND (parent_id = 0 OR parent_id IS NULL)";
        $comment_result = mysqli_query($conn, $comment_count_query);
        $comment_count = mysqli_fetch_assoc($comment_result)['count'];
        
        $reply_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'job' AND entity_id = $job_id AND parent_id > 0";
        $reply_result = mysqli_query($conn, $reply_count_query);
        $reply_count = mysqli_fetch_assoc($reply_result)['count'];
        
        $row['comment_count'] = $comment_count;
        $row['reply_count'] = $reply_count;
        array_push($jobs, $row);
    }
}
?>

<?php include_once '../components/adminHeader.php'?>
<div class="pt-16 sm:pt-20 px-2 sm:px-4 lg:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6">
        <div>
            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Manage Jobs</h1>
            <p class="text-gray-400 text-xs sm:text-sm">View and manage job postings</p>
        </div>
        <button onclick="openJobModal()" class="mt-4 sm:mt-0 w-full sm:w-auto px-3 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition flex items-center justify-center text-xs sm:text-sm">
            <i class="fas fa-plus mr-2 text-xs"></i> Add New Job
        </button>
    </div>

    <!-- Search and Filter Section -->
    <div class="glass rounded-xl p-3 sm:p-4 border border-lighter/10 mb-4 sm:mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
            <div class="relative">
                <input type="search" 
                       placeholder="Search jobs..." 
                       class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
            <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                <option value="">All Job Types</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Internship">Internship</option>
            </select>
            <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                <option value="">All Locations</option>
                <option value="Remote">Remote</option>
                <option value="On-site">On-site</option>
                <option value="Hybrid">Hybrid</option>
            </select>
        </div>
    </div>

    <!-- Jobs Cards -->
    <?php if (empty($jobs)): ?>
    <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
        <i class="fas fa-briefcase text-4xl text-gray-500 mb-4"></i>
        <p class="text-gray-400 text-sm">No jobs found</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        <?php foreach ($jobs as $job): ?>
        <div class="glass rounded-lg border border-lighter/10 overflow-hidden hover:border-neon-blue/50 transition-all duration-300 hover:shadow-lg hover:shadow-neon-blue/20">
            <!-- Job Image -->
            <div class="relative w-full h-28 overflow-hidden bg-darker flex items-center justify-center">
                <img class="w-full h-full object-contain" 
                     src="<?= !empty($job['job_image']) && $job['job_image'] != 'default.jpg' ? '../assets/uploads/jobs/' . htmlspecialchars($job['job_image']) : '../assets/uploads/jobs/default.jpg' ?>" 
                     alt="Job Image"
                     onerror="this.src='../assets/uploads/jobs/default.jpg'">
                <div class="absolute top-1 left-1 right-1 flex items-center justify-between">
                    <div class="flex items-center space-x-1 flex-wrap">
                        <?php if($job['comment_count'] > 0 || $job['reply_count'] > 0): ?>
                        <?php if($job['comment_count'] > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-neon-blue/90 text-white backdrop-blur-sm shadow-lg" title="<?= $job['comment_count'] ?> comment(s)">
                            <i class="fas fa-comment text-xxs"></i> <?= $job['comment_count'] ?>
                        </span>
                        <?php endif; ?>
                        <?php if($job['reply_count'] > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-neon-purple/90 text-white backdrop-blur-sm shadow-lg" title="<?= $job['reply_count'] ?> reply/replies">
                            <i class="fas fa-reply text-xxs"></i> <?= $job['reply_count'] ?>
                        </span>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-darker/80 text-neon-blue backdrop-blur-sm">
                        #<?= $job['job_id'] ?>
                    </span>
                </div>
            </div>
            
            <!-- Job Content -->
            <div class="p-3">
                <!-- Job Title -->
                <h3 class="text-xs font-bold text-white mb-1.5 line-clamp-2">
                    <?= htmlspecialchars($job['job_title']) ?>
                </h3>
                
                <!-- Job Description -->
                <p class="text-xxs text-gray-400 mb-3 line-clamp-2">
                    <?php 
                    $content = htmlspecialchars($job['job_description'] ?? '');
                    $maxLength = 80; // Character limit
                    if (strlen($content) > $maxLength) {
                        echo substr($content, 0, $maxLength) . '...';
                    } else {
                        echo $content;
                    }
                    ?>
                </p>
                
                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-3 border-t border-lighter/10">
                    <div class="flex space-x-1">
                        <button onclick="openJobViewModal(<?= $job['job_id'] ?>)" 
                                class="px-2 py-1 rounded-lg bg-neon-blue/20 text-neon-blue hover:bg-neon-blue/30 transition-colors text-xxs"
                                title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="openJobModal(<?= $job['job_id'] ?>)" 
                                class="px-2 py-1 rounded-lg bg-neon-green/20 text-neon-green hover:bg-neon-green/30 transition-colors text-xxs"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <button onclick="openJobDeleteModal(<?= $job['job_id'] ?>)" 
                            class="px-2 py-1 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-xxs"
                            title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-2 sm:gap-0">
        <div class="text-xs sm:text-sm text-gray-400 text-center sm:text-left">
            <?php 
            $start = $offset + 1;
            $end = min($offset + $limit, $total_jobs);
            ?>
            Showing <span class="font-medium"><?= $start ?></span> to <span class="font-medium"><?= $end ?></span> of <span class="font-medium"><?= $total_jobs ?></span> results
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors text-xs sm:text-sm">
                <i class="fas fa-chevron-left mr-1"></i>Previous
            </a>
            <?php else: ?>
            <button disabled class="px-4 py-2 rounded-lg bg-light/10 text-gray-600 cursor-not-allowed text-xs sm:text-sm">
                <i class="fas fa-chevron-left mr-1"></i>Previous
            </button>
            <?php endif; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors text-xs sm:text-sm">
                Next<i class="fas fa-chevron-right ml-1"></i>
            </a>
            <?php else: ?>
            <button disabled class="px-4 py-2 rounded-lg bg-light/10 text-gray-600 cursor-not-allowed text-xs sm:text-sm">
                Next<i class="fas fa-chevron-right ml-1"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="../assets/js/toggleHandle.js"></script>
<script src="../assets/js/modalFunction.js"></script>



</body>
</html>