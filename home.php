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

// Calculate profile completion
$required_fields = ['firstname', 'lastname', 'gender', 'batch', 'course_id', 'contact', 'address', 'city', 'country', 'graduation_year'];
$completed_fields = 0;
foreach ($required_fields as $field) {
    if (!empty($user[$field])) {
        $completed_fields++;
    }
}
$profile_complete = ($completed_fields == count($required_fields));

// Fetch upcoming events from view_event_alumni_details
$events_query = "SELECT * FROM view_event_alumni_details 
                WHERE schedule >= CURDATE() 
                ORDER BY schedule ASC 
                LIMIT 3";
$events_result = mysqli_query($conn, $events_query);

// Fetch recent news from view_news_details
$news_query = "SELECT * FROM view_news_details WHERE status = 'Published' ORDER BY date_posted DESC LIMIT 3";
$news_result = mysqli_query($conn, $news_query);

// Fetch recent job postings
$jobs_query = "SELECT * FROM view_job_postings WHERE application_deadline >= CURDATE() ORDER BY created_at DESC LIMIT 3";
$jobs_result = mysqli_query($conn, $jobs_query);
?>


    <style>
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .neon-glow {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-white">
    <?php include 'components/userHeader.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="max-w-6xl mx-auto">
            <!-- Welcome Section -->
            <div class="glass rounded-xl p-6 border border-lighter/10 mb-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['firstname']); ?>!</h1>
                        <p class="text-gray-400">Stay connected with your alma mater and fellow alumni.</p>
                    </div>
                    <?php if (!$profile_complete): ?>
                    <div class="bg-neon-blue/10 border border-neon-blue/20 rounded-lg p-4">
                        <p class="text-neon-blue mb-2">Complete your profile to unlock all features</p>
                        <a href="userProfile.php" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                            Complete Profile <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="glass rounded-xl p-6 border border-lighter/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Upcoming Events</p>
                            <h3 class="text-2xl font-bold mt-1"><?php echo mysqli_num_rows($events_result); ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-neon-blue/20 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-neon-blue"></i>
                        </div>
                    </div>
                </div>
                <div class="glass rounded-xl p-6 border border-lighter/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Active Jobs</p>
                            <h3 class="text-2xl font-bold mt-1"><?php echo mysqli_num_rows($jobs_result); ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-neon-purple/20 flex items-center justify-center">
                            <i class="fas fa-briefcase text-neon-purple"></i>
                        </div>
                    </div>
                </div>
                <div class="glass rounded-xl p-6 border border-lighter/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Recent News</p>
                            <h3 class="text-2xl font-bold mt-1"><?php echo mysqli_num_rows($news_result); ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-neon-green/20 flex items-center justify-center">
                            <i class="fas fa-newspaper text-neon-green"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Upcoming Events -->
                <div class="lg:col-span-2">
                    <div class="glass rounded-xl p-6 border border-lighter/10">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold">Upcoming Alumni Events</h2>
                            <a href="events.php" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="space-y-4">
                            <?php if (mysqli_num_rows($events_result) > 0): ?>
                                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                                <div class="flex items-start gap-4 p-4 rounded-lg bg-light/20 hover:bg-light/30 transition-colors">
                                    <?php if (!empty($event['banner'])): ?>
                                    <img src="./assets/uploads/events/<?php echo htmlspecialchars($event['banner']); ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                         class="w-24 h-24 object-cover rounded-lg">
                                    <?php else: ?>
                                    <div class="w-24 h-24 rounded-lg bg-neon-blue/10 flex flex-col items-center justify-center text-center">
                                        <span class="text-neon-blue font-bold"><?php echo date('d', strtotime($event['schedule'])); ?></span>
                                        <span class="text-xs text-gray-400"><?php echo date('M', strtotime($event['schedule'])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <h3 class="font-semibold"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($event['content']); ?></p>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-400">
                                            <span><i class="fas fa-calendar mr-1"></i> <?php echo date('F d, Y', strtotime($event['schedule'])); ?></span>
                                            <span><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-400">
                                            <span>Posted by: <?php echo htmlspecialchars($event['firstname'] . ' ' . $event['lastname']); ?></span>
                                        </div>
                                        <?php if (!empty($event['registration_link'])): ?>
                                        <div class="mt-3">
                                            <a href="<?php echo htmlspecialchars($event['registration_link']); ?>" 
                                               target="_blank" 
                                               class="inline-flex items-center text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                                Register Now <i class="fas fa-external-link-alt ml-1"></i>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-4 rounded-lg bg-light/20 text-center">
                                    <p class="text-gray-400">No upcoming alumni events at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent News -->
                <div class="lg:col-span-1">
                    <div class="glass rounded-xl p-6 border border-lighter/10">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold">Latest News</h2>
                            <a href="news.php" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="space-y-4">
                            <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                            <div class="p-4 rounded-lg bg-light/20 hover:bg-light/30 transition-colors">
                                <?php if (!empty($news['image_url'])): ?>
                                <img src="./assets/uploads/news/<?php echo htmlspecialchars($news['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                     class="w-full h-40 object-cover rounded-lg mb-3">
                                <?php endif; ?>
                                <h3 class="font-semibold"><?php echo htmlspecialchars($news['title']); ?></h3>
                                <p class="text-sm text-gray-400 mt-1"><?php echo substr(htmlspecialchars($news['content']), 0, 100) . '...'; ?></p>
                                <div class="flex items-center gap-4 mt-2 text-sm text-gray-400">
                                    <span><i class="fas fa-calendar mr-1"></i> <?php echo date('M d, Y', strtotime($news['date_posted'])); ?></span>
                                    <span><i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($news['username']); ?></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Opportunities -->
            <div class="mt-6">
                <div class="glass rounded-xl p-6 border border-lighter/10">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">Latest Job Opportunities</h2>
                        <a href="jobs.php" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                        <div class="p-4 rounded-lg bg-light/20 hover:bg-light/30 transition-colors">
                            <?php if (!empty($job['job_image'])): ?>
                            <img src="./assets/uploads/jobs/<?php echo htmlspecialchars($job['job_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                 class="w-full h-40 object-cover rounded-lg mb-3">
                            <?php else: ?>
                            <div class="w-full h-40 rounded-lg bg-neon-purple/10 flex items-center justify-center mb-3">
                                <i class="fas fa-building text-4xl text-neon-purple"></i>
                            </div>
                            <?php endif; ?>
                            <h3 class="font-semibold"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                            <p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($job['company_name']); ?></p>
                            <div class="space-y-2 mt-3">
                                <div class="flex items-center gap-4 text-sm text-gray-400">
                                    <span><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                    <span><i class="fas fa-briefcase mr-1"></i> <?php echo htmlspecialchars($job['job_type']); ?></span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-400">
                                    <span><i class="fas fa-user-tie mr-1"></i> <?php echo htmlspecialchars($job['employee_status']); ?></span>
                                    <?php if (!empty($job['salary'])): ?>
                                    <span><i class="fas fa-money-bill-wave mr-1"></i> ₱<?php echo number_format($job['salary'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-400">
                                    <i class="fas fa-clock mr-1"></i> Apply before <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <a href="job_details.php?id=<?php echo $job['job_id']; ?>" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                                <?php if (!empty($job['url'])): ?>
                                <a href="<?php echo htmlspecialchars($job['url']); ?>" target="_blank" class="text-sm text-neon-purple hover:text-neon-purple/80 transition-colors">
                                    Apply Now <i class="fas fa-external-link-alt ml-1"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality here
    </script>
</body>
</html>