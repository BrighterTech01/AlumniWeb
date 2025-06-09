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
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$batch = isset($_GET['batch']) ? $_GET['batch'] : '';
$course = isset($_GET['course']) ? $_GET['course'] : '';

// Build the alumni query based on filters
$alumni_query = "SELECT * FROM view_user_details WHERE role = 'Alumni' AND status = 1";

if ($filter === 'recent') {
    $alumni_query .= " ORDER BY date_created DESC";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $alumni_query .= " AND (firstname LIKE '%$search%' OR lastname LIKE '%$search%' OR course_name LIKE '%$search%' OR batch LIKE '%$search%')";
}

if (!empty($batch)) {
    $batch = mysqli_real_escape_string($conn, $batch);
    $alumni_query .= " AND batch = '$batch'";
}

if (!empty($course)) {
    $course = mysqli_real_escape_string($conn, $course);
    $alumni_query .= " AND course_id = '$course'";
}

$alumni_query .= " ORDER BY lastname ASC";
$alumni_result = mysqli_query($conn, $alumni_query);

// Get unique batches for filter
$batches_query = "SELECT DISTINCT batch FROM view_user_details WHERE role = 'Alumni' AND status = 1 ORDER BY batch DESC";
$batches_result = mysqli_query($conn, $batches_query);

// Get unique courses for filter
$courses_query = "SELECT DISTINCT course_id, course_name FROM view_user_details WHERE role = 'Alumni' AND status = 1 ORDER BY course_name ASC";
$courses_result = mysqli_query($conn, $courses_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMSCST Alumni Portal - Alumni Directory</title>
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
        .alumni-card {
            transition: transform 0.2s ease-in-out;
        }
        .alumni-card:hover {
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
                        <h1 class="text-2xl font-bold mb-2">Alumni Directory</h1>
                        <p class="text-gray-400">Connect with fellow NMSCST alumni and expand your network.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative flex-1">
                            <input type="search" 
                                   placeholder="Search alumni..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <select name="batch" class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                                <option value="">All Batches</option>
                                <?php while ($batch_row = mysqli_fetch_assoc($batches_result)): ?>
                                    <option value="<?php echo htmlspecialchars($batch_row['batch']); ?>" 
                                            <?php echo $batch == $batch_row['batch'] ? 'selected' : ''; ?>>
                                        Batch <?php echo htmlspecialchars($batch_row['batch']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <select name="course" class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                                <option value="">All Courses</option>
                                <?php while ($course_row = mysqli_fetch_assoc($courses_result)): ?>
                                    <option value="<?php echo htmlspecialchars($course_row['course_id']); ?>"
                                            <?php echo $course == $course_row['course_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course_row['course_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <a href="?filter=recent<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($batch) ? '&batch=' . urlencode($batch) : ''; ?><?php echo !empty($course) ? '&course=' . urlencode($course) : ''; ?>" 
                               class="px-4 py-2 rounded-lg <?php echo $filter === 'recent' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'bg-light/20 text-gray-400 hover:text-white transition-colors'; ?>">
                                Recent
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alumni Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (mysqli_num_rows($alumni_result) > 0): ?>
                    <?php while ($alumni = mysqli_fetch_assoc($alumni_result)): ?>
                        <div class="alumni-card glass rounded-xl overflow-hidden border border-lighter/10">
                            <div class="relative">
                                <?php if (!empty($alumni['image'])): ?>
                                    <img src="./assets/uploads/alumni/<?php echo htmlspecialchars($alumni['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($alumni['firstname'] . ' ' . $alumni['lastname']); ?>" 
                                         class="w-full h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-neon-blue/10 flex flex-col items-center justify-center">
                                        <i class="fas fa-user-graduate text-4xl text-neon-blue mb-2"></i>
                                        <span class="text-neon-blue font-bold text-xl"><?php echo htmlspecialchars($alumni['firstname'] . ' ' . $alumni['lastname']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($alumni['firstname'] . ' ' . $alumni['lastname']); ?></h3>
                                    <span class="px-3 py-1 rounded-full text-xs bg-neon-blue/20 text-neon-blue">
                                        Batch <?php echo htmlspecialchars($alumni['batch']); ?>
                                    </span>
                                </div>
                                
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-graduation-cap w-5"></i>
                                        <span><?php echo htmlspecialchars($alumni['course_name']); ?></span>
                                    </div>
                                    <?php if (!empty($alumni['contact'])): ?>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-phone w-5"></i>
                                        <span><?php echo htmlspecialchars($alumni['contact']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($alumni['address'])): ?>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-map-marker-alt w-5"></i>
                                        <span><?php echo htmlspecialchars($alumni['city'] . ', ' . $alumni['country']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center justify-between pt-4 border-t border-lighter/10">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-envelope w-5"></i>
                                        <span><?php echo htmlspecialchars($alumni['email']); ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="alumni_profile.php?id=<?php echo $alumni['user_id']; ?>" 
                                           class="text-sm text-neon-blue hover:text-neon-blue/80 transition-colors">
                                            View Profile
                                        </a>
                                        <button class="text-sm text-neon-purple hover:text-neon-purple/80 transition-colors">
                                            Connect <i class="fas fa-user-plus ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full">
                        <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
                            <i class="fas fa-user-graduate text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No Alumni Found</h3>
                            <p class="text-gray-400">There are no alumni profiles matching your criteria at the moment.</p>
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
                updateFilters();
            }
        });

        // Add change event listeners to selects
        document.querySelector('select[name="batch"]').addEventListener('change', updateFilters);
        document.querySelector('select[name="course"]').addEventListener('change', updateFilters);

        function updateFilters() {
            const search = document.querySelector('input[type="search"]').value.trim();
            const batch = document.querySelector('select[name="batch"]').value;
            const course = document.querySelector('select[name="course"]').value;
            const currentFilter = '<?php echo $filter; ?>';
            
            let url = `?filter=${currentFilter}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (batch) url += `&batch=${encodeURIComponent(batch)}`;
            if (course) url += `&course=${encodeURIComponent(course)}`;
            
            window.location.href = url;
        }
    </script>
</body>
</html>