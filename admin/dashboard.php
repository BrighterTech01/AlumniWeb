<?php include '../components/adminHeader.php'; ?>

<?php
// Function to count alumni
function countAlumni($conn) {
    $query = "SELECT COUNT(*) as total FROM view_user_details WHERE role = 'Alumni'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Function to count events
function countEvents($conn) {
    $query = "SELECT COUNT(*) as total FROM events";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Function to count jobs
function countJobs($conn) {
    $query = "SELECT COUNT(*) as total FROM jobs";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Function to count news
function countNews($conn) {
    $query = "SELECT COUNT(*) as total FROM news WHERE status = 'Published'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Function to get activity data
function getActivityData($conn) {
    $query = "SELECT 
        DATE_FORMAT(date_created, '%Y-%m') as month,
        COUNT(*) as count
        FROM view_user_details 
        WHERE role = 'Alumni'
        AND date_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month
        ORDER BY month ASC";
    
    $result = mysqli_query($conn, $query);
    $data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'month' => date('M Y', strtotime($row['month'] . '-01')),
                'count' => (int)$row['count']
            ];
        }
    }
    
    return $data;
}

// Function to get course distribution data
function getCourseDistribution($conn) {
    $query = "SELECT 
        course_name,
        COUNT(*) as count
        FROM view_user_details 
        WHERE role = 'Alumni'
        GROUP BY course_name
        ORDER BY count DESC
        LIMIT 5";
    
    $result = mysqli_query($conn, $query);
    $data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'course' => $row['course_name'],
                'count' => (int)$row['count']
            ];
        }
    }
    
    return $data;
}

// Get counts
$total_alumni = countAlumni($conn);
$total_events = countEvents($conn);
$total_jobs = countJobs($conn);
$total_news = countNews($conn);

// Get activity data
$activity_data = getActivityData($conn);

// Get course distribution data
$distribution_data = getCourseDistribution($conn);
?>

        <!-- Dashboard Content -->
        <div class="pt-20 px-4 sm:px-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Dashboard Overview</h1>
                    <p class="text-gray-400 text-sm sm:text-base">Welcome back, Admin! Here's what's happening today.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- Total Alumni -->
                <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Total Alumni</p>
                            <h3 class="text-xl sm:text-2xl font-bold text-white mt-1"><?php echo number_format($total_alumni); ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-neon-blue to-neon-purple flex items-center justify-center">
                            <i class="fas fa-users text-white text-sm sm:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <span class="text-neon-green text-xs sm:text-sm"><i class="fas fa-arrow-up"></i> 12%</span>
                        <span class="text-gray-400 text-xs sm:text-sm ml-2">from last month</span>
                    </div>
                </div>

                <!-- Active Events -->
                <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Active Events</p>
                            <h3 class="text-xl sm:text-2xl font-bold text-white mt-1"><?php echo number_format($total_events); ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-neon-purple to-neon-pink flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-sm sm:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <span class="text-neon-green text-xs sm:text-sm"><i class="fas fa-arrow-up"></i> 8%</span>
                        <span class="text-gray-400 text-xs sm:text-sm ml-2">from last month</span>
                    </div>
                </div>

                <!-- Job Postings -->
                <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Job Postings</p>
                            <h3 class="text-xl sm:text-2xl font-bold text-white mt-1"><?php echo number_format($total_jobs); ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-neon-green to-neon-blue flex items-center justify-center">
                            <i class="fas fa-briefcase text-white text-sm sm:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <span class="text-neon-green text-xs sm:text-sm"><i class="fas fa-arrow-up"></i> 23%</span>
                        <span class="text-gray-400 text-xs sm:text-sm ml-2">from last month</span>
                    </div>
                </div>

                <!-- News Articles -->
                <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">News Articles</p>
                            <h3 class="text-xl sm:text-2xl font-bold text-white mt-1"><?php echo number_format($total_news); ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-neon-pink to-neon-purple flex items-center justify-center">
                            <i class="fas fa-newspaper text-white text-sm sm:text-base"></i>
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <span class="text-neon-green text-xs sm:text-sm"><i class="fas fa-arrow-up"></i> 15%</span>
                        <span class="text-gray-400 text-xs sm:text-sm ml-2">from last month</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- Activity Chart -->
                <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base sm:text-lg font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Activity Overview</h3>
                        <div class="flex space-x-2">
                            <button class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-[250px] sm:h-[300px]">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Distribution Chart -->
                <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base sm:text-lg font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Alumni Distribution</h3>
                        <div class="flex space-x-2">
                            <button class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-light/20">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-[250px] sm:h-[300px]">
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
                        color: '#9ca3af'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9ca3af'
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
                    position: 'right',
                    labels: {
                        color: '#9ca3af',
                        padding: 20
                    }
                }
            },
            cutout: '70%'
        }
    });
    </script>

</body>
</html>