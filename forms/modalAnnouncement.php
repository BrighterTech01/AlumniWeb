<?php
session_start();
include_once '../components/connection.php';

// Get announcement data if editing
$announcement = null;
if(isset($_GET['id'])) {
    $announce_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT a.*, u.username, u.role 
              FROM announcements a 
              JOIN users u ON a.user_id = u.user_id 
              WHERE a.announce_id = '$announce_id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $announcement = mysqli_fetch_assoc($result);
    }
}
?>

<!-- Add/Edit Announcement Modal -->
<div id="announcementModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden transition-opacity duration-300">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-dark shadow-xl rounded-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">
                    <?= $announcement ? 'Edit Announcement' : 'Add New Announcement' ?>
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="../controllers/announcementController.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if($announcement): ?>
                    <input type="hidden" name="announce_id" value="<?= $announcement['announce_id'] ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Title</label>
                    <input type="text" name="title" value="<?= $announcement ? $announcement['title'] : '' ?>" required
                           class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Content</label>
                    <textarea name="content" rows="4" required
                              class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary"><?= $announcement ? $announcement['content'] : '' ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Status</label>
                    <select name="status" required
                            class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Select Status</option>
                        <option value="Active" <?= $announcement && $announcement['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $announcement && $announcement['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Attachment</label>
                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                           class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90">
                    <?php if($announcement && $announcement['attachment_url']): ?>
                        <p class="mt-1 text-sm text-gray-400">Current attachment: <?= htmlspecialchars($announcement['attachment_url']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" name="<?= $announcement ? 'update_announcement' : 'create_announcement' ?>"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <?= $announcement ? 'Update Announcement' : 'Add Announcement' ?>
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
                <h3 class="text-lg font-medium text-white">Delete Announcement</h3>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <p class="text-gray-300 mb-6">Are you sure you want to delete this announcement? This action cannot be undone.</p>
            
            <form action="../controllers/announcementController.php" method="POST">
                <input type="hidden" name="announce_id" id="deleteAnnounceId">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" name="delete_announcement"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Announcement Modal -->
<?php if(isset($_GET['view']) && $announcement): ?>
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 transition-opacity duration-300">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-dark shadow-xl rounded-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">Announcement Details</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Title</h4>
                    <p class="mt-1 text-white"><?= htmlspecialchars($announcement['title']) ?></p>
                </div>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Content</h4>
                    <p class="mt-1 text-white whitespace-pre-wrap"><?= htmlspecialchars($announcement['content']) ?></p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Status</h4>
                        <p class="mt-1">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $announcement['status'] == 'Active' ? 'bg-neon-green/20 text-neon-green' : 'bg-red-500/20 text-red-500' ?>">
                                <?= htmlspecialchars($announcement['status']) ?>
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Date Posted</h4>
                        <p class="mt-1 text-white"><?= date('F d, Y', strtotime($announcement['date_posted'])) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Posted By</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($announcement['username']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Role</h4>
                        <p class="mt-1">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-purple/20 text-neon-purple">
                                <?= htmlspecialchars($announcement['role']) ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <?php if($announcement['attachment_url']): ?>
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Attachment</h4>
                    <a href="../assets/uploads/announcements/<?= htmlspecialchars($announcement['attachment_url']) ?>" 
                       target="_blank" 
                       class="mt-1 text-primary hover:text-primary/90">
                        <?= htmlspecialchars($announcement['attachment_url']) ?>
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