<?php
session_start();
require_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Alumni') {
    header('Location: login.php');
    exit();
}

// Fetch stories with user details using the view
$query = "SELECT s.*, v.username, v.role, v.image 
          FROM stories s 
          JOIN view_user_details v ON s.user_id = v.user_id 
          ORDER BY s.created_at DESC";
$stories = mysqli_query($conn, $query);

// Handle search
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
if (!empty($search_query)) {
    $query = "SELECT s.*, v.username, v.role, v.image 
              FROM stories s 
              JOIN view_user_details v ON s.user_id = v.user_id 
              WHERE s.title LIKE '%$search_query%' 
              OR s.description LIKE '%$search_query%'
              ORDER BY s.created_at DESC";
    $stories = mysqli_query($conn, $query);
}

include 'components/userHeader.php';
?>

<!-- Main content starts here -->
<div class="min-h-screen bg-gray-900">
    <div class="max-w-2xl mx-auto p-4 sm:p-6 mt-16">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Alumni Stories</h1>
            <p class="text-gray-400 text-sm">Share your journey, inspire others, and connect with fellow alumni through meaningful stories.</p>
        </div>

      
        <!-- Search Section -->
        <div class="mb-6">
            <form action="" method="GET">
                <div class="relative">
                    <input type="text" name="search" 
                           class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                           placeholder="Search stories..." 
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </form>
        </div>

        <!-- Stories Feed -->
        <div class="space-y-6">
            <?php if ($stories && mysqli_num_rows($stories) > 0): ?>
                <?php while ($story = mysqli_fetch_assoc($stories)): ?>
                    <div class="bg-darker/50 backdrop-blur-md rounded-xl border border-lighter/10 overflow-hidden hover:border-primary/30 transition-all duration-300">
                        <!-- Story Header -->
                        <div class="p-4 border-b border-lighter/10">
                            <div class="flex items-center space-x-3">
                                <?php 
                                $avatarName = htmlspecialchars($story['username']);
                                $imagePath = !empty($story['image']) ? "assets/uploads/alumni/" . $story['image'] : null;
                                $avatarUrl = $imagePath && file_exists($imagePath) ? $imagePath : "https://ui-avatars.com/api/?name=" . urlencode($avatarName) . "&background=random&color=fff";
                                ?>
                                <img src="<?php echo $avatarUrl; ?>" 
                                     class="w-10 h-10 rounded-full object-cover" 
                                     alt="<?php echo $avatarName; ?>'s profile"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($avatarName); ?>&background=random&color=fff'">
                                <div>
                                    <p class="text-white font-medium"><?php echo htmlspecialchars($story['username']); ?></p>
                                    <p class="text-sm text-gray-400">
                                        <?php echo htmlspecialchars($story['role']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Story Content -->
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-white mb-3"><?php echo htmlspecialchars($story['title']); ?></h3>
                            <p class="text-gray-400 mb-4">
                                <?php 
                                $description = strip_tags($story['description']);
                                echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                                ?>
                            </p>
                        </div>

                        <!-- Story Media -->
                        <?php if ($story['vimage']): ?>
                            <div class="relative bg-black">
                                <?php if ($story['media_type'] === 'image'): ?>
                                    <img src="assets/uploads/stories/<?php echo htmlspecialchars($story['vimage']); ?>" 
                                         class="w-full h-[400px] object-contain" alt="Story Image">
                                <?php else: ?>
                                    <video class="w-full h-[400px] object-contain" controls>
                                        <source src="assets/uploads/stories/<?php echo htmlspecialchars($story['vimage']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php endif; ?>
                                <span class="absolute top-4 right-4 px-3 py-1 bg-darker/80 backdrop-blur-sm rounded-full text-xs font-medium text-white">
                                    <i class="fas fa-<?php echo $story['media_type'] === 'image' ? 'image' : 'video'; ?> mr-1"></i>
                                    <?php echo ucfirst($story['media_type']); ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Story Footer -->
                        <div class="p-4 border-t border-lighter/10">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-400">
                                    <?php echo date('M d, Y', strtotime($story['created_at'])); ?>
                                </span>
                                <a href="javascript:void(0)" 
                                   onclick="openStoryViewModal(<?php echo $story['story_id']; ?>)"
                                   class="px-4 py-2 bg-primary/20 text-primary rounded-lg hover:bg-primary/30 transition-colors">
                                    Read More
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-darker/50 backdrop-blur-md rounded-xl border border-lighter/10">
                    <i class="fas fa-book-open text-5xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">No Stories Found</h3>
                    <p class="text-gray-400">Be the first to share your story!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Story View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
        </div>
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block w-full max-w-3xl p-6 my-8 text-left align-middle transition-all transform bg-darker shadow-xl rounded-2xl border border-lighter/10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-white" id="storyTitle"></h3>
                <button onclick="closeStoryViewModal()" class="text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex items-center space-x-3 mb-4">
                <img id="storyUserImage" class="w-10 h-10 rounded-full object-cover" alt="User profile">
                <div>
                    <p id="storyUsername" class="text-white font-medium"></p>
                    <p id="storyUserRole" class="text-sm text-gray-400"></p>
                </div>
            </div>
            <div id="storyMedia" class="mb-4"></div>
            <div class="prose prose-invert max-w-none">
                <p id="storyDescription" class="text-gray-300"></p>
            </div>
            <div class="mt-4 text-sm text-gray-400">
                <span id="storyDate"></span>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-submit form when search input changes
    document.querySelector('input[name="search"]').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });

    // Show success message in modal
    <?php if (isset($success_message)): ?>
    var shareStoryModal = new bootstrap.Modal(document.getElementById('shareStoryModal'));
    shareStoryModal.show();
    <?php endif; ?>

    // Update file input accept attribute based on media type
    document.querySelector('select[name="media_type"]').addEventListener('change', function() {
        const fileInput = document.querySelector('input[name="media"]');
        if (this.value === 'image') {
            fileInput.accept = 'image/*';
        } else {
            fileInput.accept = 'video/*';
        }
    });

function openStoryViewModal(storyId) {
    // Fetch story details
    fetch(`forms/get_story.php?id=${storyId}`)
        .then(response => response.json())
        .then(story => {
            // Update modal content
            document.getElementById('storyTitle').textContent = story.title;
            document.getElementById('storyUsername').textContent = story.username;
            document.getElementById('storyUserRole').textContent = story.role;
            document.getElementById('storyDescription').textContent = story.description;
            document.getElementById('storyDate').textContent = new Date(story.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Set user image
            const userImage = document.getElementById('storyUserImage');
            const imagePath = story.image ? `assets/uploads/alumni/${story.image}` : null;
            userImage.src = imagePath && fileExists(imagePath) ? imagePath : `https://ui-avatars.com/api/?name=${encodeURIComponent(story.username)}&background=random&color=fff`;
            userImage.alt = `${story.username}'s profile`;

            // Handle media content
            const mediaContainer = document.getElementById('storyMedia');
            if (story.vimage) {
                if (story.media_type === 'image') {
                    mediaContainer.innerHTML = `
                        <img src="assets/uploads/stories/${story.vimage}" 
                             alt="Story Image" 
                             class="w-full h-[400px] object-contain rounded-lg">
                    `;
                } else {
                    mediaContainer.innerHTML = `
                        <video class="w-full h-[400px] object-contain rounded-lg" controls>
                            <source src="assets/uploads/stories/${story.vimage}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    `;
                }
            } else {
                mediaContainer.innerHTML = '';
            }

            // Show modal
            const modal = document.getElementById('viewModal');
            modal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading story details', 'error');
        });
}

function closeStoryViewModal() {
    const modal = document.getElementById('viewModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('viewModal');
    if (modal && event.target === modal) {
        closeStoryViewModal();
    }
});

// Helper function to check if file exists
function fileExists(url) {
    const http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    try {
        http.send();
        return http.status !== 404;
    } catch (e) {
        return false;
    }
}
</script>
