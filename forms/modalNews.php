<?php
session_start();
include_once '../components/connection.php';

// Get news data if editing
$news = null;
if(isset($_GET['id'])) {
    $news_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT n.*, u.username, u.role 
              FROM news n 
              JOIN users u ON n.user_id = u.user_id 
              WHERE n.news_id = '$news_id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $news = mysqli_fetch_assoc($result);
    }
}
?>

<!-- Add/Edit News Modal -->
<div id="newsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden transition-opacity duration-300">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-dark shadow-xl rounded-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">
                    <?= $news ? 'Edit News' : 'Add New News' ?>
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="../controllers/newsController.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if($news): ?>
                    <input type="hidden" name="news_id" value="<?= $news['news_id'] ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Title</label>
                    <input type="text" name="title" value="<?= $news ? $news['title'] : '' ?>" required
                           class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Content</label>
                    <textarea name="content" rows="4" required
                              class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary"><?= $news ? $news['content'] : '' ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Status</label>
                    <select name="status" required
                            class="mt-1 block w-full rounded-md bg-light/20 border-gray-600 text-white shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Select Status</option>
                        <option value="Published" <?= $news && $news['status'] == 'Published' ? 'selected' : '' ?>>Published</option>
                        <option value="Draft" <?= $news && $news['status'] == 'Draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Image</label>
                    <input type="file" name="image" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90">
                    <?php if($news && $news['image_url']): ?>
                        <div class="mt-2">
                            <img src="../assets/uploads/news/<?= htmlspecialchars($news['image_url']) ?>" 
                                 alt="Current Image" 
                                 class="h-20 w-20 object-cover rounded-lg">
                            <p class="mt-1 text-sm text-gray-400">Current image</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" name="<?= $news ? 'update_news' : 'create_news' ?>"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <?= $news ? 'Update News' : 'Add News' ?>
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
                <h3 class="text-lg font-medium text-white">Delete News</h3>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <p class="text-gray-300 mb-6">Are you sure you want to delete this news? This action cannot be undone.</p>
            
            <form action="../controllers/newsController.php" method="POST">
                <input type="hidden" name="news_id" id="deleteNewsId">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" name="delete_news"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View News Modal -->
<?php if(isset($_GET['view']) && $news): ?>
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 transition-opacity duration-300">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-dark shadow-xl rounded-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-white">News Details</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <?php if($news['image_url']): ?>
                <div class="relative h-48 w-full">
                    <img src="../assets/uploads/news/<?= htmlspecialchars($news['image_url']) ?>" 
                         alt="News Image" 
                         class="h-full w-full object-cover rounded-lg">
                </div>
                <?php endif; ?>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Title</h4>
                    <p class="mt-1 text-white"><?= htmlspecialchars($news['title']) ?></p>
                </div>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-400">Content</h4>
                    <p class="mt-1 text-white whitespace-pre-wrap"><?= htmlspecialchars($news['content']) ?></p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Status</h4>
                        <p class="mt-1">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $news['status'] == 'Published' ? 'bg-neon-green/20 text-neon-green' : 'bg-yellow-500/20 text-yellow-500' ?>">
                                <?= htmlspecialchars($news['status']) ?>
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Date Posted</h4>
                        <p class="mt-1 text-white"><?= date('F d, Y', strtotime($news['date_posted'])) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Posted By</h4>
                        <p class="mt-1 text-white"><?= htmlspecialchars($news['username']) ?></p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-400">Role</h4>
                        <p class="mt-1">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-purple/20 text-neon-purple">
                                <?= htmlspecialchars($news['role']) ?>
                            </span>
                        </p>
                    </div>
                </div>
                
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