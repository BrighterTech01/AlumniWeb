<?php
   include_once '../components/connection.php';


// Query to fetch alumni details from the view
$query = "SELECT * FROM view_testimonials_full ORDER BY created_at DESC";
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
        <div class="pt-16 sm:pt-20 px-2 sm:px-4 lg:px-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6">
                <div>
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Manage Testimonials</h1>
                    <p class="text-gray-400 text-xs sm:text-sm">View and manage testimonial records</p>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="glass rounded-xl p-3 sm:p-4 border border-lighter/10 mb-4 sm:mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div class="relative">
                        <input type="search" 
                               placeholder="Search alumni..." 
                               class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                    <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary/50 text-xs sm:text-sm">
                        <option value="">All Courses</option>
                     
                    </select>
                    <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                        <option value="">All Batches</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                    </select>
                    <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all text-xs sm:text-sm">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Alumni Table -->
            <div class="glass rounded-xl p-2 sm:p-4 border border-lighter/10 card-hover overflow-x-auto">
                <div class="min-w-full">
                    <table class="min-w-full divide-y divide-lighter/20">
                        <thead>
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">ID</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Alumni</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider hidden md:table-cell">Testimonial</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider hidden lg:table-cell">Rating</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider hidden md:table-cell">Created</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-lighter/10">
                            <?php if (empty($alumni)): ?>
                            <tr>
                                <td colspan="7" class="px-2 py-3 text-center text-gray-400 text-xs sm:text-sm">
                                    No testimonial records found
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($alumni as $a): ?>
                            <tr class="hover:bg-light/5 transition-colors">
                                <td class="px-2 py-3 whitespace-nowrap text-xs text-gray-300 hidden sm:table-cell"><?php echo htmlspecialchars($a['testimonial_id']); ?></td>
                                <td class="px-2 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <img class="h-8 w-8 rounded-full object-cover" 
                                                 src="<?= !empty($a['profile_image']) && $a['profile_image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($a['profile_image']) : '../assets/uploads/alumni/default.png' ?>" 
                                                 alt="Profile">
                                        </div>
                                        <div class="ml-2 sm:ml-4 min-w-0 flex-1">
                                            <div class="text-xs sm:text-sm font-medium text-white truncate">
                                                <?php echo htmlspecialchars($a['name']); ?>
                                            </div>
                                            <div class="text-xxs sm:text-xs text-gray-400 truncate">
                                                <?php echo htmlspecialchars($a['user_fullname']); ?> (<?php echo htmlspecialchars($a['user_role']); ?>)
                                            </div>
                                            <!-- Mobile-only info -->
                                            <div class="sm:hidden text-xxs text-gray-500 mt-1">
                                                <div class="truncate max-w-[150px]" title="<?php echo htmlspecialchars($a['testimonial_text']); ?>">
                                                    <?php echo htmlspecialchars($a['testimonial_text']); ?>
                                                </div>
                                                <div class="text-yellow-400">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <?php if ($i < (int)$a['rating']): ?>
                                                            <i class="fas fa-star text-yellow-400 text-xxs"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star text-gray-400 text-xxs"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                                <div><?php echo date('M d, Y', strtotime($a['created_at'])); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-300 max-w-xs truncate hidden md:table-cell" title="<?php echo htmlspecialchars($a['testimonial_text']); ?>">
                                    <?php echo htmlspecialchars($a['testimonial_text']); ?>
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap text-xs sm:text-sm text-yellow-400 hidden lg:table-cell">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <?php if ($i < (int)$a['rating']): ?>
                                            <i class="fas fa-star text-yellow-400"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-gray-400"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap text-xs">
                                    <span class="px-2 py-1 rounded-full text-xxs sm:text-xs font-semibold <?php 
                                        if ($a['status'] === 'approved') echo 'bg-neon-green/20 text-neon-green';
                                        elseif ($a['status'] === 'pending') echo 'bg-yellow-500/20 text-yellow-500';
                                        else echo 'bg-gray-600/30 text-gray-400';
                                    ?>">
                                        <?php echo ucfirst($a['status']); ?>
                                    </span>
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-400 hidden md:table-cell">
                                    <?php echo date('M d, Y', strtotime($a['created_at'])); ?>
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap text-xs">
                                    <div class="flex space-x-1 sm:space-x-2">
                                        <?php if ($a['status'] === 'pending'): ?>
                                            <form method="post" action="../controllers/testimonialController.php" style="display:inline;">
                                                <input type="hidden" name="testimonial_id" value="<?php echo $a['testimonial_id']; ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" name="update_testimonial" class="px-2 py-1 rounded bg-neon-green/20 text-neon-green hover:bg-neon-green/40 transition-colors text-xxs sm:text-xs" title="Approve" onclick="return confirm('Approve this testimonial?')">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                            <form method="post" action="../controllers/testimonialController.php" style="display:inline;">
                                                <input type="hidden" name="testimonial_id" value="<?php echo $a['testimonial_id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_testimonial" class="px-2 py-1 rounded bg-neon-pink/20 text-neon-pink hover:bg-neon-pink/40 transition-colors text-xxs sm:text-xs" title="Reject" onclick="return confirm('Reject this testimonial?')">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" action="../controllers/testimonialController.php" style="display:inline;">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $a['testimonial_id']; ?>">
                                            <button type="submit" name="delete_testimonial" class="px-2 py-1 rounded bg-gray-600/30 text-gray-400 hover:bg-red-600/40 hover:text-white transition-colors text-xxs sm:text-xs" title="Delete" onclick="return confirm('Delete this testimonial?')">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex flex-col sm:flex-row items-center justify-between mt-3 gap-2 sm:gap-0">
                    <div class="text-xxs sm:text-xs text-gray-400 text-center sm:text-left">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">12</span> results
                    </div>
                    <div class="flex space-x-1">
                        <button class="px-2 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors text-xxs sm:text-xs">
                            Previous
                        </button>
                        <button class="px-2 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors text-xxs sm:text-xs">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/toggleHandle.js"></script>
    <script src="../assets/js/modalFunction.js"></script>

    <script>
        // Responsive table adjustments
        function adjustTableForMobile() {
            if (window.innerWidth < 640) {
                // Additional mobile-specific adjustments if needed
            }
        }

        // Run on load and resize
        window.addEventListener('load', adjustTableForMobile);
        window.addEventListener('resize', adjustTableForMobile);
    </script>
</body>
</html>