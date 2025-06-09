<?php
include_once '../components/connection.php';

// If editing, fetch story data
$story_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT s.*, u.username, u.role 
              FROM stories s 
              LEFT JOIN users u ON s.user_id = u.user_id 
              WHERE s.story_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $story_data = mysqli_fetch_assoc($result);
    }
}
?>

<!-- Add/Edit Story Modal -->
<div id="storyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= isset($story_data) ? 'Edit Story' : 'Share Your Story' ?>
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="storyForm" action="../controllers/storiesController.php" method="POST" enctype="multipart/form-data">
            <?php if(isset($story_data)): ?>
                <input type="hidden" name="story_id" value="<?= $story_data['story_id'] ?>">
            <?php endif; ?>
            
            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm text-gray-400 mb-1">Title</label>
                    <input type="text" id="title" name="title" required
                           class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                           value="<?= $story_data['title'] ?? '' ?>"
                           placeholder="Enter a compelling title for your story">
                </div>
                
                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm text-gray-400 mb-1">Story Content</label>
                    <textarea id="description" name="description" required rows="6"
                              class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                              placeholder="Share your experience, achievements, or journey..."><?= $story_data['description'] ?? '' ?></textarea>
                </div>
                
                <!-- Media Type -->
                <div>
                    <label for="media_type" class="block text-sm text-gray-400 mb-1">Media Type</label>
                    <select id="media_type" name="media_type" required
                            class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue">
                        <option value="image" <?= (isset($story_data) && $story_data['media_type'] == 'image') ? 'selected' : '' ?>>Image</option>
                        <option value="video" <?= (isset($story_data) && $story_data['media_type'] == 'video') ? 'selected' : '' ?>>Video</option>
                    </select>
                </div>
                
                <!-- Media Upload -->
                <div>
                    <label for="media" class="block text-sm text-gray-400 mb-1">Media Upload</label>
                    <input type="file" id="media" name="media" accept="image/*,video/*"
                           class="w-full bg-dark-800 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <?php if(isset($story_data) && !empty($story_data['media'])): ?>
                        <p class="text-xs text-gray-400 mt-1">Current: <?= $story_data['media'] ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-1">
                        Max file size: 5MB for images, 50MB for videos. Supported formats: JPG, PNG, GIF, MP4, MOV
                    </p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="<?= isset($story_data) ? 'update_story' : 'create_story' ?>" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    <?= isset($story_data) ? 'Update Story' : 'Share Story' ?>
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
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this story? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" action="../controllers/storiesController.php">
            <input type="hidden" name="story_id" id="deleteStoryId">
            <input type="hidden" name="delete_story" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Delete Story
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Story Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Story Details
            </h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <?php if(isset($story_data)): ?>
        <div class="space-y-6">
            <!-- Story Header -->
            <div>
                <h4 class="text-lg font-semibold text-white mb-2">
                    <?= htmlspecialchars($story_data['title']) ?>
                </h4>
                <div class="flex items-center space-x-4 text-sm text-gray-400">
                    <span>Posted by <?= htmlspecialchars($story_data['username']) ?></span>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                        <?= htmlspecialchars($story_data['role']) ?>
                    </span>
                    <span><?= date('F d, Y', strtotime($story_data['date_created'])) ?></span>
                </div>
            </div>

            <!-- Media Display -->
            <?php if(!empty($story_data['media'])): ?>
            <div class="rounded-lg overflow-hidden">
                <?php if($story_data['media_type'] == 'image'): ?>
                    <img src="../assets/uploads/stories/<?= htmlspecialchars($story_data['media']) ?>" 
                         alt="Story Media" class="w-full h-auto">
                <?php else: ?>
                    <video controls class="w-full">
                        <source src="../assets/uploads/stories/<?= htmlspecialchars($story_data['media']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Story Content -->
            <div class="prose prose-invert max-w-none">
                <?= nl2br(htmlspecialchars($story_data['description'])) ?>
            </div>

            <!-- Story Status -->
            <div class="pt-4 border-t border-gray-700">
                <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $story_data['status'] == 'approved' ? 'bg-neon-green/20 text-neon-green' : 'bg-yellow-500/20 text-yellow-500' ?>">
                    <?= ucfirst($story_data['status']) ?>
                </span>
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