<?php
session_start();
include_once '../components/connection.php';

// Initialize variables
$alumnus = null;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'create';
$title = 'Add New Alumni';
$buttonText = 'Add Alumni';

// Check if we're editing/viewing an existing alumni
if (isset($_GET['id'])) {
    $alumnusId = $_GET['id'];
    $query = "SELECT u.*, c.name as course_name, c.course_id 
              FROM users u 
              LEFT JOIN courses c ON u.course_id = c.course_id 
              WHERE u.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $alumnusId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $alumnus = $result->fetch_assoc();
        $title = $mode === 'view' ? 'Alumni Details' : 'Edit Alumni';
        $buttonText = 'Update Alumni';
    }
}

// Fetch courses for dropdown
$courses = [];
$course_query = "SELECT * FROM courses ORDER BY name";
$course_result = $conn->query($course_query);
if($course_result) {
    while($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<!-- Main Alumni Modal -->
<div id="alumniModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-dark rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-lighter/10">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= htmlspecialchars($title) ?>
            </h3>
            <button onclick="closeAlumniModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <?php if ($mode === 'view' && $alumnus): ?>
            <!-- View Mode Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="h-20 w-20 rounded-full overflow-hidden border-2 border-primary">
                            <img src="<?= !empty($alumnus['image']) && $alumnus['image'] != 'default.png' ? '../assets/uploads/alumni/' . htmlspecialchars($alumnus['image']) : '../assets/uploads/alumni/default.png' ?>" 
                                 alt="Profile" class="h-full w-full object-cover" onerror="this.src='../assets/uploads/alumni/default.png'">
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold"><?= htmlspecialchars($alumnus['firstname'] . ' ' . $alumnus['lastname']) ?></h4>
                            <p class="text-sm text-gray-400"><?= htmlspecialchars($alumnus['email']) ?></p>
                            <p class="text-xs text-gray-500 mt-1">ID: <?= htmlspecialchars($alumnus['user_id']) ?></p>
                            <p class="text-xs text-gray-500">Role: <?= htmlspecialchars($alumnus['role']) ?></p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Gender</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['gender'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Course</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['course_name']) ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Batch</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['batch']) ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Graduation Year</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['graduation_year']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-span-1">
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Contact Number</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['contact'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">District</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['district'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">City</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['city'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Province</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['province'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Country</label>
                            <p class="text-sm"><?= htmlspecialchars($alumnus['country'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 block mb-1">Status</label>
                            <p class="text-sm"><span class="px-2 py-1 rounded-full text-xs font-semibold <?= $alumnus['status'] ? 'bg-neon-green/20 text-neon-green' : 'bg-gray-600/30 text-gray-400' ?>">
                                <?= $alumnus['status'] ? 'Active' : 'Inactive' ?>
                            </span></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-lighter/10">
                <h4 class="text-sm font-semibold mb-3">Account Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Name</label>
                        <p class="text-sm"><?= htmlspecialchars($alumnus['firstname'] . ' ' . $alumnus['lastname']) ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Date Created</label>
                        <p class="text-sm"><?= date('M d, Y h:i A', strtotime($alumnus['date_created'])) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
               
              
                <button onclick="closeAlumniModal()" class="px-4 py-2 rounded-lg bg-light/20 hover:bg-light/30 transition-colors text-sm">
                    Close
                </button>
            </div>
            
        <?php else: ?>
            <!-- Create/Edit Mode Form -->
            <form id="alumniForm" action="../controllers/alumniController.php" method="POST" enctype="multipart/form-data">
                <?php if(isset($alumnus)): ?>
                    <input type="hidden" name="user_id" value="<?= $alumnus['user_id'] ?? '' ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-primary">Personal Information</h4>
                        
                        <div>
                            <label for="firstname" class="text-xs text-gray-400 block mb-1">First Name *</label>
                            <input type="text" id="firstname" name="firstname" value="<?= $alumnus['firstname'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                        </div>
                        
                        <div>
                            <label for="middlename" class="text-xs text-gray-400 block mb-1">Middle Name</label>
                            <input type="text" id="middlename" name="middlename" value="<?= $alumnus['middlename'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label for="lastname" class="text-xs text-gray-400 block mb-1">Last Name *</label>
                            <input type="text" id="lastname" name="lastname" value="<?= $alumnus['lastname'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                        </div>
                        
                        <div>
                            <label for="gender" class="text-xs text-gray-400 block mb-1">Gender *</label>
                            <select id="gender" name="gender" class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?= ($alumnus['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($alumnus['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= ($alumnus['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="email" class="text-xs text-gray-400 block mb-1">Email *</label>
                            <input type="email" id="email" name="email" value="<?= $alumnus['email'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                        </div>
                        
                        <div>
                            <label for="contact" class="text-xs text-gray-400 block mb-1">Contact Number</label>
                            <input type="tel" id="contact" name="contact" value="<?= $alumnus['contact'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label for="image" class="text-xs text-gray-400 block mb-1">Profile Image</label>
                            <input type="file" id="image" name="image" accept="image/*" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-light/20 file:text-gray-300 hover:file:bg-light/30">
                            <?php if (isset($alumnus) && !empty($alumnus['image'])): ?>
                                <p class="text-xs text-gray-500 mt-1">Current: <?= htmlspecialchars($alumnus['image']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Address Information -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-primary">Address Information</h4>
                        
                        <div>
                            <label for="district" class="text-xs text-gray-400 block mb-1">District</label>
                            <input type="text" id="district" name="district" value="<?= $alumnus['district'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label for="city" class="text-xs text-gray-400 block mb-1">City</label>
                            <input type="text" id="city" name="city" value="<?= $alumnus['city'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label for="province" class="text-xs text-gray-400 block mb-1">Province</label>
                            <input type="text" id="province" name="province" value="<?= $alumnus['province'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label for="country" class="text-xs text-gray-400 block mb-1">Country</label>
                            <input type="text" id="country" name="country" value="<?= $alumnus['country'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <!-- Academic Information -->
                        <h4 class="text-sm font-semibold text-primary pt-2">Academic Information</h4>
                        
                        <div>
                            <label for="course_id" class="text-xs text-gray-400 block mb-1">Course *</label>
                            <select id="course_id" name="course_id" class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['course_id'] ?>" <?= ($alumnus['course_id'] ?? '') == $course['course_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($course['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="batch" class="text-xs text-gray-400 block mb-1">Batch *</label>
                            <input type="text" id="batch" name="batch" value="<?= $alumnus['batch'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                        </div>
                        
                        <div>
                            <label for="graduation_year" class="text-xs text-gray-400 block mb-1">Graduation Year *</label>
                            <input type="number" id="graduation_year" name="graduation_year" min="1900" max="2099" step="1" value="<?= $alumnus['graduation_year'] ?? '' ?>" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password Section (only for edit mode) -->
                <?php if (isset($alumnus)): ?>
                <div class="space-y-4 mb-6 border-t border-lighter/10 pt-4">
                    <h4 class="text-sm font-semibold text-primary">Change Password</h4>
                    <p class="text-xs text-gray-500 mb-3">Leave blank if you don't want to change the password.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="text-xs text-gray-400 block mb-1">New Password</label>
                            <input type="password" id="new_password" name="new_password" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                                   placeholder="Enter new password">
                        </div>
                        
                        <div>
                            <label for="confirm_new_password" class="text-xs text-gray-400 block mb-1">Confirm New Password</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                                   placeholder="Confirm new password">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Status and Role -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="status" class="text-xs text-gray-400 block mb-1">Status *</label>
                        <select id="status" name="status" class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                            <option value="1" <?= ($alumnus['status'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($alumnus['status'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <?php if (!isset($alumnus)): ?>
                    <div>
                        <label for="role" class="text-xs text-gray-400 block mb-1">Role *</label>
                        <select id="role" name="role" class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                            <option value="Alumni" selected>Alumni</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- User Account Section (only for new alumni) -->
                <?php if (!isset($alumnus)): ?>
                <div class="space-y-4 mb-6 border-t border-lighter/10 pt-4">
                    <h4 class="text-sm font-semibold text-primary">Account Information</h4>
                    
                    <div>
                        <label for="username" class="text-xs text-gray-400 block mb-1">Username *</label>
                        <input type="text" id="username" name="username" 
                               class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="text-xs text-gray-400 block mb-1">Password *</label>
                            <input type="password" id="password" name="password" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="text-xs text-gray-400 block mb-1">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="w-full bg-light/10 border border-lighter/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" required>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-500">Note: The user will be required to change their password upon first login.</p>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAlumniModal()" class="px-4 py-2 rounded-lg bg-light/20 hover:bg-light/30 transition-colors text-sm">
                        Cancel
                    </button>
                  
                    <button type="submit" name="<?= isset($alumnus) ? 'update_alumni' : 'create_alumni' ?>" class="px-4 py-2 rounded-lg bg-primary hover:bg-primary-dark transition-colors text-sm">
                        <?= $buttonText ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAlumniModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-dark rounded-xl p-6 w-full max-w-md border border-lighter/10">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-red-500">Confirm Deletion</h3>
            <button onclick="closeDeleteAlumniModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this alumni record? This action cannot be undone.</p>
        
        <form id="deleteAlumniForm" method="POST" action="../actions/manage_alumni.php">
            <input type="hidden" name="alumnus_id" id="deleteAlumnusId" value="">
            <input type="hidden" name="delete_alumni" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteAlumniModal()" class="px-4 py-2 rounded-lg border border-lighter/10 hover:bg-light/10 transition-colors text-sm">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 transition-colors text-sm">
                    Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show the modal immediately after loading
document.getElementById("alumniModal").classList.remove("hidden");

// Enhanced form validation
document.getElementById('alumniForm')?.addEventListener('submit', function(e) {
    // Password validation (for create form)
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (password && confirmPassword) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            showError('Passwords do not match!');
            password.focus();
            return false;
        }
        
        if (password.value.length < 8) {
            e.preventDefault();
            showError('Password must be at least 8 characters long!');
            password.focus();
            return false;
        }
    }
    
    // Password change validation (for edit form)
    const newPassword = document.getElementById('new_password');
    const confirmNewPassword = document.getElementById('confirm_new_password');
    
    if (newPassword && confirmNewPassword) {
        // If either field is filled, both must be filled
        if ((newPassword.value && !confirmNewPassword.value) || (!newPassword.value && confirmNewPassword.value)) {
            e.preventDefault();
            showError('Please fill in both password fields to change password!');
            if (!newPassword.value) newPassword.focus();
            else confirmNewPassword.focus();
            return false;
        }
        
        // If both are filled, validate them
        if (newPassword.value && confirmNewPassword.value) {
            if (newPassword.value !== confirmNewPassword.value) {
                e.preventDefault();
                showError('New passwords do not match!');
                newPassword.focus();
                return false;
            }
            
            if (newPassword.value.length < 8) {
                e.preventDefault();
                showError('New password must be at least 8 characters long!');
                newPassword.focus();
                return false;
            }
        }
    }
    
    // Email validation
    const email = document.getElementById('email');
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        e.preventDefault();
        showError('Please enter a valid email address!');
        email.focus();
        return false;
    }
    
    // Required fields validation
    const requiredFields = document.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('border-red-500');
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        showError('Please fill in all required fields!');
        return false;
    }
    
    return true;
});

// Delete modal functions
function showDeleteModal(alumnusId) {
    document.getElementById('deleteAlumnusId').value = alumnusId;
    document.getElementById('deleteAlumniModal').classList.remove('hidden');
}

function closeDeleteAlumniModal() {
    document.getElementById('deleteAlumniModal').classList.add('hidden');
}

function closeAlumniModal() {
    document.getElementById('alumniModal').classList.add('hidden');
    // Optional: Remove the modal from DOM after animation
    setTimeout(() => {
        document.getElementById('alumniModal').remove();
    }, 300);
}

function showError(message) {
    // You can replace this with a more sophisticated notification system
    alert(message);
}

// Real-time username availability check
document.getElementById('username')?.addEventListener('blur', function() {
    const username = this.value;
    if (username.length < 3) return;
    
    fetch(`../actions/check_username.php?username=${encodeURIComponent(username)}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                this.classList.add('border-red-500');
                showError('Username already taken!');
            } else {
                this.classList.remove('border-red-500');
            }
        });
});

// Real-time email availability check
document.getElementById('email')?.addEventListener('blur', function() {
    const email = this.value;
    if (!email.includes('@')) return;
    
    fetch(`../actions/check_email.php?email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                this.classList.add('border-red-500');
                showError('Email already registered!');
            } else {
                this.classList.remove('border-red-500');
            }
        });
});
</script>