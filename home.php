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

// Fetch user details from users table
$user_id = $_SESSION['user_id'];
$query = "SELECT u.*, c.name AS course_name 
          FROM users u 
          LEFT JOIN courses c ON u.course_id = c.course_id 
          WHERE u.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Construct full name
$fullname = trim($user['firstname'] . ' ' . ($user['middlename'] ? $user['middlename'] . ' ' : '') . $user['lastname']);

// Calculate profile completion
$required_fields = ['firstname', 'lastname', 'gender', 'batch', 'course_id', 'contact', 'district', 'city', 'province', 'country', 'graduation_year'];
$completed_fields = 0;
foreach ($required_fields as $field) {
    if (!empty($user[$field])) {
        $completed_fields++;
    }
}
$profile_complete = ($completed_fields == count($required_fields));

// Fetch upcoming events
$events_query = "SELECT e.*, u.firstname, u.lastname 
                 FROM events e 
                 LEFT JOIN users u ON e.user_id = u.user_id 
                 WHERE e.schedule >= CURDATE() 
                 ORDER BY e.schedule ASC 
                 LIMIT 3";
$events_result = mysqli_query($conn, $events_query);

// Fetch recent news
$news_query = "SELECT n.*, CONCAT(u.firstname, ' ', u.lastname) AS username 
               FROM news n 
               LEFT JOIN users u ON n.user_id = u.user_id 
               WHERE n.status = 'Published' 
               ORDER BY n.date_posted DESC 
               LIMIT 3";
$news_result = mysqli_query($conn, $news_query);

// Fetch recent job postings
$jobs_query = "SELECT j.* 
               FROM jobs j 
               WHERE j.application_deadline >= CURDATE() 
               ORDER BY j.created_at DESC 
               LIMIT 3";
$jobs_result = mysqli_query($conn, $jobs_query);

// Fetch latest approved testimonials
$testimonials_query = "SELECT t.*, u.firstname, u.lastname, u.image, u.batch, c.name AS course_name 
                       FROM testimonials t 
                       JOIN users u ON t.user_id = u.user_id 
                       LEFT JOIN courses c ON u.course_id = c.course_id 
                       WHERE t.status = 'approved' 
                       ORDER BY t.created_at DESC 
                       LIMIT 3";
$testimonials_result = mysqli_query($conn, $testimonials_query);

// Fetch latest announcements
$announcements_query = "SELECT a.* 
                        FROM announcements a 
                        WHERE a.status = 'Active' 
                        ORDER BY a.date_posted DESC 
                        LIMIT 3";
$announcements_result = mysqli_query($conn, $announcements_query);
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

                <!-- Recent News and Announcements -->
                <div class="lg:col-span-1">
                    <div class="glass rounded-xl p-6 border border-lighter/10 mb-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold">Latest News</h2>
                            <a href="news.php" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-lighter/20 text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Author</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-lighter/10">
                                    <?php if (mysqli_num_rows($news_result) > 0): ?>
                                        <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <a href="news.php" class="text-neon-blue hover:underline">
                                                    <?php echo htmlspecialchars($news['title']); ?>
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-gray-400">
                                                <?php echo date('M d, Y', strtotime($news['date_posted'])); ?>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-gray-400">
                                                <?php echo htmlspecialchars($news['username']); ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-center text-gray-400">No news found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="glass rounded-xl p-6 border border-lighter/10">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold">Latest Announcements</h2>
                            <a href="announcements.php" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-lighter/20 text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Content</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Attachment</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-lighter/10">
                                    <?php if (mysqli_num_rows($announcements_result) > 0): ?>
                                        <?php while ($announcement = mysqli_fetch_assoc($announcements_result)): ?>
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <a href="announcement_details.php?id=<?php echo $announcement['announce_id']; ?>" class="text-neon-blue hover:underline">
                                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 text-gray-400 max-w-xs break-words" style="white-space:normal;">
                                                <?php echo htmlspecialchars(mb_strimwidth($announcement['content'], 0, 50, '...')); ?>
                                            </td>
                                            <td class="px-4 py-2 text-gray-400">
                                                <?php if (!empty($announcement['attachment_url'])): ?>
                                                    <a href="<?php echo htmlspecialchars($announcement['attachment_url']); ?>" target="_blank" class="text-neon-blue hover:underline">View</a>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-500">None</span>
                                                <?php endif; ?>
                                            </td>
                                  
                                           
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="px-4 py-3 text-center text-gray-400">No announcements found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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

            <!-- Alumni Testimonials -->
            <div class="mt-6">
                <div class="glass rounded-xl p-6 border border-lighter/10">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">Alumni Testimonials</h2>
                        <a href="testimonials.php" class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php if (mysqli_num_rows($testimonials_result) > 0): ?>
                            <?php while ($testimonial = mysqli_fetch_assoc($testimonials_result)): ?>
                                <div class="p-4 rounded-lg bg-light/20 hover:bg-light/30 transition-colors flex flex-col h-full">
                                    <div class="flex-1">
                                        <p class="text-gray-300 mb-4">"<?php echo nl2br(htmlspecialchars($testimonial['testimonial_text'])); ?>"</p>
                                        <div class="flex gap-1 mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-600'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 pt-4 border-t border-lighter/10 mt-4">
                                        <?php if (!empty($testimonial['image'])): ?>
                                            <img src="./assets/uploads/alumni/<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['firstname'] . ' ' . $testimonial['lastname']); ?>" class="w-10 h-10 rounded-full object-cover">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-neon-blue/10 flex items-center justify-center">
                                                <i class="fas fa-user text-neon-blue"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h3 class="font-semibold text-sm"><?php echo htmlspecialchars($testimonial['firstname'] . ' ' . $testimonial['lastname']); ?></h3>
                                            <p class="text-xs text-gray-500">
                                                Batch <?php echo htmlspecialchars($testimonial['batch']); ?> •
                                                <?php echo htmlspecialchars($testimonial['course_name']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-span-full">
                                <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
                                    <i class="fas fa-comments text-4xl text-gray-400 mb-4"></i>
                                    <h3 class="text-xl font-semibold mb-2">No Testimonials Yet</h3>
                                    <p class="text-gray-400">Be the first to share your success story!</p>
                                </div>
                            </div>
                        <?php endif; ?>
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