<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

include_once 'connection.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT u.*, a.firstname, a.lastname, a.image 
          FROM users u 
          LEFT JOIN alumnus a ON u.alumnus_id = a.alumnus_id 
          WHERE u.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get user's full name
$fullname = $user['firstname'] . ' ' . $user['lastname'];
$image = $user['image'] ? '../assets/uploads/alumni/' . $user['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '&background=random';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMSCST Alumni Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link href="../assets/css/output.css" rel="stylesheet">
    <script>
        tailwind.config = {
            content: [
                "./**/*.{html,js,php}",
                "./admin/**/*.{html,js,php}",
                "./includes/**/*.{html,js,php}"
            ],
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#f59e0b',
                        dark: '#0f172a',
                        darker: '#020617',
                        light: '#1e293b',
                        lighter: '#334155',
                        neon: {
                            blue: '#00f2ff',
                            purple: '#7c3aed',
                            pink: '#ff00ff',
                            green: '#00ff9d'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                        'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
                    }
                }
            }
        }
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    
</head>
<body class="bg-dark text-white min-h-screen flex">
    <!-- Mobile Sidebar Toggle & Overlay -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden backdrop-blur-sm"></div>
    <aside id="sidebar" class="sidebar fixed z-40 top-0 left-0 flex-col justify-between w-64 bg-darker/95 px-5 py-6 border-r border-lighter/10 h-screen overflow-y-auto hidden md:flex backdrop-blur-md">
        <div>
            <div class="flex items-center space-x-3 mb-10">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-neon-blue to-neon-purple flex items-center justify-center text-black font-bold">
                    <i class="fas fa-graduation-cap text-sm"></i>
                </div>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon-blue to-neon-purple font-bold text-lg tracking-wide">NMSCST ALUMNI</span>
            </div>
            
            <nav class="flex flex-col space-y-1 text-gray-300 text-sm font-medium">
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="dashboard.php">
                    <i class="fas fa-chart-line w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_alumni.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_alumni.php">
                    <i class="fas fa-users w-5 text-center"></i>
                    <span>Alumni</span>
                    <span class="ml-auto bg-gradient-to-r from-neon-blue to-neon-purple text-white text-xs px-2 py-0.5 rounded-full">1.2k</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_events.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_events.php">
                    <i class="fas fa-calendar-alt w-5 text-center"></i>
                    <span>Events</span>
                    <span class="ml-auto bg-gradient-to-r from-neon-blue to-neon-purple text-white text-xs px-2 py-0.5 rounded-full">3</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_jobs.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_jobs.php">
                    <i class="fas fa-briefcase w-5 text-center"></i>
                    <span>Jobs</span>
                    <span class="ml-auto bg-gradient-to-r from-neon-blue to-neon-purple text-white text-xs px-2 py-0.5 rounded-full">12</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_jobs.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_stories.php">
                    <i class="fas fa-briefcase w-5 text-center"></i>
                    <span>Stories</span>
                    <span class="ml-auto bg-gradient-to-r from-neon-blue to-neon-purple text-white text-xs px-2 py-0.5 rounded-full">12</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="announcements.php">
                    <i class="fas fa-newspaper w-5 text-center"></i>
                    <span>Announcements</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_testimonials.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_testimonials.php">
                    <i class="fas fa-comment-alt w-5 text-center"></i>
                    <span>Testimonials</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_news.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_news.php">
                    <i class="fas fa-newspaper w-5 text-center"></i>
                    <span>News</span>
                </a>
               
            </nav>
        </div>
        
        <div class="text-gray-400 text-xs">
            <p>Alumni Connect v2.0 © 2024</p>
        </div>
    </aside>

    <!-- Main content -->
    <main id="main-content" class="flex-1 flex flex-col bg-gradient-to-br from-dark to-darker md:ml-64 transition-all duration-300">
        <!-- Navbar -->
        <nav class="glass border-b border-lighter/10 px-6 py-4 fixed w-full md:w-[calc(100%-16rem)] z-20 flex items-center justify-between">
            <!-- Mobile Hamburger -->
            <button id="sidebar-toggle" class="md:hidden mr-4 p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20 transition-colors focus:outline-none">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <!-- Search Bar -->
            <div class="flex-1 max-w-xl">
                <div class="relative">
                    <input type="search" 
                           placeholder="Search alumni, events, jobs..." 
                           class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <!-- Right Side Items -->
            <div class="flex items-center space-x-6">
                <!-- Notifications -->
                <div class="relative">
                    <button class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20 transition-colors">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-neon-blue rounded-full"></span>
                    </button>
                </div>
                <!-- Messages -->
                <div class="relative">
                    <button class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20 transition-colors">
                        <i class="fas fa-envelope"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-neon-purple rounded-full"></span>
                    </button>
                </div>
                <!-- Profile -->
                <div class="flex items-center space-x-3 relative">
                    <div class="relative">
                        <img src="<?php echo $image; ?>" 
                             alt="Profile" 
                             class="w-8 h-8 rounded-full cursor-pointer ring-2 ring-neon-blue">
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-neon-green border-2 border-darker rounded-full"></span>
                    </div>
                    <div class="hidden md:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($fullname); ?></p>
                        <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['role']); ?></p>
                    </div>
                    <button onclick="profileToggle()" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20 transition-colors">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profileDropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-darker border border-lighter/10 rounded-lg shadow-lg py-2 z-50">
                        <a href="../admin/adminProfile.php" class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-300 hover:bg-light/20 hover:text-white transition-colors">
                            <i class="fas fa-user w-5"></i>
                            <span>Profile</span>
                        </a>
                     
                        <div class="border-t border-lighter/10 my-1"></div>
                        <a href="../logout.php" class="flex items-center space-x-2 px-4 py-2 text-sm text-red-400 hover:bg-light/20 hover:text-red-300 transition-colors">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main content starts here -->
        <!-- Your page content will be inserted here -->

   
    <script>
        function profileToggle() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function closeDropdown(e) {
                if (!e.target.closest('#profileDropdown') && !e.target.closest('button[onclick="profileToggle()"]')) {
                    dropdown.classList.add('hidden');
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }
    </script>
