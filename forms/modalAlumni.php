<?php
include_once '../components/connection.php';

// Fetch courses for dropdown
$courses = [];
$course_query = "SELECT * FROM courses ORDER BY name";
$course_result = mysqli_query($conn, $course_query);
if($course_result) {
    while($row = mysqli_fetch_assoc($course_result)) {
        $courses[] = $row;
    }
}

// If editing, fetch alumni data
$alumni_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM view_user_details WHERE alumnus_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $alumni_data = mysqli_fetch_assoc($result);
    }
}
?>

<!-- Add/Edit Alumni Modal -->
<div id="alumniModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= isset($alumni_data) ? 'Edit Alumni' : 'Add New Alumni' ?>
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="alumniForm" action="../controllers/alumniController.php" method="POST" enctype="multipart/form-data">
            <?php if(isset($alumni_data)): ?>
                <input type="hidden" name="alumnus_id" value="<?= $alumni_data['alumnus_id'] ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Personal Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-neon-blue">Personal Information</h4>
                    
                    <div>
                        <label for="firstname" class="block text-sm text-gray-400 mb-1">First Name</label>
                        <input type="text" id="firstname" name="firstname" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $alumni_data['firstname'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="middlename" class="block text-sm text-gray-400 mb-1">Middle Name</label>
                        <input type="text" id="middlename" name="middlename"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $alumni_data['middlename'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="lastname" class="block text-sm text-gray-400 mb-1">Last Name</label>
                        <input type="text" id="lastname" name="lastname" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $alumni_data['lastname'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Gender</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="Male" required
                                       class="text-neon-blue" <?= (isset($alumni_data) && $alumni_data['gender'] == 'Male') ? 'checked' : '' ?>>
                                <span class="ml-2 text-white">Male</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="Female"
                                       class="text-neon-blue" <?= (isset($alumni_data) && $alumni_data['gender'] == 'Female') ? 'checked' : '' ?>>
                                <span class="ml-2 text-white">Female</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="image" class="block text-sm text-gray-400 mb-1">Profile Image</label>
                        <input type="file" id="image" name="image" accept="image/*"
                               class="w-full bg-dark-800 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        <?php if(isset($alumni_data) && $alumni_data['image'] != 'default.png'): ?>
                            <p class="text-xs text-gray-400 mt-1">Current: <?= $alumni_data['image'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Academic Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-neon-blue">Academic Information</h4>
                    
                    <div>
                        <label for="batch" class="block text-sm text-gray-400 mb-1">Batch</label>
                        <input type="text" id="batch" name="batch" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $alumni_data['batch'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="course_id" class="block text-sm text-gray-400 mb-1">Course</label>
                        <select id="course_id" name="course_id" required
                                class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                            <option value="">Select Course</option>
                            <?php foreach($courses as $course): ?>
                                <option value="<?= $course['course_id'] ?>" 
                                    <?= (isset($alumni_data) && $alumni_data['course_id'] == $course['course_id']) ? 'selected' : '' ?>>
                                    <?= $course['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="graduation_year" class="block text-sm text-gray-400 mb-1">Graduation Year</label>
                        <input type="number" id="graduation_year" name="graduation_year" required min="1900" max="<?= date('Y') ?>"
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $alumni_data['graduation_year'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="status" class="text-neon-blue" 
                                   <?= (isset($alumni_data) && $alumni_data['status'] == 1) ? 'checked' : '' ?>>
                            <span class="ml-2 text-white">Active</span>
                        </label>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="space-y-4 md:col-span-2">
                    <h4 class="text-sm font-semibold text-neon-blue">Contact Information</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm text-gray-400 mb-1">Email</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   value="<?= $alumni_data['email'] ?? '' ?>">
                        </div>
                        
                        <div>
                            <label for="contact" class="block text-sm text-gray-400 mb-1">Contact Number</label>
                            <input type="text" id="contact" name="contact" required
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   value="<?= $alumni_data['contact'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label for="address" class="block text-sm text-gray-400 mb-1">Address</label>
                        <input type="text" id="address" name="address" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $alumni_data['address'] ?? '' ?>">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm text-gray-400 mb-1">City</label>
                            <input type="text" id="city" name="city" required
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   value="<?= $alumni_data['city'] ?? '' ?>">
                        </div>
                        
                        <div>
                            <label for="country" class="block text-sm text-gray-400 mb-1">Country</label>
                            <input type="text" id="country" name="country" required
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   value="<?= $alumni_data['country'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <!-- User Account Information -->
                <?php if(!isset($alumni_data)): ?>
                <div class="space-y-4 md:col-span-2">
                    <h4 class="text-sm font-semibold text-neon-blue">User Account Information</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="username" class="block text-sm text-gray-400 mb-1">Username</label>
                            <input type="text" id="username" name="username" required
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   pattern="[a-zA-Z0-9_]+" title="Username can only contain letters, numbers, and underscores">
                            <p class="text-xs text-gray-400 mt-1">Username can only contain letters, numbers, and underscores</p>
                        </div>
                        
                        <div>
                            <label for="role" class="block text-sm text-gray-400 mb-1">Role</label>
                            <select id="role" name="role" required
                                    class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                                <option value="Alumni">Alumni</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm text-gray-400 mb-1">Password</label>
                            <input type="password" id="password" name="password" required
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   minlength="8">
                            <p class="text-xs text-gray-400 mt-1">Password must be at least 8 characters long</p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm text-gray-400 mb-1">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   minlength="8">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="<?= isset($alumni_data) ? 'update_alumni' : 'create_alumni' ?>" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    <?= isset($alumni_data) ? 'Update Alumni' : 'Add Alumni' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-red-500">Confirm Deletion</h3>
            <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this alumni record? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" action="../controllers/alumniController.php">
            <input type="hidden" name="alumnus_id" id="deleteAlumnusId">
            <input type="hidden" name="delete_alumni" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Delete Alumni
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Alumni Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Alumni Details
            </h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <?php if(isset($alumni_data)): ?>
        <div class="space-y-6">
            <!-- Profile Section -->
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <img class="h-24 w-24 rounded-full object-cover" 
                         src="<?= !empty($alumni_data['image']) && $alumni_data['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($alumni_data['image']) : '../assets/uploads/alumni/default.png' ?>" 
                         alt="Profile">
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white">
                        <?= htmlspecialchars($alumni_data['firstname'] . ' ' . $alumni_data['middlename'] . ' ' . $alumni_data['lastname']) ?>
                    </h4>
                    <p class="text-gray-400"><?= htmlspecialchars($alumni_data['email']) ?></p>
                    <p class="text-gray-400">
                        <?= htmlspecialchars($alumni_data['username']) ?> 
                        <span class="px-2 py-1 ml-2 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                            <?= htmlspecialchars($alumni_data['role']) ?>
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
                        <p class="text-white"><?= htmlspecialchars($alumni_data['gender']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Contact Number</label>
                        <p class="text-white"><?= htmlspecialchars($alumni_data['contact']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Address</label>
                        <p class="text-white"><?= htmlspecialchars($alumni_data['address']) ?></p>
                        <p class="text-gray-400"><?= htmlspecialchars($alumni_data['city'] . ', ' . $alumni_data['country']) ?></p>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-neon-blue">Academic Information</h4>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Course</label>
                        <p class="text-white"><?= htmlspecialchars($alumni_data['course_name'] ?? 'Not specified') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Batch</label>
                        <p class="text-white"><?= htmlspecialchars($alumni_data['batch']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Graduation Year</label>
                        <p class="text-white"><?= htmlspecialchars($alumni_data['graduation_year']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Status</label>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $alumni_data['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400' ?>">
                            <?= $alumni_data['status'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-neon-blue">Account Information</h4>
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Username</label>
                    <p class="text-white"><?= htmlspecialchars($alumni_data['username']) ?></p>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Role</label>
                    <p class="text-white"><?= htmlspecialchars($alumni_data['role']) ?></p>
                </div>
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <p class="text-white"><?= htmlspecialchars($alumni_data['email']) ?></p>
                </div>
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Date Created</label>
                    <p class="text-white"><?= date('F d, Y', strtotime($alumni_data['date_created'])) ?></p>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-700">
                    <h5 class="text-sm font-semibold text-neon-blue mb-3">Account Management</h5>
                    <form action="../controllers/alumniController.php" method="POST" class="space-y-4">
                        <input type="hidden" name="alumnus_id" value="<?= $alumni_data['alumnus_id'] ?>">
                        
                        <div>
                            <label for="new_username" class="block text-sm text-gray-400 mb-1">New Username</label>
                            <input type="text" id="new_username" name="new_username"
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   pattern="[a-zA-Z0-9_]+" title="Username can only contain letters, numbers, and underscores">
                        </div>

                        <div>
                            <label for="new_role" class="block text-sm text-gray-400 mb-1">New Role</label>
                            <select id="new_role" name="new_role"
                                    class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                                <option value="Alumni" <?= $alumni_data['role'] == 'Alumni' ? 'selected' : '' ?>>Alumni</option>
                                <option value="Admin" <?= $alumni_data['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm text-gray-400 mb-1">New Password</label>
                            <input type="password" id="new_password" name="new_password"
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   minlength="8">
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep current password</p>
                        </div>

                        <div>
                            <label for="confirm_new_password" class="block text-sm text-gray-400 mb-1">Confirm New Password</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password"
                                   class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                                   minlength="8">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_account" 
                                    class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                                Update Account
                            </button>
                        </div>
                    </form>
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