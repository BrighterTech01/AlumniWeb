<?php
include_once '../components/connection.php';

// If viewing, fetch user data
$user_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM view_user_details WHERE user_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
}
?>

<!-- View User Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                User Details
            </h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <?php if(isset($user_data)): ?>
        <div class="space-y-6">
            <!-- Profile Section -->
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <img class="h-24 w-24 rounded-full object-cover" 
                         src="<?= !empty($user_data['image']) && $user_data['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($user_data['image']) : '../assets/uploads/alumni/default.png' ?>" 
                         alt="Profile">
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white">
                        <?= htmlspecialchars($user_data['firstname'] . ' ' . $user_data['middlename'] . ' ' . $user_data['lastname']) ?>
                    </h4>
                    <p class="text-gray-400"><?= htmlspecialchars($user_data['email']) ?></p>
                    <p class="text-gray-400">
                        <?= htmlspecialchars($user_data['username']) ?> 
                        <span class="px-2 py-1 ml-2 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                            <?= htmlspecialchars($user_data['role']) ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-neon-blue">Personal Information</h4>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Gender</label>
                        <p class="text-white"><?= htmlspecialchars($user_data['gender']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Contact Number</label>
                        <p class="text-white"><?= htmlspecialchars($user_data['contact']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Address</label>
                        <p class="text-white"><?= htmlspecialchars($user_data['address']) ?></p>
                        <p class="text-gray-400"><?= htmlspecialchars($user_data['city'] . ', ' . $user_data['country']) ?></p>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-neon-blue">Academic Information</h4>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Course</label>
                        <p class="text-white"><?= htmlspecialchars($user_data['course_name'] ?? 'Not specified') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Batch</label>
                        <p class="text-white"><?= htmlspecialchars($user_data['batch']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Graduation Year</label>
                        <p class="text-white"><?= htmlspecialchars($user_data['graduation_year']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Status</label>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $user_data['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400' ?>">
                            <?= $user_data['status'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-neon-blue">Account Information</h4>
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Username</label>
                    <p class="text-white"><?= htmlspecialchars($user_data['username']) ?></p>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Role</label>
                    <p class="text-white"><?= htmlspecialchars($user_data['role']) ?></p>
                </div>
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <p class="text-white"><?= htmlspecialchars($user_data['email']) ?></p>
                </div>
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Date Created</label>
                    <p class="text-white"><?= date('F d, Y', strtotime($user_data['date_created'])) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-6 flex justify-end">
            <button onclick="closeViewModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                Close
            </button>
        </div>
    </div>
</div> 