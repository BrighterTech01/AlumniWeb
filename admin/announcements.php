<?php
include_once '../components/connection.php';

// Query to fetch announcements with user details
$query = "SELECT a.*, u.username, u.role 
          FROM announcements a 
          LEFT JOIN users u ON a.user_id = u.user_id 
          ORDER BY a.date_posted DESC";
$result = mysqli_query($conn, $query);
$announcements = [];

// Check if query was successful and has results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($announcements, $row);
    }
}
?>

<?php include_once '../components/adminHeader.php'?>
<div class="pt-20 px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Manage Announcements</h1>
            <p class="text-gray-400 text-sm sm:text-base">View and manage announcements</p>
        </div>
        <button onclick="openAnnouncementModal()" class="mt-4 sm:mt-0 px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
            Add New Announcement
        </button>
    </div>

    <!-- Search and Filter Section -->
    <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <input type="search" 
                       placeholder="Search announcements..." 
                       class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
            <input type="date" 
                   class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50">
        </div>
    </div>

    <!-- Announcements Table -->
    <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover overflow-x-auto">
        <table class="min-w-full divide-y divide-lighter/20">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Content</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date Posted</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Posted By</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-lighter/10">
                <?php if (empty($announcements)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-3 text-center text-gray-400">
                        No announcements found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($announcements as $announcement): ?>
                <tr class="hover:bg-light/5 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?= $announcement['announce_id'] ?></td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-white">
                            <?= htmlspecialchars($announcement['title']) ?>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-300 truncate max-w-[200px]" title="<?= htmlspecialchars($announcement['content']) ?>">
                            <?= htmlspecialchars($announcement['content']) ?>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $announcement['status'] == 'Active' ? 'bg-neon-green/20 text-neon-green' : 'bg-red-500/20 text-red-500' ?>">
                            <?= htmlspecialchars($announcement['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                        <?= date('M d, Y', strtotime($announcement['date_posted'])) ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <div class="text-gray-300"><?= htmlspecialchars($announcement['username']) ?></div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-purple/20 text-neon-purple">
                            <?= htmlspecialchars($announcement['role']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="openViewModal(<?= $announcement['announce_id'] ?>)" class="text-neon-blue hover:text-white transition-colors" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openAnnouncementModal(<?= $announcement['announce_id'] ?>)" class="text-neon-green hover:text-white transition-colors" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="openDeleteModal(<?= $announcement['announce_id'] ?>)" class="text-neon-pink hover:text-white transition-colors" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4">
            <div class="text-sm text-gray-400">
                Showing <span class="font-medium">1</span> to <span class="font-medium"><?= count($announcements) ?></span> of <span class="font-medium"><?= count($announcements) ?></span> results
            </div>
            <div class="flex space-x-2">
                <button class="px-3 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors">
                    Previous
                </button>
                <button class="px-3 py-1 rounded-lg bg-light/20 text-gray-400 hover:text-white hover:bg-light/30 transition-colors">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/modalFunction.js"></script>
<script>
    // Function to open announcement modal
    function openAnnouncementModal(announceId = null) {
        const url = announceId ? `../forms/modalAnnouncement.php?id=${announceId}` : '../forms/modalAnnouncement.php';
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = html;
                
                const modal = tempContainer.querySelector('#announcementModal');
                if (modal) {
                    document.body.appendChild(modal);
                    requestAnimationFrame(() => {
                        modal.classList.remove('hidden');
                    });
                }
            })
            .catch(error => {
                console.error('Error loading modal:', error);
                showToast('Failed to load form. Please try again.', 'error');
            });
    }

    // Function to open view modal
    function openViewModal(announceId) {
        fetch(`../forms/modalAnnouncement.php?id=${announceId}&view=true`)
            .then(response => response.text())
            .then(html => {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = html;
                
                const modal = tempContainer.querySelector('#viewModal');
                if (modal) {
                    document.body.appendChild(modal);
                    requestAnimationFrame(() => {
                        modal.classList.remove('hidden');
                    });
                }
            })
            .catch(error => {
                console.error('Error loading view modal:', error);
                showToast('Failed to load announcement details. Please try again.', 'error');
            });
    }

    // Function to open delete modal
    function openDeleteModal(announceId) {
        fetch('../forms/modalAnnouncement.php')
            .then(response => response.text())
            .then(html => {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = html;
                
                const deleteModal = tempContainer.querySelector('#deleteModal');
                if (deleteModal) {
                    document.body.appendChild(deleteModal);
                    document.getElementById('deleteAnnounceId').value = announceId;
                    requestAnimationFrame(() => {
                        deleteModal.classList.remove('hidden');
                    });
                }
            })
            .catch(error => {
                console.error('Error loading delete modal:', error);
                showToast('Failed to load delete confirmation. Please try again.', 'error');
            });
    }

    // Function to close modals
    function closeModal() {
        const modal = document.getElementById('announcementModal');
        if (modal) {
            modal.classList.add('hidden');
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 300);
        }
    }

    function closeViewModal() {
        const modal = document.getElementById('viewModal');
        if (modal) {
            modal.classList.add('hidden');
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 300);
        }
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.classList.add('hidden');
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 300);
        }
    }

    // Handle click outside to close modals
    document.addEventListener('click', function(event) {
        const announcementModal = document.getElementById('announcementModal');
        if (announcementModal && !announcementModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
            closeModal();
        }
        
        const viewModal = document.getElementById('viewModal');
        if (viewModal && !viewModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
            closeViewModal();
        }
        
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal && !deleteModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
            closeDeleteModal();
        }
    });

    // Handle form submission success/error messages
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('success')) {
            const successValue = urlParams.get('success');
            let message = '';
            
            switch(successValue) {
                case '1':
                    message = 'Announcement added successfully!';
                    break;
                case '2':
                    message = 'Announcement updated successfully!';
                    break;
                case '3':
                    message = 'Announcement deleted successfully!';
                    break;
            }
            
            if (message) {
                showToast(message, 'success');
            }
        }
        
        if (urlParams.has('error')) {
            const errorValue = urlParams.get('error');
            let message = 'An error occurred. Please try again.';
            
            switch(errorValue) {
                case 'missing_fields':
                    message = 'Please fill in all required fields.';
                    break;
                case 'announcement_creation_failed':
                    message = 'Failed to create announcement.';
                    break;
                case 'announcement_update_failed':
                    message = 'Failed to update announcement.';
                    break;
                case 'announcement_delete_failed':
                    message = 'Failed to delete announcement.';
                    break;
                case 'announcement_not_found':
                    message = 'Announcement not found.';
                    break;
                case 'unauthorized':
                    message = 'You are not authorized to perform this action.';
                    break;
            }
            
            showToast(message, 'error');
        }
    });

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
            type === 'success' ? 'bg-neon-green' : 'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
        });
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease-out';
            setTimeout(() => {
                if (toast.parentNode) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
</script>
</body>
</html>