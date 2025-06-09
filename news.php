<?php
// Only start session if it hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Alumni') {
    header("Location: login.php");
    exit();
}

// Fetch user details from view_user_details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM view_user_details WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Construct full name
$fullname = trim($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']);

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the events query based on filters
$events_query = "SELECT * FROM view_event_alumni_details WHERE 1=1";

if ($filter === 'upcoming') {
    $events_query .= " AND schedule >= CURDATE()";
} elseif ($filter === 'past') {
    $events_query .= " AND schedule < CURDATE()";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $events_query .= " AND (title LIKE '%$search%' OR content LIKE '%$search%' OR location LIKE '%$search%')";
}

$events_query .= " ORDER BY schedule ASC";
$events_result = mysqli_query($conn, $events_query);

// Fetch recent news from view_news_details
$news_query = "SELECT * FROM view_news_details WHERE status = 'Published' ORDER BY date_posted DESC LIMIT 3";
$news_result = mysqli_query($conn, $news_query);

// Fetch recent job postings
$jobs_query = "SELECT * FROM view_job_postings WHERE application_deadline >= CURDATE() ORDER BY created_at DESC LIMIT 3";
$jobs_result = mysqli_query($conn, $jobs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMSCST Alumni Portal - Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .neon-glow {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }
        .event-card {
            transition: transform 0.2s ease-in-out;
        }
        .event-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-white">
    <?php include 'components/userHeader.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="glass rounded-xl p-6 border border-lighter/10 mb-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Alumni Events</h1>
                        <p class="text-gray-400">Stay connected with your alma mater through these events.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <input type="search" 
                                   placeholder="Search events..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="?filter=upcoming<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 rounded-lg <?php echo $filter === 'upcoming' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'bg-light/20 text-gray-400 hover:text-white transition-colors'; ?>">
                                Upcoming
                            </a>
                            <a href="?filter=past<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 rounded-lg <?php echo $filter === 'past' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'bg-light/20 text-gray-400 hover:text-white transition-colors'; ?>">
                                Past Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (mysqli_num_rows($events_result) > 0): ?>
                    <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                        <div class="event-card glass rounded-xl overflow-hidden border border-lighter/10">
                            <?php if (!empty($event['banner'])): ?>
                                <img src="./assets/uploads/events/<?php echo htmlspecialchars($event['banner']); ?>" 
                                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-neon-blue/10 flex flex-col items-center justify-center">
                                    <i class="fas fa-calendar-alt text-4xl text-neon-blue mb-2"></i>
                                    <span class="text-neon-blue font-bold text-xl"><?php echo date('d', strtotime($event['schedule'])); ?></span>
                                    <span class="text-gray-400"><?php echo date('M Y', strtotime($event['schedule'])); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <span class="px-3 py-1 rounded-full text-xs <?php echo strtotime($event['schedule']) >= strtotime('today') ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-500/20 text-gray-400'; ?>">
                                        <?php echo strtotime($event['schedule']) >= strtotime('today') ? 'Upcoming' : 'Past Event'; ?>
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-400 mb-4"><?php echo substr(htmlspecialchars($event['content']), 0, 150) . '...'; ?></p>
                                
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-calendar w-5"></i>
                                        <span><?php echo date('F d, Y', strtotime($event['schedule'])); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-clock w-5"></i>
                                        <span><?php echo date('h:i A', strtotime($event['schedule'])); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-map-marker-alt w-5"></i>
                                        <span><?php echo htmlspecialchars($event['location']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <img src="<?php echo !empty($event['image']) ? './assets/uploads/alumni/' . htmlspecialchars($event['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($event['firstname'] . ' ' . $event['lastname']); ?>" 
                                             alt="<?php echo htmlspecialchars($event['firstname'] . ' ' . $event['lastname']); ?>"
                                             class="w-6 h-6 rounded-full mr-2">
                                        <span>Posted by <?php echo htmlspecialchars($event['firstname'] . ' ' . $event['lastname']); ?></span>
                                    </div>
                                    <?php if (!empty($event['registration_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($event['registration_link']); ?>" 
                                           target="_blank" 
                                           class="inline-flex items-center text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                            Register <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full">
                        <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
                            <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No Events Found</h3>
                            <p class="text-gray-400">There are no events matching your criteria at the moment.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Add search functionality
        document.querySelector('input[type="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const search = this.value.trim();
                const currentFilter = '<?php echo $filter; ?>';
                window.location.href = `?filter=${currentFilter}${search ? '&search=' + encodeURIComponent(search) : ''}`;
            }
        });
    </script>
</body>
</html>