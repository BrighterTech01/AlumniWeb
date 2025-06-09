<?php
// Only start session if it hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Alumni') {
    header("Location: login.php");
    exit();
}

// Fetch user details from view_user_details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM view_user_details WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Construct full name
$fullname = trim($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']);

// Calculate profile completion
$required_fields = ['firstname', 'lastname', 'gender', 'batch', 'course_id', 'contact', 'address', 'city', 'country', 'graduation_year'];
$completed_fields = 0;
foreach ($required_fields as $field) {
    if (!empty($user[$field])) {
        $completed_fields++;
    }
}
$profile_complete = ($completed_fields == count($required_fields));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .neon-glow {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-white">
    <?php include 'components/userHeader.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="max-w-6xl mx-auto">
            <!-- Profile Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Profile Image -->
                <div class="glass rounded-xl p-6 border border-lighter/10">
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            <img src="<?php echo !empty($user['image']) && $user['image'] != 'default.png' ? 'assets/uploads/alumni/' . htmlspecialchars($user['image']) : 'assets/uploads/alumni/default.png'; ?>" 
                                 alt="Profile" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-neon-blue/20">
                            <button onclick="document.getElementById('profile_image').click()" 
                                    class="absolute bottom-0 right-0 bg-neon-blue text-white p-2 rounded-full hover:bg-neon-blue/80 transition">
                                <i class="fas fa-camera"></i>
                            </button>
                            <input type="file" id="profile_image" name="profile_image" class="hidden" accept="image/*">
                        </div>
                        <h2 class="mt-4 text-xl font-bold text-white"><?php echo htmlspecialchars($fullname); ?></h2>
                        <p class="text-gray-400"><?php echo htmlspecialchars($user['role']); ?></p>
                        <div class="mt-4 w-full">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">Profile Completion</span>
                                <span class="text-white"><?php echo $profile_complete ? '100%' : 'Incomplete'; ?></span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                                <div class="bg-neon-blue h-2 rounded-full" style="width: <?php echo $profile_complete ? '100%' : '50%'; ?>"></div>
                            </div>
                        </div>
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

                <!-- Account Information -->
                <div class="lg:col-span-2">
                    <div class="glass rounded-xl p-6 border border-lighter/10">
                        <h3 class="text-lg font-semibold text-neon-blue mb-4">Account Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-400 text-sm">Username</label>
                                <div class="text-white font-medium"><?php echo htmlspecialchars($user['username'] ?? 'Not specified'); ?></div>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm">Email</label>
                                <div class="text-white font-medium"><?php echo htmlspecialchars($user['email'] ?? 'Not specified'); ?></div>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm">Role</label>
                                <div class="text-white font-medium"><?php echo htmlspecialchars($user['role'] ?? 'Not specified'); ?></div>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm">Status</label>
                                <div class="text-white font-medium">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo isset($user['status']) && $user['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400'; ?>">
                                        <?php echo isset($user['status']) && $user['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm">Date Created</label>
                                <div class="text-white font-medium">
                                    <?php echo isset($user['date_created']) ? date('F d, Y h:i A', strtotime($user['date_created'])) : 'Not specified'; ?>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm">Last Updated</label>
                                <div class="text-white font-medium">
                                    <?php echo isset($user['date_updated']) ? date('F d, Y h:i A', strtotime($user['date_updated'])) : 'Not specified'; ?>
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
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['gender'] ?? 'Not specified'); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Contact</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['contact'] ?? 'Not specified'); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Address</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['address'] ?? 'Not specified'); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">City</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['city'] ?? 'Not specified'); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Country</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['country'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="glass rounded-xl p-6 border border-lighter/10">
                <h3 class="text-lg font-semibold text-neon-blue mb-4">Academic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm">Course</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['course_name'] ?? 'Not specified'); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Batch</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['batch'] ?? 'Not specified'); ?></div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm">Graduation Year</label>
                        <div class="text-white font-medium"><?php echo htmlspecialchars($user['graduation_year'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-gray-800 rounded-xl p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Edit Profile</h3>
                <button onclick="closeEditProfileModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">First Name</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Middle Name</label>
                        <input type="text" name="middlename" value="<?php echo htmlspecialchars($user['middlename']); ?>" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Last Name</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Gender</label>
                        <select name="gender" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo $user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $user['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Contact</label>
                        <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Country</label>
                        <input type="text" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeEditProfileModal()" 
                            class="px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="update_profile" 
                            class="px-4 py-2 bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-gray-800 rounded-xl p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Change Password</h3>
                <button onclick="closeChangePasswordModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="update_password.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Current Password</label>
                    <input type="password" name="current_password" required 
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">New Password</label>
                    <input type="password" name="new_password" required 
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeChangePasswordModal()" 
                            class="px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="change_password" 
                            class="px-4 py-2 bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditProfileModal() {
            document.getElementById('editProfileModal').classList.remove('hidden');
            document.getElementById('editProfileModal').classList.add('flex');
        }

        function closeEditProfileModal() {
            document.getElementById('editProfileModal').classList.add('hidden');
            document.getElementById('editProfileModal').classList.remove('flex');
        }

        function openChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.remove('hidden');
            document.getElementById('changePasswordModal').classList.add('flex');
        }

        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.add('hidden');
            document.getElementById('changePasswordModal').classList.remove('flex');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editProfileModal');
            const passwordModal = document.getElementById('changePasswordModal');
            if (event.target === editModal) {
                closeEditProfileModal();
            }
            if (event.target === passwordModal) {
                closeChangePasswordModal();
            }
        }

        // Image preview before upload
        document.getElementById('profile_image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('img[alt="Profile"]').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>