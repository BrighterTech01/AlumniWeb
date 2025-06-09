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
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the jobs query based on filters
$jobs_query = "SELECT * FROM view_job_postings WHERE 1=1";

if ($filter === 'active') {
    $jobs_query .= " AND application_deadline >= CURDATE()";
} elseif ($filter === 'expired') {
    $jobs_query .= " AND application_deadline < CURDATE()";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $jobs_query .= " AND (job_title LIKE '%$search%' OR company_name LIKE '%$search%' OR location LIKE '%$search%' OR description LIKE '%$search%')";
}

$jobs_query .= " ORDER BY created_at DESC";
$jobs_result = mysqli_query($conn, $jobs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMSCST Alumni Portal - Job Board</title>
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
        .job-card {
            transition: transform 0.2s ease-in-out;
        }
        .job-card:hover {
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
                        <h1 class="text-2xl font-bold mb-2">Job Board</h1>
                        <p class="text-gray-400">Find your next career opportunity from our curated job listings.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <input type="search" 
                                   placeholder="Search jobs..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="?filter=active<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 rounded-lg <?php echo $filter === 'active' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'bg-light/20 text-gray-400 hover:text-white transition-colors'; ?>">
                                Active Jobs
                            </a>
                            <a href="?filter=expired<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 rounded-lg <?php echo $filter === 'expired' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'bg-light/20 text-gray-400 hover:text-white transition-colors'; ?>">
                                Expired Jobs
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jobs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (mysqli_num_rows($jobs_result) > 0): ?>
                    <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                        <div class="job-card glass rounded-xl overflow-hidden border border-lighter/10">
                            <?php if (!empty($job['job_image'])): ?>
                                <img src="./assets/uploads/jobs/<?php echo htmlspecialchars($job['job_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-neon-purple/10 flex flex-col items-center justify-center">
                                    <i class="fas fa-building text-4xl text-neon-purple mb-2"></i>
                                    <span class="text-neon-purple font-bold text-xl"><?php echo htmlspecialchars($job['company_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                    <span class="px-3 py-1 rounded-full text-xs <?php echo strtotime($job['application_deadline']) >= strtotime('today') ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-500/20 text-gray-400'; ?>">
                                        <?php echo strtotime($job['application_deadline']) >= strtotime('today') ? 'Active' : 'Expired'; ?>
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-400 mb-4"><?php echo substr(htmlspecialchars($job['description']), 0, 150) . '...'; ?></p>
                                
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-building w-5"></i>
                                        <span><?php echo htmlspecialchars($job['company_name']); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-map-marker-alt w-5"></i>
                                        <span><?php echo htmlspecialchars($job['location']); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-briefcase w-5"></i>
                                        <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                                    </div>
                                    <?php if (!empty($job['salary'])): ?>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-money-bill-wave w-5"></i>
                                        <span>₱<?php echo number_format($job['salary'], 2); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-clock w-5"></i>
                                        <span>Apply before <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="job_details.php?id=<?php echo $job['job_id']; ?>" 
                                           class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                            Details
                                        </a>
                                        <?php if (!empty($job['url'])): ?>
                                        <a href="<?php echo htmlspecialchars($job['url']); ?>" 
                                           target="_blank" 
                                           class="text-sm text-neon-purple hover:text-neon-purple/80 transition-colors">
                                            Apply <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full">
                        <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
                            <i class="fas fa-briefcase text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No Jobs Found</h3>
                            <p class="text-gray-400">There are no job listings matching your criteria at the moment.</p>
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
</html>