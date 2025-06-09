<?php include '../components/adminHeader.php'; ?>

<!-- Fetch admin details from the view -->
<?php
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM view_user_details WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Get full name
$fullname = $admin['firstname'] . ' ' . $admin['middlename'] . ' ' . $admin['lastname'];
?>

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
                        <img src="<?php echo !empty($admin['image']) ? '../assets/uploads/alumni/' . htmlspecialchars($admin['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '&background=random'; ?>" 
                             alt="Profile" 
                             class="w-32 h-32 rounded-full object-cover ring-4 ring-neon-blue/20">
                        <span class="absolute bottom-2 right-2 w-4 h-4 bg-neon-green border-2 border-darker rounded-full"></span>
                    </div>
                    <h2 class="text-xl font-bold text-white mt-4"><?php echo htmlspecialchars($fullname); ?></h2>
                    <p class="text-gray-400"><?php echo htmlspecialchars($admin['role']); ?></p>
                    <div class="mt-4 flex space-x-3">
                        <button onclick="openEditProfileModal()" class="px-4 py-2 bg-neon-blue/20 text-neon-blue rounded-lg hover:bg-neon-blue/30 transition-colors">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                            </button>
                        <button onclick="openChangePasswordModal()" class="px-4 py-2 bg-neon-purple/20 text-neon-purple rounded-lg hover:bg-neon-purple/30 transition-colors">
                            <i class="fas fa-key mr-2"></i>Change Password
                            </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="lg:col-span-2">
            <div class="glass rounded-xl p-6 border border-lighter/10">
                <h3 class="text-lg font-semibold text-neon-blue mb-4">Account Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm">Username</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['username']); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Email</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['email']); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Role</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['role']); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Status</label>
                        <div class="text-white font-medium">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $admin['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400'; ?>">
                            <?php echo $admin['status'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Date Created</label>
                        <div class="text-white font-medium">
                            <?php echo date('F d, Y h:i A', strtotime($admin['date_created'])); ?>
                        </div>
                    </div>
                </div>
                        </div>
                    </div>
                </div>

    <!-- Personal Information -->
    <div class="glass rounded-xl p-6 border border-lighter/10 mb-6">
        <h3 class="text-lg font-semibold text-neon-blue mb-4">Personal Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm">Full Name</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($fullname); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Gender</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['gender']); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Contact</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['contact']); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Address</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['address']); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">City</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['city']); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Country</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($admin['country']); ?></div>
                    </div>
                </div>
            </div>

    <!-- Academic Information -->
    <div class="glass rounded-xl p-6 border border-lighter/10">
        <h3 class="text-lg font-semibold text-neon-blue mb-4">Academic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-400 text-sm">Course</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['course_name']); ?></div>
            </div>
            <div>
                <label class="block text-gray-400 text-sm">Batch</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['batch']); ?></div>
            </div>
            <div>
                <label class="block text-gray-400 text-sm">Graduation Year</label>
                <div class="text-white font-medium"><?php echo htmlspecialchars($admin['graduation_year']); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="relative glass rounded-xl p-6 w-full max-w-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                    Edit Profile
                </h3>
                <button onclick="closeEditProfileModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="../controllers/adminController.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="update_admin_profile" value="1">
                
                <!-- Profile Image -->
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        <img src="<?php echo !empty($admin['image']) ? '../assets/uploads/alumni/' . htmlspecialchars($admin['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '&background=random'; ?>" 
                             alt="Profile" 
                             class="w-24 h-24 rounded-full object-cover ring-4 ring-neon-blue/20">
                        <label for="profile_image" class="absolute bottom-0 right-0 bg-neon-blue text-white p-2 rounded-full cursor-pointer hover:bg-neon-blue/80 transition-colors">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="profile_image" name="profile_image" class="hidden" accept="image/*">
                    </div>
                    <div>
                        <h4 class="text-white font-medium">Profile Picture</h4>
                        <p class="text-gray-400 text-sm">Upload a new profile picture</p>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">First Name</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($admin['firstname']); ?>" 
                               class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Middle Name</label>
                        <input type="text" name="middlename" value="<?php echo htmlspecialchars($admin['middlename']); ?>" 
                               class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Last Name</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($admin['lastname']); ?>" 
                               class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Gender</label>
                        <select name="gender" class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                            <option value="Male" <?php echo $admin['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $admin['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Contact Number</label>
                        <input type="tel" name="contact" value="<?php echo htmlspecialchars($admin['contact']); ?>" 
                               class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" 
                               class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    </div>
                </div>

                <!-- Address Information -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Address</label>
                        <textarea name="address" rows="2" 
                                  class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue"><?php echo htmlspecialchars($admin['address']); ?></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 text-sm mb-1">City</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($admin['city']); ?>" 
                                   class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-1">Country</label>
                            <input type="text" name="country" value="<?php echo htmlspecialchars($admin['country']); ?>" 
                                   class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditProfileModal()" 
                            class="px-4 py-2 text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-neon-blue text-white rounded-lg hover:bg-neon-blue/80 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="relative glass rounded-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                    Change Password
                </h3>
                <button onclick="closeChangePasswordModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="../controllers/adminController.php" method="POST" class="space-y-4">
                <input type="hidden" name="change_password" value="1">
                
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                </div>
                
                <div>
                    <label class="block text-gray-400 text-sm mb-1">New Password</label>
                    <input type="password" name="new_password" required
                           class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                </div>
                
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required
                           class="w-full bg-white/10 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeChangePasswordModal()" 
                            class="px-4 py-2 text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-neon-blue text-white rounded-lg hover:bg-neon-blue/80 transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="../assets/js/toggleHandle.js"></script>
    <script src="../assets/js/modalFunction.js"></script>

<script>
// Modal Functions
function openEditProfileModal() {
    document.getElementById('editProfileModal').classList.remove('hidden');
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').classList.add('hidden');
}

function openChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('hidden');
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('hidden');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const editProfileModal = document.getElementById('editProfileModal');
    const changePasswordModal = document.getElementById('changePasswordModal');
    
    if (event.target === editProfileModal) {
        closeEditProfileModal();
    }
    if (event.target === changePasswordModal) {
        closeChangePasswordModal();
    }
}

// Preview profile image before upload
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.relative img').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>