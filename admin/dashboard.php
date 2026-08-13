<?php include '../components/adminHeader.php'; ?>

<?php
// Count alumni
$alumni_query = "SELECT COUNT(*) as total FROM users WHERE role = 'Alumni'";
$alumni_result = mysqli_query($conn, $alumni_query);
if ($alumni_result) {
    $alumni_row = mysqli_fetch_assoc($alumni_result);
    $total_alumni = $alumni_row['total'];
} else {
    $total_alumni = 0;
}

// Count events
$events_query = "SELECT COUNT(*) as total FROM events";
$events_result = mysqli_query($conn, $events_query);
if ($events_result) {
    $events_row = mysqli_fetch_assoc($events_result);
    $total_events = $events_row['total'];
} else {
    $total_events = 0;
}

// Count jobs
$jobs_query = "SELECT COUNT(*) as total FROM jobs";
$jobs_result = mysqli_query($conn, $jobs_query);
if ($jobs_result) {
    $jobs_row = mysqli_fetch_assoc($jobs_result);
    $total_jobs = $jobs_row['total'];
} else {
    $total_jobs = 0;
}

// Count news
$news_query = "SELECT COUNT(*) as total FROM news WHERE status = 'Published'";
$news_result = mysqli_query($conn, $news_query);
if ($news_result) {
    $news_row = mysqli_fetch_assoc($news_result);
    $total_news = $news_row['total'];
} else {
    $total_news = 0;
}

// Get activity data
$activity_query = "SELECT 
    DATE_FORMAT(date_created, '%Y-%m') as month,
    COUNT(*) as count
    FROM users 
    WHERE role = 'Alumni'
    AND date_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC";

$activity_result = mysqli_query($conn, $activity_query);
$activity_data = [];

if ($activity_result) {
    while ($row = mysqli_fetch_assoc($activity_result)) {
        $activity_data[] = [
            'month' => date('M Y', strtotime($row['month'] . '-01')),
            'count' => (int)$row['count']
        ];
    }
}

// Get course distribution data
$distribution_query = "SELECT 
    COALESCE(c.name, 'No Course') as course_name,
    COUNT(*) as count
    FROM users u
    LEFT JOIN courses c ON u.course_id = c.course_id
    WHERE u.role = 'Alumni'
    GROUP BY c.name
    ORDER BY count DESC
    LIMIT 5";

$distribution_result = mysqli_query($conn, $distribution_query);
$distribution_data = [];

if ($distribution_result) {
    while ($row = mysqli_fetch_assoc($distribution_result)) {
        $distribution_data[] = [
            'course' => $row['course_name'] ? $row['course_name'] : 'No Course',
            'count' => (int)$row['count']
        ];
    }
}
?>

        <!-- Dashboard Content -->
        <div class="pt-16 sm:pt-20 px-3 sm:px-4 lg:px-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6 lg:mb-8">
                <div>
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Dashboard Overview</h1>
                    <p class="text-gray-400 text-xs sm:text-sm lg:text-base">Welcome back, Admin! Here's what's happening today.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
                <!-- Total Alumni -->
                <div class="glass rounded-xl p-3 sm:p-4 lg:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-400 text-xs sm:text-sm">Total Alumni</p>
                            <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mt-1 truncate"><?php echo number_format($total_alumni); ?></h3>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-full bg-gradient-to-br from-neon-blue to-neon-purple flex items-center justify-center flex-shrink-0 ml-2 sm:ml-3">
                            <i class="fas fa-users text-white text-xs sm:text-sm lg:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-3 lg:mt-4">
                        <span class="text-neon-green text-xs"><i class="fas fa-arrow-up"></i> 12%</span>
                    </div>
                </div>

                <!-- Active Events -->
                <div class="glass rounded-xl p-3 sm:p-4 lg:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-400 text-xs sm:text-sm">Active Events</p>
                            <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mt-1 truncate"><?php echo number_format($total_events); ?></h3>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-full bg-gradient-to-br from-neon-purple to-neon-pink flex items-center justify-center flex-shrink-0 ml-2 sm:ml-3">
                            <i class="fas fa-calendar-alt text-white text-xs sm:text-sm lg:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-3 lg:mt-4">
                        <span class="text-neon-green text-xs"><i class="fas fa-arrow-up"></i> 8%</span>
                    </div>
                </div>

                <!-- Job Postings -->
                <div class="glass rounded-xl p-3 sm:p-4 lg:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-400 text-xs sm:text-sm">Job Postings</p>
                            <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mt-1 truncate"><?php echo number_format($total_jobs); ?></h3>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-full bg-gradient-to-br from-neon-green to-neon-blue flex items-center justify-center flex-shrink-0 ml-2 sm:ml-3">
                            <i class="fas fa-briefcase text-white text-xs sm:text-sm lg:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-3 lg:mt-4">
                        <span class="text-neon-green text-xs"><i class="fas fa-arrow-up"></i> 23%</span>
                    </div>
                </div>

                <!-- News Articles -->
                <div class="glass rounded-xl p-3 sm:p-4 lg:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-400 text-xs sm:text-sm">News Articles</p>
                            <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-white mt-1 truncate"><?php echo number_format($total_news); ?></h3>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-full bg-gradient-to-br from-neon-pink to-neon-purple flex items-center justify-center flex-shrink-0 ml-2 sm:ml-3">
                            <i class="fas fa-newspaper text-white text-xs sm:text-sm lg:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-3 lg:mt-4">
                        <span class="text-neon-green text-xs"><i class="fas fa-arrow-up"></i> 15%</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
                <!-- Activity Chart -->
                <div class="glass rounded-xl p-3 sm:p-4 lg:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <h3 class="text-sm sm:text-base lg:text-lg font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Activity Overview</h3>
                        <div class="flex space-x-1 sm:space-x-2">
                            <button class="p-1.5 sm:p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20">
                                <i class="fas fa-ellipsis-v text-xs sm:text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-[200px] sm:h-[250px] lg:h-[300px]">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Distribution Chart -->
                <div class="glass rounded-xl p-3 sm:p-4 lg:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <h3 class="text-sm sm:text-base lg:text-lg font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Alumni Distribution</h3>
                        <div class="flex space-x-1 sm:space-x-2">
                            <button class="p-1.5 sm:p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20">
                                <i class="fas fa-ellipsis-v text-xs sm:text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-[200px] sm:h-[250px] lg:h-[300px]">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>



        
    </main>

    
    <script src="../assets/js/toggleHandle.js"></script>
    <script src="../assets/js/modalFunction.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($activity_data, 'month')); ?>,
            datasets: [{
                label: 'New Alumni',
                data: <?php echo json_encode(array_column($activity_data, 'count')); ?>,
                borderColor: '#00f2ff',
                backgroundColor: 'rgba(0, 242, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#9ca3af',
                        font: {
                            size: window.innerWidth < 640 ? 10 : 12
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9ca3af',
                        font: {
                            size: window.innerWidth < 640 ? 10 : 12
                        },
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });

    // Distribution Chart
    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    const distributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($distribution_data, 'course')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($distribution_data, 'count')); ?>,
                backgroundColor: [
                    'rgba(0, 242, 255, 0.8)',
                    'rgba(124, 58, 237, 0.8)',
                    'rgba(255, 0, 255, 0.8)',
                    'rgba(0, 255, 157, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: window.innerWidth < 1024 ? 'bottom' : 'right',
                    labels: {
                        color: '#9ca3af',
                        padding: window.innerWidth < 640 ? 10 : 20,
                        font: {
                            size: window.innerWidth < 640 ? 10 : 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            cutout: window.innerWidth < 640 ? '60%' : '70%'
        }
    });

    // Handle window resize for charts
    window.addEventListener('resize', function() {
        activityChart.resize();
        distributionChart.resize();
    });
    </script>

</body>
</html>