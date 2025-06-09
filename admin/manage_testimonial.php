<?php
   include_once '../components/connection.php';


// Query to fetch alumni details from the view
$query = "SELECT * FROM view_alumni_details ORDER BY date_created DESC";
$result = mysqli_query($conn, $query);
$alumni = [];


// Check if query was successful and has results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($alumni, $row);
    }
}

?>



<?php include_once '../components/adminHeader.php'?>
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
                        <input type="search" 
                               placeholder="Search alumni..." 
                               class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        <option value="">All Courses</option>
                     
                    </select>
                    <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        <option value="">All Batches</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                    </select>
                    <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Alumni Table -->
            <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover overflow-x-auto">
                <table class="min-w-full divide-y divide-lighter/20">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Gender</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Batch</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Course</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Contact</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Location</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-lighter/10">
                        <?php if (empty($alumni)): ?>
                        <tr>
                            <td colspan="10" class="px-4 py-3 text-center text-gray-400">
                                No alumni records found
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($alumni as $a): ?>
                        <tr class="hover:bg-light/5 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($a['alumnus_id']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-full" 
                                             src="<?php echo !empty($a['image']) ? htmlspecialchars($a['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($a['firstname'] . '+' . $a['lastname']) . '&background=random'; ?>" 
                                             alt="Profile">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">
                                            <?php echo htmlspecialchars($a['firstname'] . ' ' . $a['middlename'] . ' ' . $a['lastname']); ?>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            <?php echo htmlspecialchars($a['username']); ?> (<?php echo htmlspecialchars($a['role']); ?>)
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($a['gender']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                <?php echo htmlspecialchars($a['batch']); ?>
                                <div class="text-xs text-gray-400">Grad: <?php echo htmlspecialchars($a['graduation_year']); ?></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($a['course_name']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($a['contact']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                <div><?php echo htmlspecialchars($a['alumnus_email']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($a['user_email']); ?></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                                <div><?php echo htmlspecialchars($a['city'] . ', ' . $a['country']); ?></div>
                                <div class="text-xs text-gray-400 truncate max-w-[200px]" title="<?php echo htmlspecialchars($a['address']); ?>">
                                    <?php echo htmlspecialchars($a['address']); ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $a['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400'; ?>">
                                    <?php echo $a['status'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                <div class="text-xs text-gray-400 mt-1">
                                    <?php echo date('M d, Y', strtotime($a['date_created'])); ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <button onclick="confirmDelete(<?php echo $a['alumnus_id']; ?>)" class="text-neon-pink hover:text-white transition-colors" title="Delete">
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
                        Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">12</span> results
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors">
                            Previous
                        </button>
                        <button class="px-3 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/toggleHandle.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this alumni?')) {
                fetch(`../api/delete_alumni.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Alumni deleted successfully');
                        location.reload();
                    } else {
                        alert('Error deleting alumni');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting alumni');
                });
            }
        }
    </script>
</body>
</html>