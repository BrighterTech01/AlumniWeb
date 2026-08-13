<?php
include_once '../components/connection.php';

// Pagination settings
$limit = 10; // Number of events per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $limit;

// Get total count of events
$count_query = "SELECT COUNT(*) as total FROM events";
$count_result = mysqli_query($conn, $count_query);
$total_events = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_events / $limit);

// Query to fetch events with user details (with pagination)
$query = "SELECT e.*, u.firstname, u.lastname, u.role 
          FROM events e 
          LEFT JOIN users u ON e.user_id = u.user_id 
          ORDER BY e.date_created DESC 
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$events = [];

// Check if query was successful and has results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get comment and reply counts for this event
        $event_id = $row['event_id'];
        $comment_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'event' AND entity_id = $event_id AND (parent_id = 0 OR parent_id IS NULL)";
        $comment_result = mysqli_query($conn, $comment_count_query);
        $comment_count = mysqli_fetch_assoc($comment_result)['count'];
        
        $reply_count_query = "SELECT COUNT(*) as count FROM comments WHERE entity_type = 'event' AND entity_id = $event_id AND parent_id > 0";
        $reply_result = mysqli_query($conn, $reply_count_query);
        $reply_count = mysqli_fetch_assoc($reply_result)['count'];
        
        $row['comment_count'] = $comment_count;
        $row['reply_count'] = $reply_count;
        array_push($events, $row);
    }
}
?>

<?php include_once '../components/adminHeader.php'?>
<div class="pt-16 sm:pt-20 px-2 sm:px-4 lg:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6">
        <div>
            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Manage Events</h1>
            <p class="text-gray-400 text-xs sm:text-sm">View and manage events</p>
        </div>
        <button onclick="openEventModal()" class="mt-4 sm:mt-0 w-full sm:w-auto px-3 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition flex items-center justify-center text-xs sm:text-sm">
            <i class="fas fa-plus mr-2 text-xs"></i> Add New Event
        </button>
    </div>

    <!-- Search and Filter Section -->
    <div class="glass rounded-xl p-3 sm:p-4 border border-lighter/10 mb-4 sm:mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
            <div class="relative">
                <input type="search" 
                       placeholder="Search events..." 
                       class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
            <input type="date" 
                   class="bg-light/20 text-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary/50 text-xs sm:text-sm">
            <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                <option value="">All Locations</option>
                <option value="Online">Online</option>
                <option value="On-site">On-site</option>
            </select>
        </div>
    </div>

    <!-- Events Cards -->
    <?php if (empty($events)): ?>
    <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-500 mb-4"></i>
        <p class="text-gray-400 text-sm">No events found</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        <?php foreach ($events as $event): ?>
        <div class="glass rounded-lg border border-lighter/10 overflow-hidden hover:border-neon-blue/50 transition-all duration-300 hover:shadow-lg hover:shadow-neon-blue/20">
            <!-- Event Banner Image -->
            <div class="relative w-full h-28 overflow-hidden bg-darker flex items-center justify-center">
                <img class="w-full h-full object-contain" 
                     src="<?= !empty($event['banner']) && $event['banner'] != 'default.jpg' ? '../assets/uploads/events/' . htmlspecialchars($event['banner']) : '../assets/uploads/events/default.jpg' ?>" 
                     alt="Event Banner"
                     onerror="this.src='../assets/uploads/events/default.jpg'">
                <div class="absolute top-1 left-1 right-1 flex items-center justify-between">
                    <div class="flex items-center space-x-1 flex-wrap">
                        <?php if($event['comment_count'] > 0 || $event['reply_count'] > 0): ?>
                        <?php if($event['comment_count'] > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-neon-blue/90 text-white backdrop-blur-sm shadow-lg" title="<?= $event['comment_count'] ?> comment(s)">
                            <i class="fas fa-comment text-xxs"></i> <?= $event['comment_count'] ?>
                        </span>
                        <?php endif; ?>
                        <?php if($event['reply_count'] > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-neon-purple/90 text-white backdrop-blur-sm shadow-lg" title="<?= $event['reply_count'] ?> reply/replies">
                            <i class="fas fa-reply text-xxs"></i> <?= $event['reply_count'] ?>
                        </span>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full text-xxs font-semibold bg-darker/80 text-neon-blue backdrop-blur-sm">
                        #<?= $event['event_id'] ?>
                    </span>
                </div>
            </div>
            
            <!-- Event Content -->
            <div class="p-3">
                <!-- Event Title -->
                <h3 class="text-xs font-bold text-white mb-1.5 line-clamp-2">
                    <?= htmlspecialchars($event['title']) ?>
                </h3>
                
                <!-- Event Description -->
                <p class="text-xxs text-gray-400 mb-3 line-clamp-2">
                    <?php 
                    $content = htmlspecialchars($event['content']);
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
                        <button onclick="openEventViewModal(<?= $event['event_id'] ?>)" 
                                class="px-2 py-1 rounded-lg bg-neon-blue/20 text-neon-blue hover:bg-neon-blue/30 transition-colors text-xxs"
                                title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="openEventModal(<?= $event['event_id'] ?>)" 
                                class="px-2 py-1 rounded-lg bg-neon-green/20 text-neon-green hover:bg-neon-green/30 transition-colors text-xxs"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <button onclick="openEventDeleteModal(<?= $event['event_id'] ?>)" 
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
            $end = min($offset + $limit, $total_events);
            ?>
            Showing <span class="font-medium"><?= $start ?></span> to <span class="font-medium"><?= $end ?></span> of <span class="font-medium"><?= $total_events ?></span> results
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
<!-- modalFunction.js is now loaded in adminHeader.php -->

</body>
</html>