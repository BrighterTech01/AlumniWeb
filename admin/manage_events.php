<?php
include_once '../components/connection.php';

// Query to fetch events with user details
$query = "SELECT e.*, u.username, u.role 
          FROM events e 
          LEFT JOIN users u ON e.user_id = u.user_id 
          ORDER BY e.date_created DESC";
$result = mysqli_query($conn, $query);
$events = [];

// Check if query was successful and has results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($events, $row);
    }
}
?>

<?php include_once '../components/adminHeader.php'?>
<div class="pt-20 px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">Manage Events</h1>
            <p class="text-gray-400 text-sm sm:text-base">View and manage events</p>
        </div>
        <button onclick="openEventModal()" class="mt-4 sm:mt-0 px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
            Add New Event
        </button>
    </div>

    <!-- Search and Filter Section -->
    <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <input type="search" 
                       placeholder="Search events..." 
                       class="w-full bg-light/20 text-gray-300 placeholder-gray-400 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <input type="date" 
                   class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50">
            <select class="bg-light/20 text-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                <option value="">All Locations</option>
                <option value="Online">Online</option>
                <option value="On-site">On-site</option>
            </select>
        </div>
    </div>

    <!-- Events Table -->
    <div class="glass rounded-xl p-4 sm:p-6 border border-lighter/10 card-hover overflow-x-auto">
        <table class="min-w-full divide-y divide-lighter/20">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Event</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Schedule</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Location</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Posted By</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date Created</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-lighter/10">
                <?php if (empty($events)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-3 text-center text-gray-400">
                        No events found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($events as $event): ?>
                <tr class="hover:bg-light/5 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300"><?= $event['event_id'] ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-20">
                                <img class="h-12 w-20 rounded-lg object-cover" 
                                     src="<?= !empty($event['banner']) && $event['banner'] != 'default.jpg' ? '../assets/uploads/events/' . htmlspecialchars($event['banner']) : '../assets/uploads/events/default.jpg' ?>" 
                                     alt="Event Banner">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-white">
                                    <?= htmlspecialchars($event['title']) ?>
                                </div>
                                <div class="text-xs text-gray-400 truncate max-w-[200px]" title="<?= htmlspecialchars($event['content']) ?>">
                                    <?= htmlspecialchars($event['content']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                        <?= date('M d, Y', strtotime($event['schedule'])) ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                        <?= htmlspecialchars($event['location']) ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <div class="text-gray-300"><?= htmlspecialchars($event['username']) ?></div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                            <?= htmlspecialchars($event['role']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">
                        <?= date('M d, Y', strtotime($event['date_created'])) ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="openViewModal(<?= $event['event_id'] ?>)" class="text-neon-blue hover:text-white transition-colors" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openEventModal(<?= $event['event_id'] ?>)" class="text-neon-green hover:text-white transition-colors" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="openDeleteModal(<?= $event['event_id'] ?>)" class="text-neon-pink hover:text-white transition-colors" title="Delete">
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
                Showing <span class="font-medium">1</span> to <span class="font-medium"><?= count($events) ?></span> of <span class="font-medium"><?= count($events) ?></span> results
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
    // Function to open event modal
    function openEventModal(eventId = null) {
        const url = eventId ? `../forms/modalEvent.php?id=${eventId}` : '../forms/modalEvent.php';
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = html;
                
                const modal = tempContainer.querySelector('#eventModal');
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
    function openViewModal(eventId) {
        fetch(`../forms/modalEvent.php?id=${eventId}&view=true`)
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
                showToast('Failed to load event details. Please try again.', 'error');
            });
    }

    // Function to open delete modal
    function openDeleteModal(eventId) {
        fetch('../forms/modalEvent.php')
            .then(response => response.text())
            .then(html => {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = html;
                
                const deleteModal = tempContainer.querySelector('#deleteModal');
                if (deleteModal) {
                    document.body.appendChild(deleteModal);
                    document.getElementById('deleteEventId').value = eventId;
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
        const modal = document.getElementById('eventModal');
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
        const eventModal = document.getElementById('eventModal');
        if (eventModal && !eventModal.contains(event.target) && !event.target.closest('[data-modal-trigger]')) {
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
                    message = 'Event added successfully!';
                    break;
                case '2':
                    message = 'Event updated successfully!';
                    break;
                case '3':
                    message = 'Event deleted successfully!';
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
                case 'event_creation_failed':
                    message = 'Failed to create event.';
                    break;
                case 'event_update_failed':
                    message = 'Failed to update event.';
                    break;
                case 'event_delete_failed':
                    message = 'Failed to delete event.';
                    break;
                case 'event_not_found':
                    message = 'Event not found.';
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