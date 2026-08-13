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
$query = "SELECT *
          FROM users 
          WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get user's full name
$fullname = trim($user['firstname'] . ' ' . ($user['middlename'] ? $user['middlename'] . ' ' : '') . $user['lastname']);
// Set profile image path
$image = (!empty($user['image']) && $user['image'] != 'default.png') 
    ? '../assets/uploads/alumni/' . htmlspecialchars($user['image']) 
    : '../assets/uploads/alumni/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMSCST Alumni Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,container-queries"></script>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link href="../assets/css/output.css" rel="stylesheet">

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/modalFunction.js"></script>
    
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
            <!-- Profile Section at Top -->
            <div class="mb-8 pb-6 border-b border-lighter/10">
                <div class="flex flex-col items-center">
                    <div class="relative mb-3">
                        <img src="<?php echo $image; ?>" 
                             alt="Profile" 
                             class="w-16 h-16 rounded-full object-cover ring-4 ring-neon-blue/30"
                             onerror="this.src='../assets/uploads/alumni/default.png'">
                        <span class="absolute bottom-0 right-0 w-4 h-4 bg-neon-green border-2 border-darker rounded-full"></span>
                    </div>
                    <h3 class="text-white font-semibold text-center"><?php echo htmlspecialchars($fullname); ?></h3>
                    <p class="text-gray-400 text-xs text-center mt-1"><?php echo htmlspecialchars($user['role']); ?></p>
                </div>
            </div>
            
            <nav class="flex flex-col space-y-1 text-gray-300 text-sm font-medium">
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="dashboard.php">
                    <i class="fas fa-chart-line w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_alumni.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_alumni.php">
                    <i class="fas fa-users w-5 text-center"></i>
                    <span>Alumni</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_events.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_events.php">
                    <i class="fas fa-calendar-alt w-5 text-center"></i>
                    <span>Events</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_jobs.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_jobs.php">
                    <i class="fas fa-briefcase w-5 text-center"></i>
                    <span>Jobs</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_stories.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_stories.php">
                    <i class="fas fa-book-open w-5 text-center"></i>
                    <span>Stories</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="announcements.php">
                    <i class="fas fa-newspaper w-5 text-center"></i>
                    <span>Announcements</span>
                </a>
                <a class="flex items-center space-x-3 p-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_testimonials.php' ? 'bg-primary/20 text-white border border-primary/30 neon-glow' : 'hover:bg-light/20 hover:text-white transition-colors'; ?>" href="manage_testimonial.php">
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
        <nav class="glass border-b border-lighter/10 px-4 sm:px-6 py-3 fixed w-full md:w-[calc(100%-16rem)] z-20">
            <div class="flex items-center justify-between">
                <!-- Left Section: Logo & Mobile Hamburger -->
                <div class="flex items-center space-x-3">
                    <button id="sidebar-toggle" class="md:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20 transition-colors focus:outline-none">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center">
                            <img src="../assets/img/nmscLogo.png" alt="Logo" class="w-9 h-9 object-cover">
                        </div>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon-blue to-neon-purple font-bold text-base sm:text-lg tracking-wide hidden sm:inline-block">NMSCST ALUMNI</span>
                    </div>
                </div>
                
                <!-- Right Section: Notifications & Profile -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20 transition-colors relative" title="Notifications">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notificationBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center hidden shadow-lg border-2 border-darker">0</span>
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div id="notificationsDropdown" class="hidden absolute right-0 top-full mt-2 w-80 sm:w-96 bg-darker border border-lighter/10 rounded-xl shadow-2xl z-50 max-h-[500px] overflow-hidden">
                            <!-- Header -->
                            <div class="p-4 border-b border-lighter/10 bg-light/10 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-bell text-neon-blue"></i>
                                    <h3 class="text-white font-semibold">Notifications</h3>
                                    <span id="notificationCount" class="px-2 py-0.5 bg-neon-blue/20 text-neon-blue text-xs rounded-full font-medium">0</span>
                                </div>
                                <button onclick="markAllAsRead()" class="text-xs text-neon-blue hover:text-neon-purple transition-colors px-2 py-1 rounded hover:bg-light/20">
                                    Mark all read
                                </button>
                            </div>
                            
                            <!-- Notifications List -->
                            <div id="notificationsList" class="overflow-y-auto max-h-[400px]">
                                <div class="text-center py-8 text-gray-400">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                    <p class="text-sm">Loading notifications...</p>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="p-3 border-t border-lighter/10 bg-light/5 text-center">
                                <a href="dashboard.php" class="text-xs text-neon-blue hover:text-neon-purple transition-colors">
                                    <i class="fas fa-home mr-1"></i>Go to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile -->
                    <div class="relative">
                        <button onclick="profileToggle()" class="p-1 sm:p-2 rounded-lg hover:bg-light/20 transition-colors group">
                            <div class="relative">
                                <img src="<?php echo $image; ?>" 
                                     alt="Profile" 
                                     class="w-8 h-8 sm:w-9 sm:h-9 rounded-full object-cover ring-2 ring-neon-blue/50 group-hover:ring-neon-blue transition-all"
                                     onerror="this.src='../assets/uploads/alumni/default.png'">
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-neon-green border-2 border-darker rounded-full"></span>
                            </div>
                        </button>

                        <!-- Profile Dropdown Menu -->
                        <div id="profileDropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-darker border border-lighter/10 rounded-lg shadow-xl py-2 z-50">
                            <a href="adminProfile.php" class="flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-light/20 hover:text-white transition-colors">
                                <i class="fas fa-user w-5 text-center text-neon-blue"></i>
                                <span>My Profile</span>
                            </a>
                            <div class="border-t border-lighter/10 my-1"></div>
                            <a href="../logout.php" class="flex items-center space-x-3 px-4 py-2.5 text-sm text-red-400 hover:bg-light/20 hover:text-red-300 transition-colors">
                                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

   

   
    <script>
        function profileToggle() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
            
            // Close notifications if open
            document.getElementById('notificationsDropdown').classList.add('hidden');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function closeDropdown(e) {
                if (!e.target.closest('#profileDropdown') && !e.target.closest('button[onclick="profileToggle()"]')) {
                    dropdown.classList.add('hidden');
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }
        
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationsDropdown');
            dropdown.classList.toggle('hidden');
            
            // Close profile dropdown if open
            document.getElementById('profileDropdown').classList.add('hidden');
            
            // Load notifications when opening
            if(!dropdown.classList.contains('hidden')) {
                loadNotifications();
            }
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function closeNotifications(e) {
                if (!e.target.closest('#notificationsDropdown') && !e.target.closest('button[onclick="toggleNotifications()"]')) {
                    dropdown.classList.add('hidden');
                    document.removeEventListener('click', closeNotifications);
                }
            });
        }
        
        function loadNotifications() {
            fetch('../controllers/notificationController.php?get_notifications=1&limit=10')
                .then(response => response.json())
                .then(notifications => {
                    const list = document.getElementById('notificationsList');
                    const countBadge = document.getElementById('notificationCount');
                    
                    // Update count badge
                    if(countBadge) {
                        countBadge.textContent = notifications.length;
                    }
                    
                    if(notifications.length === 0) {
                        list.innerHTML = `
                            <div class="text-center py-12 text-gray-400">
                                <i class="fas fa-bell-slash text-3xl mb-3 opacity-50"></i>
                                <p class="text-sm">No notifications yet</p>
                                <p class="text-xs mt-1 text-gray-500">You'll see notifications here when someone comments on your events</p>
                            </div>
                        `;
                    } else {
                        list.innerHTML = notifications.map(notif => {
                            // Get entity type icon, color classes, and name
                            let entityIcon = 'fa-file';
                            let bgGradient = 'from-neon-blue/20 to-neon-blue/40';
                            let iconColor = 'text-neon-blue';
                            let badgeBg = 'bg-neon-blue/20';
                            let badgeText = 'text-neon-blue';
                            let entityName = '';
                            
                            switch(notif.entity_type) {
                                case 'event':
                                    entityIcon = 'fa-calendar-alt';
                                    bgGradient = 'from-neon-blue/20 to-neon-blue/40';
                                    iconColor = 'text-neon-blue';
                                    badgeBg = 'bg-neon-blue/20';
                                    badgeText = 'text-neon-blue';
                                    entityName = 'Event';
                                    break;
                                case 'announcement':
                                    entityIcon = 'fa-bullhorn';
                                    bgGradient = 'from-neon-purple/20 to-neon-purple/40';
                                    iconColor = 'text-neon-purple';
                                    badgeBg = 'bg-neon-purple/20';
                                    badgeText = 'text-neon-purple';
                                    entityName = 'Announcement';
                                    break;
                                case 'job':
                                    entityIcon = 'fa-briefcase';
                                    bgGradient = 'from-neon-green/20 to-neon-green/40';
                                    iconColor = 'text-neon-green';
                                    badgeBg = 'bg-neon-green/20';
                                    badgeText = 'text-neon-green';
                                    entityName = 'Job';
                                    break;
                                case 'news':
                                    entityIcon = 'fa-newspaper';
                                    bgGradient = 'from-neon-blue/20 to-neon-blue/40';
                                    iconColor = 'text-neon-blue';
                                    badgeBg = 'bg-neon-blue/20';
                                    badgeText = 'text-neon-blue';
                                    entityName = 'News';
                                    break;
                                case 'story':
                                    entityIcon = 'fa-book-open';
                                    bgGradient = 'from-neon-purple/20 to-neon-purple/40';
                                    iconColor = 'text-neon-purple';
                                    badgeBg = 'bg-neon-purple/20';
                                    badgeText = 'text-neon-purple';
                                    entityName = 'Story';
                                    break;
                            }
                            
                            // Override for replies
                            if(notif.notification_type == 'reply') {
                                bgGradient = 'from-neon-purple/20 to-neon-blue/20';
                                iconColor = 'text-neon-purple';
                                entityIcon = 'fa-reply';
                            }
                            
                            return `
                            <div class="p-4 hover:bg-light/10 transition-colors cursor-pointer border-b border-lighter/5 ${notif.is_read == 0 ? 'bg-light/5 border-l-4 border-neon-blue' : 'border-l-4 border-transparent'}" 
                                 onclick="viewNotification(${notif.notification_id}, '${notif.entity_type}', ${notif.entity_id})">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br ${bgGradient} flex items-center justify-center flex-shrink-0">
                                        <i class="fas ${entityIcon} ${iconColor}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-white ${notif.is_read == 0 ? 'font-semibold' : 'font-normal'} leading-relaxed">
                                            ${escapeHtml(notif.message)}
                                        </p>
                                        <div class="flex items-center space-x-2 mt-2 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-full text-xxs font-semibold ${badgeBg} ${badgeText}">
                                                ${entityName}
                                            </span>
                                            <p class="text-xs text-gray-400">
                                                <i class="fas fa-clock mr-1"></i>${formatTime(notif.created_at)}
                                            </p>
                                            ${notif.entity_title ? `<span class="text-xs text-gray-500 truncate max-w-[150px]" title="${escapeHtml(notif.entity_title)}">• ${escapeHtml(notif.entity_title.substring(0, 25))}${notif.entity_title.length > 25 ? '...' : ''}</span>` : ''}
                                        </div>
                                    </div>
                                    ${notif.is_read == 0 ? '<div class="w-2 h-2 bg-neon-blue rounded-full flex-shrink-0 mt-2 animate-pulse"></div>' : ''}
                                </div>
                            </div>
                        `;
                        }).join('');
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('notificationsList').innerHTML = `
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2 text-red-400"></i>
                            <p class="text-sm">Error loading notifications</p>
                            <button onclick="loadNotifications()" class="mt-2 text-xs text-neon-blue hover:text-neon-purple">Try again</button>
                        </div>
                    `;
                });
        }
        
        function loadUnreadCount() {
            fetch('../controllers/notificationController.php?get_unread_count=1')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if(data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Error loading unread count:', error));
        }
        
        function markAsRead(notificationId) {
            const formData = new FormData();
            formData.append('mark_read', '1');
            formData.append('notification_id', notificationId);
            
            fetch('../controllers/notificationController.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        loadNotifications();
                        loadUnreadCount();
                    }
                })
                .catch(error => console.error('Error marking as read:', error));
        }
        
        function markAllAsRead() {
            const formData = new FormData();
            formData.append('mark_all_read', '1');
            
            fetch('../controllers/notificationController.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        loadNotifications();
                        loadUnreadCount();
                    }
                })
                .catch(error => console.error('Error marking all as read:', error));
        }
        
        function viewNotification(notificationId, entityType, entityId) {
            // Mark as read
            markAsRead(notificationId);
            
            // Navigate to the entity based on type
            let url = '';
            let modalFunction = null;
            
            switch(entityType) {
                case 'event':
                    url = `manage_events.php`;
                    modalFunction = typeof openEventViewModal !== 'undefined' ? openEventViewModal : null;
                    break;
                case 'announcement':
                    url = `announcements.php`;
                    modalFunction = typeof openAnnouncementViewModal !== 'undefined' ? openAnnouncementViewModal : null;
                    break;
                case 'job':
                    url = `manage_jobs.php`;
                    modalFunction = typeof openJobViewModal !== 'undefined' ? openJobViewModal : null;
                    break;
                case 'news':
                    url = `manage_news.php`;
                    modalFunction = typeof openNewsViewModal !== 'undefined' ? openNewsViewModal : null;
                    break;
                case 'story':
                    url = `manage_stories.php`;
                    modalFunction = typeof openStoryViewModal !== 'undefined' ? openStoryViewModal : null;
                    break;
                default:
                    url = `dashboard.php`;
            }
            
            // Try to open modal first, otherwise navigate to page
            if(modalFunction) {
                modalFunction(entityId);
            } else {
                window.location.href = url;
            }
            
            // Close notifications dropdown
            document.getElementById('notificationsDropdown').classList.add('hidden');
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);
            
            if(seconds < 60) return 'Just now';
            if(minutes < 60) return `${minutes}m ago`;
            if(hours < 24) return `${hours}h ago`;
            if(days < 7) return `${days}d ago`;
            
            return date.toLocaleDateString();
        }
        
        // Load unread count on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadUnreadCount();
            // Refresh count every 30 seconds
            setInterval(loadUnreadCount, 30000);
        });
    </script>
