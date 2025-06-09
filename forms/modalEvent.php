<?php
include_once '../components/connection.php';

// If editing, fetch event data
$event_data = null;
if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT e.*, u.username, u.role 
              FROM events e 
              LEFT JOIN users u ON e.user_id = u.user_id 
              WHERE e.event_id = '$id'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $event_data = mysqli_fetch_assoc($result);
    }
}
?>

<!-- Add/Edit Event Modal -->
<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                <?= isset($event_data) ? 'Edit Event' : 'Add New Event' ?>
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="eventForm" action="../controllers/eventController.php" method="POST" enctype="multipart/form-data">
            <?php if(isset($event_data)): ?>
                <input type="hidden" name="event_id" value="<?= $event_data['event_id'] ?>">
            <?php endif; ?>
            
            <div class="space-y-4">
                <!-- Event Information -->
                <div>
                    <label for="title" class="block text-sm text-gray-400 mb-1">Event Title</label>
                    <input type="text" id="title" name="title" required
                           class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                           value="<?= $event_data['title'] ?? '' ?>">
                </div>
                
                <div>
                    <label for="content" class="block text-sm text-gray-400 mb-1">Event Content</label>
                    <textarea id="content" name="content" required rows="4"
                              class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"><?= $event_data['content'] ?? '' ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="schedule" class="block text-sm text-gray-400 mb-1">Schedule</label>
                        <input type="date" id="schedule" name="schedule" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $event_data['schedule'] ?? '' ?>">
                    </div>
                    
                    <div>
                        <label for="location" class="block text-sm text-gray-400 mb-1">Location</label>
                        <input type="text" id="location" name="location" required
                               class="w-full bg-white border border-lighter/10 rounded-lg px-4 py-2 text-black focus:outline-none focus:ring-2 focus:ring-neon-blue"
                               value="<?= $event_data['location'] ?? '' ?>">
                    </div>
                </div>
                
                <div>
                    <label for="banner" class="block text-sm text-gray-400 mb-1">Event Banner</label>
                    <input type="file" id="banner" name="banner" accept="image/*"
                           class="w-full bg-dark-800 border border-lighter/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-neon-blue">
                    <?php if(isset($event_data) && $event_data['banner'] != 'default.jpg'): ?>
                        <p class="text-xs text-gray-400 mt-1">Current: <?= $event_data['banner'] ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if(!isset($event_data)): ?>
                    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                <?php endif; ?>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" name="<?= isset($event_data) ? 'update_event' : 'create_event' ?>" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-neon-blue to-neon-purple text-white hover:opacity-90 transition">
                    <?= isset($event_data) ? 'Update Event' : 'Add Event' ?>
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
        
        <p class="text-gray-300 mb-6">Are you sure you want to delete this event? This action cannot be undone.</p>
        
        <form id="deleteForm" method="POST" action="../controllers/eventController.php">
            <input type="hidden" name="event_id" id="deleteEventId">
            <input type="hidden" name="delete_event" value="1">
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-600 text-white hover:bg-gray-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Delete Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Event Modal -->
<div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="glass rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                Event Details
            </h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <?php if(isset($event_data)): ?>
        <div class="space-y-6">
            <!-- Event Banner -->
            <div class="relative h-48 rounded-lg overflow-hidden">
                <img class="w-full h-full object-cover" 
                     src="<?= !empty($event_data['banner']) && $event_data['banner'] != 'default.jpg' ? '../assets/uploads/events/' . htmlspecialchars($event_data['banner']) : '../assets/uploads/events/default.jpg' ?>" 
                     alt="Event Banner">
            </div>

            <!-- Event Information -->
            <div class="space-y-4">
                <div>
                    <h4 class="text-lg font-semibold text-white"><?= htmlspecialchars($event_data['title']) ?></h4>
                    <p class="text-gray-400">
                        Posted by <?= htmlspecialchars($event_data['username']) ?> 
                        <span class="px-2 py-1 ml-2 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                            <?= htmlspecialchars($event_data['role']) ?>
                        </span>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Schedule</label>
                        <p class="text-white"><?= date('F d, Y', strtotime($event_data['schedule'])) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Location</label>
                        <p class="text-white"><?= htmlspecialchars($event_data['location']) ?></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Event Content</label>
                    <div class="text-white whitespace-pre-wrap"><?= htmlspecialchars($event_data['content']) ?></div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Date Created</label>
                    <p class="text-white"><?= date('F d, Y', strtotime($event_data['date_created'])) ?></p>
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