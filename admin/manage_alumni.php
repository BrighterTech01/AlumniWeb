<?php
include_once '../components/connection.php';

// Query to fetch alumni details from the view
$query = "SELECT * FROM view_user_details ORDER BY date_created DESC";
$result = mysqli_query($conn, $query);
$alumni = [];

// Check if query was successful and has results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $alumni[] = $row;
    }
}

// Fetch courses for filter dropdown
$courses = [];
$course_query = "SELECT * FROM courses ORDER BY name";
$course_result = mysqli_query($conn, $course_query);
if($course_result) {
    while($row = mysqli_fetch_assoc($course_result)) {
        $courses[] = $row;
    }
}

// Fetch unique batches for filter dropdown
$batches = [];
$batch_query = "SELECT DISTINCT batch FROM alumnus ORDER BY batch DESC";
$batch_result = mysqli_query($conn, $batch_query);
if($batch_result) {
    while($row = mysqli_fetch_assoc($batch_result)) {
        $batches[] = $row['batch'];
    }
}

// Display credentials if available
if(isset($_SESSION['new_alumni_credentials']) && isset($_GET['show_credentials'])) {
    $credentials = $_SESSION['new_alumni_credentials'];
    echo '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold mb-4">New Alumni Account Created</h3>
                <p class="mb-4">Please save these login credentials:</p>
                <div class="space-y-2 mb-6">
                    <p><strong>Username:</strong> ' . htmlspecialchars($credentials['username']) . '</p>
                    <p><strong>Password:</strong> ' . htmlspecialchars($credentials['password']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($credentials['email']) . '</p>
                </div>
                <p class="text-sm text-red-600 mb-4">Please note: The user should change their password upon first login.</p>
                <button onclick="this.parentElement.parentElement.remove()" 
                        class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                    Close
                </button>
            </div>
          </div>';
    // Clear the credentials from session
    unset($_SESSION['new_alumni_credentials']);
}
?>

<?php include_once '../components/adminHeader.php' ?>
        <div class="pt-20 px-4 sm:px-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Manage Alumni</h1>
                    <p class="text-gray-400 text-sm sm:text-base">View and manage alumni records</p>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative">
                        <input type="search" id="searchInput" 
                               placeholder="Search alumni..." 
                               class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <select id="courseFilter" class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        <option value="">All Courses</option>
                        <?php foreach($courses as $course): ?>
                            <option value="<?= htmlspecialchars($course['course_id']) ?>">
                                <?= htmlspecialchars($course['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="batchFilter" class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        <option value="">All Batches</option>
                        <?php foreach($batches as $batch): ?>
                            <option value="<?= htmlspecialchars($batch) ?>"><?= htmlspecialchars($batch) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="statusFilter" class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="flex justify-end mt-4">
                    <button onclick="openAlumniModal()" 
                            class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Alumni
                    </button>
                </div>
            </div>
            
            <!-- Alumni Table -->
            <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover overflow-x-auto">
                <table id="alumniTable" class="min-w-full divide-y divide-lighter/20">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                               <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Image</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Course</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Batch</th>
                           
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-lighter/10">
                        <?php if (empty($alumni)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-400">
                                No alumni records found
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($alumni as $a): ?>
                        <tr class="hover:bg-light/5 transition-colors alumni-row" 
                            data-id="<?= htmlspecialchars($a['alumnus_id']) ?>"
                            data-course="<?= htmlspecialchars($a['course_id']) ?>"
                            data-batch="<?= htmlspecialchars($a['batch']) ?>"
                            data-status="<?= htmlspecialchars($a['status']) ?>">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?= htmlspecialchars($a['alumnus_id']) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-full object-cover" 
                                             src="<?= !empty($a['image']) && $a['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($a['image']) : '../assets/uploads/alumni/default.png' ?>" 
                                             alt="Profile">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">
                                            <?= htmlspecialchars($a['firstname'] . ' ' . $a['middlename'] . ' ' . $a['lastname']) ?>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            <?= htmlspecialchars($a['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?= htmlspecialchars($a['course_name']) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                <?= htmlspecialchars($a['batch']) ?>
                                <div class="text-xs text-gray-400">Grad: <?= htmlspecialchars($a['graduation_year']) ?></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                <div><?= htmlspecialchars($a['contact']) ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($a['city'] . ', ' . $a['country']) ?></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $a['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400' ?>">
                                    <?= $a['status'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <button onclick="openViewModal(<?= $a['alumnus_id'] ?>)" 
                                            class="text-neon-green hover:text-white transition-colors" 
                                            title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="openAlumniModal(<?= $a['alumnus_id'] ?>)" 
                                            class="text-neon-blue hover:text-white transition-colors" 
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="openDeleteModal(<?= $a['alumnus_id'] ?>)" 
                                            class="text-neon-pink hover:text-white transition-colors" 
                                            title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="flex items-center justify-between mt-4">
                    <div class="text-sm text-gray-400">
                        Showing <span class="font-medium">1</span> to <span class="font-medium"><?= count($alumni) ?></span> of <span class="font-medium"><?= count($alumni) ?></span> results
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors disabled:opacity-50" disabled>
                            Previous
                        </button>
                        <button class="px-3 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors disabled:opacity-50" disabled>
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Include the modal file -->
    <?php include_once '../forms/modalAlumni.php' ?>

    <script src="../assets/js/toggleHandle.js"></script>
    <script src="../assets/js/modalfunction.js"></script>
    
    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const courseFilter = document.getElementById('courseFilter');
            const batchFilter = document.getElementById('batchFilter');
            const statusFilter = document.getElementById('statusFilter');
            const rows = document.querySelectorAll('.alumni-row');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const courseValue = courseFilter.value;
                const batchValue = batchFilter.value;
                const statusValue = statusFilter.value;

                rows.forEach(row => {
                    const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const courseMatch = courseValue === '' || row.dataset.course === courseValue;
                    const batchMatch = batchValue === '' || row.dataset.batch === batchValue;
                    const statusMatch = statusValue === '' || row.dataset.status === statusValue;
                    const searchMatch = name.includes(searchTerm);

                    if (courseMatch && batchMatch && statusMatch && searchMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Add event listeners for filtering
            searchInput.addEventListener('input', filterTable);
            courseFilter.addEventListener('change', filterTable);
            batchFilter.addEventListener('change', filterTable);
            statusFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>