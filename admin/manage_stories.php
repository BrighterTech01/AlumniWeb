<?php
session_start();
include_once '../components/connection.php';

// Pagination settings
$limit = 10; // Number of stories per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $limit;

// Get total count of stories
$count_query = "SELECT COUNT(*) as total FROM stories";
$count_result = mysqli_query($conn, $count_query);
$total_stories = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_stories / $limit);

// Fetch stories with user details using the view (with pagination)
$query = "SELECT * FROM view_stories_with_users ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$stories = [];

// Check if query was successful and has results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get comment and reply counts for this story
        $story_id = $row['story_id'];
        $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'story' AND entity_id = $story_id AND (parent_id = 0 OR parent_id IS NULL)";
        $comment_result = mysqli_query($conn, $comment_count_query);
        $comment_count = mysqli_fetch_assoc($comment_result)['count'];
        
        $reply_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'story' AND entity_id = $story_id AND parent_id > 0";
        $reply_result = mysqli_query($conn, $reply_count_query);
        $reply_count = mysqli_fetch_assoc($reply_result)['count'];
        
        $row['comment_count'] = $comment_count;
        $row['reply_count'] = $reply_count;
        array_push($stories, $row);
    }
}
?>

<body class="bg-gray-900 text-white">
    <?php include_once '../components/adminHeader.php'; ?>

    <!-- Success/Error Messages -->
    <?php if(isset($_GET['success'])): ?>
        <div class="fixed top-20 right-4 z-50">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <?php
                switch($_GET['success']) {
                    case '1':
                        echo 'Story created successfully!';
                        break;
                    case '2':
                        echo 'Story updated successfully!';
                        break;
                    case '3':
                        echo 'Story deleted successfully!';
                        break;
                    default:
                        echo 'Operation completed successfully!';
                }
                ?>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const message = document.querySelector('.fixed.top-20.right-4');
                if(message) message.remove();
            }, 3000);
        </script>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="fixed top-20 right-4 z-50">
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <?php
                switch($_GET['error']) {
                    case 'story_creation_failed':
                        echo 'Failed to create story. Please try again.';
                        break;
                    case 'story_update_failed':
                        echo 'Failed to update story. Please try again.';
                        break;
                    case 'story_delete_failed':
                        echo 'Failed to delete story. Please try again.';
                        break;
                    case 'unauthorized':
                        echo 'You are not authorized to perform this action.';
                        break;
                    case 'story_not_found':
                        echo 'Story not found.';
                        break;
                    default:
                        echo 'An error occurred. Please try again.';
                }
                ?>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const message = document.querySelector('.fixed.top-20.right-4');
                if(message) message.remove();
            }, 3000);
        </script>
    <?php endif; ?>

    <div class="pt-20 px-4 sm:px-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8">
                <div>
                <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Manage Stories</h1>
                <p class="text-gray-400 text-sm sm:text-base">View and manage all stories posted by alumni</p>
            </div>
            <button onclick="openStoryModal()" class="mt-4 sm:mt-0 px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                Add New Story
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="search" 
                           placeholder="Search stories..." 
                           class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all min-w-[150px]">
                    <option value="">All Media Types</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                </select>
                <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all min-w-[150px]">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <!-- Stories Cards -->
        <?php if (empty($stories)): ?>
        <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
            <i class="fas fa-book-open text-4xl text-gray-500 mb-4"></i>
            <p class="text-gray-400 text-sm">No stories found</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
            <?php foreach ($stories as $story): ?>
            <div class="glass rounded-lg border border-lighter/10 overflow-hidden hover:border-neon-blue/50 transition-all duration-300 hover:shadow-lg hover:shadow-neon-blue/20">
                <!-- Story Media -->
                <div class="relative w-full h-28 overflow-hidden bg-darker flex items-center justify-center">
                    <?php if($story['media_type'] == 'image' && $story['vimage']): ?>
                    <img class="w-full h-full object-contain" 
                         src="../assets/uploads/stories/<?= htmlspecialchars($story['vimage']) ?>"
                         alt="Story Image"
                         onerror="this.src='../assets/uploads/stories/default.jpg'">
                    <?php elseif($story['media_type'] == 'video' && $story['vimage']): ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-video text-4xl text-neon-purple"></i>
                    </div>
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-image text-4xl text-gray-500"></i>
                    </div>
                    <?php endif; ?>
                    <div class="absolute top-1 left-1 right-1 flex items-center justify-between">
                        <div class="flex items-center space-x-1 flex-wrap">
                            <?php if($story['comment_count'] > 0 || $story['reply_count'] > 0): ?>
                            <?php if($story['comment_count'] > 0): ?>
                            <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-neon-blue/90 text-white backdrop-blur-sm shadow-lg" title="<?= $story['comment_count'] ?> comment(s)">
                                <i class="fas fa-comment text-xxs"></i> <?= $story['comment_count'] ?>
                            </span>
                            <?php endif; ?>
                            <?php if($story['reply_count'] > 0): ?>
                            <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-neon-purple/90 text-white backdrop-blur-sm shadow-lg" title="<?= $story['reply_count'] ?> reply/replies">
                                <i class="fas fa-reply text-xxs"></i> <?= $story['reply_count'] ?>
                            </span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-darker/80 text-neon-blue backdrop-blur-sm">
                            #<?= $story['story_id'] ?>
                        </span>
                    </div>
                </div>
                
                <!-- Story Content -->
                <div class="p-3">
                    <!-- Story Title -->
                    <h3 class="text-xs font-bold text-white mb-1.5 line-clamp-2">
                        <?= htmlspecialchars($story['title']) ?>
                    </h3>
                    
                    <!-- Story Description -->
                    <p class="text-xxs text-gray-400 mb-3 line-clamp-2">
                        <?php 
                        $content = htmlspecialchars($story['description'] ?? '');
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
                            <button onclick="openStoryViewModal(<?= $story['story_id'] ?>)" 
                                    class="px-2 py-1 rounded-lg bg-neon-blue/20 text-neon-blue hover:bg-neon-blue/30 transition-colors text-xxs"
                                    title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openStoryModal(<?= $story['story_id'] ?>)" 
                                    class="px-2 py-1 rounded-lg bg-neon-green/20 text-neon-green hover:bg-neon-green/30 transition-colors text-xxs"
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                        <button onclick="openStoryDeleteModal(<?= $story['story_id'] ?>)" 
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
                $end = min($offset + $limit, $total_stories);
                ?>
                Showing <span class="font-medium"><?= $start ?></span> to <span class="font-medium"><?= $end ?></span> of <span class="font-medium"><?= $total_stories ?></span> results
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