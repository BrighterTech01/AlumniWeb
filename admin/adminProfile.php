<?php include '../components/adminHeader.php'; ?>

<!-- Fetch admin details from the view -->
<?php
$user_id = $_SESSION['user_id'];
$query = "SELECT u.*, c.name AS course_name 
          FROM users u 
          LEFT JOIN courses c ON u.course_id = c.course_id 
          WHERE u.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Get full name
$fullname = trim($admin['firstname'] . ' ' . ($admin['middlename'] ? $admin['middlename'] . ' ' : '') . $admin['lastname']);
?>

<!-- Success/Error Messages -->
<?php if(isset($_SESSION['success'])): ?>
    <div class="fixed top-20 right-4 z-50">
        <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const message = document.querySelector('.fixed.top-20.right-4');
            if(message) message.remove();
        }, 3000);
    </script>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="fixed top-20 right-4 z-50">
        <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const message = document.querySelector('.fixed.top-20.right-4');
            if(message) message.remove();
        }, 3000);
    </script>
<?php endif; ?>

<!-- Profile Content -->
<div class="pt-20 px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">My Profile</h1>
            <p class="text-gray-400 text-sm sm:text-base">View and manage your account information</p>
        </div>
    </div>

    <!-- Profile Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="glass rounded-xl p-6 border border-lighter/10">
                <div class="flex flex-col items-center">
                    <div class="relative">
                        <?php 
                        $profile_img = (!empty($admin['image']) && $admin['image'] != 'default.png') 
                            ? '../assets/uploads/alumni/' . htmlspecialchars($admin['image']) 
                            : '../assets/uploads/alumni/default.png';
                        ?>
                        <img src="<?php echo $profile_img; ?>" 
                             alt="Profile" 
                             class="w-32 h-32 rounded-full object-cover ring-4 ring-neon-blue/20"
                             onerror="this.src='../assets/uploads/alumni/default.png'">
                        <span class="absolute bottom-2 right-2 w-4 h-4 bg-neon-green border-2 border-darker rounded-full"></span>
                    </div>
                    <h2 class="text-xl font-bold text-white mt-4"><?php echo htmlspecialchars($fullname); ?></h2>
                    <p class="text-gray-400"><?php echo htmlspecialchars($admin['role'] ?? ''); ?></p>
                    <div class="mt-4 flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <button onclick="openEditProfileModal()" class="px-4 py-2 bg-neon-blue/20 text-neon-blue rounded-lg hover:bg-neon-blue/30 transition-colors flex items-center justify-center">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </button>
                        <button onclick="openChangePasswordModal()" class="px-4 py-2 bg-neon-purple/20 text-neon-purple rounded-lg hover:bg-neon-purple/30 transition-colors flex items-center justify-center">
                            <i class="fas fa-key mr-2"></i>Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="lg:col-span-2">
            <div class="glass rounded-xl p-6 border border-lighter/10">
                <h3 class="text-lg font-semibold text-neon-blue mb-4">
                    <i class="fas fa-user-circle mr-2"></i>Account Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 bg-light/5 rounded-lg">
                        <label class="block text-gray-400 text-xs mb-1">Email</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['email'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="p-3 bg-light/5 rounded-lg">
                        <label class="block text-gray-400 text-xs mb-1">Role</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['role'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="p-3 bg-light/5 rounded-lg">
                        <label class="block text-gray-400 text-xs mb-1">Status</label>
                        <div class="text-white font-medium">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $admin['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400'; ?>">
                                <?php echo $admin['status'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-3 bg-light/5 rounded-lg">
                        <label class="block text-gray-400 text-xs mb-1">Date Created</label>
                        <div class="text-white font-medium">
                            <?php echo isset($admin['date_created']) ? date('F d, Y h:i A', strtotime($admin['date_created'])) : 'N/A'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="glass rounded-xl p-6 border border-lighter/10 mb-6">
        <h3 class="text-lg font-semibold text-neon-blue mb-4">
            <i class="fas fa-id-card mr-2"></i>Personal Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Full Name</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($fullname); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Gender</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['gender'] ?? 'N/A'); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Contact</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['contact'] ?? 'N/A'); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">District</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['district'] ?? 'N/A'); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">City</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['city'] ?? 'N/A'); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Province</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['province'] ?? 'N/A'); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Country</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['country'] ?? 'N/A'); ?></div>
            </div>
        </div>
    </div>

    <!-- Academic Information -->
    <div class="glass rounded-xl p-6 border border-lighter/10">
        <h3 class="text-lg font-semibold text-neon-blue mb-4">
            <i class="fas fa-graduation-cap mr-2"></i>Academic Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Course</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['course_name'] ?? 'N/A'); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Batch</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['batch'] ?? 'N/A'); ?></div>
            </div>
            <div class="p-3 bg-light/5 rounded-lg">
                <label class="block text-gray-400 text-xs mb-1">Graduation Year</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['graduation_year'] ?? 'N/A'); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Edit Profile
            </h3>
            <button onclick="closeEditProfileModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form action="../controllers/adminController.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="update_admin_profile" value="1">
            
            <!-- Profile Image -->
            <div class="flex items-center space-x-6 pb-4 border-b border-lighter/10">
                <div class="relative">
                    <?php 
                    $preview_img = (!empty($admin['image']) && $admin['image'] != 'default.png') 
                        ? '../assets/uploads/alumni/' . htmlspecialchars($admin['image']) 
                        : '../assets/uploads/alumni/default.png';
                    ?>
                    <img id="profilePreview" src="<?php echo $preview_img; ?>" 
                         alt="Profile" 
                         class="w-24 h-24 rounded-full object-cover ring-4 ring-neon-blue/20"
                         onerror="this.src='../assets/uploads/alumni/default.png'">
                    <label for="profile_image" class="absolute bottom-0 right-0 bg-neon-blue text-white p-2 rounded-full cursor-pointer hover:bg-neon-blue/80 transition-colors shadow-lg">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profile_image" name="profile_image" class="hidden" accept="image/*">
                </div>
                <div>
                    <h4 class="text-white font-medium">Profile Picture</h4>
                    <p class="text-gray-400 text-sm">Upload a new profile picture (Max 5MB)</p>
                </div>
            </div>

            <!-- Personal Information -->
            <div>
                <h4 class="text-white font-medium mb-4">Personal Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">First Name *</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($admin['firstname'] ?? ''); ?>" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Middle Name</label>
                        <input type="text" name="middlename" value="<?php echo htmlspecialchars($admin['middlename'] ?? ''); ?>"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Last Name *</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($admin['lastname'] ?? ''); ?>" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Gender</label>
                        <select name="gender" class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (isset($admin['gender']) && $admin['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($admin['gender']) && $admin['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <h4 class="text-white font-medium mb-4">Contact Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Contact Number</label>
                        <input type="tel" name="contact" value="<?php echo htmlspecialchars($admin['contact'] ?? ''); ?>"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div>
                <h4 class="text-white font-medium mb-4">Address Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">District</label>
                        <input type="text" name="district" value="<?php echo htmlspecialchars($admin['district'] ?? ''); ?>"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($admin['city'] ?? ''); ?>"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Province</label>
                        <input type="text" name="province" value="<?php echo htmlspecialchars($admin['province'] ?? ''); ?>"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Country</label>
                        <input type="text" name="country" value="<?php echo htmlspecialchars($admin['country'] ?? ''); ?>"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-lighter/10">
                <button type="button" onclick="closeEditProfileModal()" 
                        class="px-6 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Change Password
            </h3>
            <button onclick="closeChangePasswordModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form action="../controllers/adminController.php" method="POST" class="space-y-4">
            <input type="hidden" name="change_password" value="1">
            
            <div>
                <label class="block text-gray-400 text-sm mb-1">Current Password *</label>
                <input type="password" name="current_password" required
                       class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
            </div>
            
            <div>
                <label class="block text-gray-400 text-sm mb-1">New Password *</label>
                <input type="password" name="new_password" required minlength="8"
                       class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                <p class="text-xs text-gray-400 mt-1">Must be at least 8 characters long</p>
            </div>
            
            <div>
                <label class="block text-gray-400 text-sm mb-1">Confirm New Password *</label>
                <input type="password" name="confirm_password" required minlength="8"
                       class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-lighter/10">
                <button type="button" onclick="closeChangePasswordModal()" 
                        class="px-6 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/toggleHandle.js"></script>
<script src="../assets/js/modalFunction.js"></script>

<script>
// Modal Functions
function openEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function openChangePasswordModal() {
    const modal = document.getElementById('changePasswordModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeChangePasswordModal() {
    const modal = document.getElementById('changePasswordModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const editProfileModal = document.getElementById('editProfileModal');
    const changePasswordModal = document.getElementById('changePasswordModal');
    
    if (editProfileModal && event.target === editProfileModal) {
        closeEditProfileModal();
    }
    if (changePasswordModal && event.target === changePasswordModal) {
        closeChangePasswordModal();
    }
});

// Preview profile image before upload
const profileImageInput = document.getElementById('profile_image');
if (profileImageInput) {
    profileImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profilePreview');
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });
}
</script>

</body>
</html>
