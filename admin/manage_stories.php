<?php
include_once '../components/connection.php';

// Fetch stories with user details using the view
$query = "SELECT * FROM view_stories_with_users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stories - Alumni Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include_once '../components/adminHeader.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="glass rounded-xl p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-neon-blue to-neon-purple text-transparent bg-clip-text">
                        Manage Stories
                    </h1>
                    <p class="text-gray-400 mt-1">View and manage all stories posted by alumni</p>
                </div>
                <button onclick="openStoryModal()" class="px-4 py-2 rounded-lg bg-neon-blue hover:bg-neon-purple transition">
                    <i class="fas fa-plus mr-2"></i>Add New Story
                </button>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="glass rounded-xl p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search stories..." 
                           class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 focus:border-neon-blue focus:ring-1 focus:ring-neon-blue">
                    <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                </div>
                <select id="mediaTypeFilter" class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 focus:border-neon-blue focus:ring-1 focus:ring-neon-blue">
                    <option value="">All Media Types</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                </select>
                <select id="statusFilter" class="px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 focus:border-neon-blue focus:ring-1 focus:ring-neon-blue">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <!-- Stories Table -->
        <div class="glass rounded-xl p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-700">
                            <th class="pb-3 font-semibold text-neon-blue">ID</th>
                            <th class="pb-3 font-semibold text-neon-blue">Story</th>
                            <th class="pb-3 font-semibold text-neon-blue">Media Type</th>
                            <th class="pb-3 font-semibold text-neon-blue">Posted By</th>
                            <th class="pb-3 font-semibold text-neon-blue">Status</th>
                            <th class="pb-3 font-semibold text-neon-blue">Date Created</th>
                            <th class="pb-3 font-semibold text-neon-blue">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="border-b border-gray-700 hover:bg-gray-800/50 transition">
                                    <td class="py-4"><?= $row['story_id'] ?></td>
                                    <td class="py-4">
                                        <div class="flex items-center space-x-3">
                                            <?php if($row['media_type'] == 'image'): ?>
                                                <img src="../assets/uploads/stories/<?= $row['vimage'] ?>" 
                                                     alt="Story Image" 
                                                     class="w-12 h-12 rounded-lg object-cover">
                                            <?php endif; ?>
                                            <div>
                                                <h4 class="font-medium text-white"><?= htmlspecialchars($row['title']) ?></h4>
                                                <p class="text-sm text-gray-400"><?= substr(htmlspecialchars($row['description']), 0, 50) ?>...</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                            <?= $row['media_type'] == 'image' ? 'bg-neon-blue/20 text-neon-blue' : 'bg-neon-purple/20 text-neon-purple' ?>">
                                            <?= ucfirst($row['media_type']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-white"><?= htmlspecialchars($row['username']) ?></span>
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-neon-blue/20 text-neon-blue">
                                                <?= htmlspecialchars($row['role']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                            <?php
                                            switch($row['status']) {
                                                case 'pending':
                                                    echo 'bg-yellow-500/20 text-yellow-500';
                                                    break;
                                                case 'approved':
                                                    echo 'bg-neon-green/20 text-neon-green';
                                                    break;
                                                case 'rejected':
                                                    echo 'bg-red-500/20 text-red-500';
                                                    break;
                                            }
                                            ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 text-gray-400">
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="openStoryViewModal(<?= $row['story_id'] ?>)" 
                                                    class="text-neon-blue hover:text-neon-purple">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="openStoryModal(<?= $row['story_id'] ?>)" 
                                                    class="text-yellow-500 hover:text-yellow-400">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="openStoryDeleteModal(<?= $row['story_id'] ?>)" 
                                                    class="text-red-500 hover:text-red-400">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-4 text-center text-gray-400">
                                    No stories found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/modalFunction.js"></script>
    <script>
        // Show success/error messages if present in URL
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        const type = urlParams.get('type');
        
        if(message) {
            showToast(message, type || 'success');
        }
    </script>
</body>
</html>