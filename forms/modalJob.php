<?php
session_start();
include_once '../components/connection.php';

// Get job data if editing
$job = null;
if(isset($_GET['id'])) {
    $job_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT j.*, u.username, u.role 
              FROM jobs j 
              JOIN users u ON j.user_id = u.user_id 
              WHERE j.job_id = '$job_id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $job = mysqli_fetch_assoc($result);
    }
}
?>

<!-- Add/Edit Job Modal -->
<div id="jobModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden transition-opacity duration-300">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-dark shadow-xl rounded-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">
                    <?= $job ? 'Edit Job' : 'Add New Job' ?>
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="../controllers/jobController.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if($job): ?>
                    <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Job Title</label>
                        <input type="text" name="job_title" value="<?= $job ? $job['job_title'] : '' ?>" required
                               class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Company Name</label>
                        <input type="text" name="company_name" value="<?= $job ? $job['company_name'] : '' ?>" required
                               class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Salary</label>
                        <input type="number" step="0.01" name="salary" value="<?= $job ? $job['salary'] : '' ?>" required
                               class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Job Type</label>
                        <select name="job_type" required
                                class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Select Job Type</option>
                            <option value="Full-time" <?= $job && $job['job_type'] == 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                            <option value="Part-time" <?= $job && $job['job_type'] == 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                            <option value="Contract" <?= $job && $job['job_type'] == 'Contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="Internship" <?= $job && $job['job_type'] == 'Internship' ? 'selected' : '' ?>>Internship</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Employee Status</label>
                        <input type="text" name="employee_status" value="<?= $job ? $job['employee_status'] : '' ?>" required
                               class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Location</label>
                        <input type="text" name="location" value="<?= $job ? $job['location'] : '' ?>" required
                               class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Application Deadline</label>
                        <input type="date" name="application_deadline" value="<?= $job ? $job['application_deadline'] : '' ?>" required
                               class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Application URL</label>
                        <input type="url" name="url" value="<?= $job ? $job['url'] : '' ?>"
                               class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Description</label>
                    <textarea name="description" rows="4" required
                              class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary"><?= $job ? $job['description'] : '' ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Job Image</label>
                    <input type="file" name="job_image" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90">
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" name="<?= $job ? 'update_job' : 'create_job' ?>"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <?= $job ? 'Update Job' : 'Add Job' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden transition-opacity duration-300">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-dark shadow-xl rounded-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">Delete Job</h3>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <p class="text-gray-300 mb-6">Are you sure you want to delete this job? This action cannot be undone.</p>
            
            <form action="../controllers/jobController.php" method="POST">
                <input type="hidden" name="job_id" id="deleteJobId">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" name="delete_job"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Job Modal -->
<?php if(isset($_GET['view']) && $job): ?>
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 transition-opacity duration-300">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-dark shadow-xl rounded-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">Job Details</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="relative h-48 rounded-lg overflow-hidden">
                    <img src="<?= !empty($job['job_image']) && $job['job_image'] != 'default.jpg' ? '../assets/uploads/jobs/' . htmlspecialchars($job['job_image']) : '../assets/uploads/jobs/default.jpg' ?>" 
                         alt="Job Image" class="w-full h-full object-cover">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Job Title</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($job['job_title']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Company Name</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($job['company_name']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Salary</h4>
                        <p class="mt-1 text-white">$<?= number_format($job['salary'], 2) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Job Type</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($job['job_type']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Employee Status</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($job['employee_status']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Location</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($job['location']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Application Deadline</h4>
                        <p class="mt-1 text-white"><?= date('F d, Y', strtotime($job['application_deadline'])) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Posted By</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($job['username']) ?></p>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Description</h4>
                    <p class="mt-1 text-white whitespace-pre-wrap"><?= htmlspecialchars($job['description']) ?></p>
                </div>
                
                <?php if(!empty($job['url'])): ?>
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Application URL</h4>
                    <a href="<?= htmlspecialchars($job['url']) ?>" target="_blank" 
                       class="mt-1 text-primary hover:text-primary/90">
                        <?= htmlspecialchars($job['url']) ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-end mt-6">
                    <button onclick="closeViewModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?> 