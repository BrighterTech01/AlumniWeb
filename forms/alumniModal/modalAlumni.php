<?php
if (!isset($_GET['id'])) {
    echo '<div id="alumniModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"><div class="glass rounded-xl p-8"><p class="text-red-500">No alumni selected.</p><button onclick="closeAlumniModal()" class="mt-4 px-4 py-2 bg-gray-700 rounded text-white">Close</button></div></div>';
    exit;
}
include_once '../../components/connection.php';
$user_id = (int)$_GET['id'];
$query = "SELECT * FROM view_user_details WHERE user_id = $user_id LIMIT 1";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div id="alumniModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"><div class="glass rounded-xl p-8"><p class="text-red-500">Alumni not found.</p><button onclick="closeAlumniModal()" class="mt-4 px-4 py-2 bg-gray-700 rounded text-white">Close</button></div></div>';
    exit;
}
$alumni = mysqli_fetch_assoc($result);
?>
<div id="alumniModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="glass rounded-xl p-6 w-full max-w-lg mx-4 relative shadow-2xl">
        <button onclick="closeAlumniModal()" aria-label="Close" class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl">
            <i class="fas fa-times"></i>
        </button>
        <?php if (!empty($alumni['image'])): ?>
            <img src="assets/uploads/alumni/<?php echo htmlspecialchars($alumni['image']); ?>" alt="<?php echo htmlspecialchars($alumni['firstname'] . ' ' . $alumni['lastname']); ?>" class="w-full h-48 object-cover rounded-lg mb-4">
        <?php else: ?>
            <div class="w-full h-48 bg-neon-blue/10 flex flex-col items-center justify-center rounded-lg mb-4">
                <i class="fas fa-user-graduate text-4xl text-neon-blue mb-2"></i>
                <span class="text-neon-blue font-bold text-xl"><?php echo htmlspecialchars($alumni['firstname'] . ' ' . $alumni['lastname']); ?></span>
            </div>
        <?php endif; ?>
        <h2 class="text-2xl font-bold mb-2 text-white"><?php echo htmlspecialchars($alumni['firstname'] . ' ' . $alumni['middlename'] . ' ' . $alumni['lastname']); ?></h2>
        <div class="flex flex-wrap gap-3 text-sm text-gray-300 mb-2">
            <span><i class="fas fa-graduation-cap mr-1"></i> <?php echo htmlspecialchars($alumni['course_name']); ?></span>
            <span><i class="fas fa-users mr-1"></i> Batch <?php echo htmlspecialchars($alumni['batch']); ?></span>
            <span><i class="fas fa-calendar-alt mr-1"></i> Graduated <?php echo htmlspecialchars($alumni['graduation_year']); ?></span>
        </div>
        <div class="text-gray-200 mb-4">
            <?php if (!empty($alumni['contact'])): ?>
                <div class="mb-1"><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($alumni['contact']); ?></div>
            <?php endif; ?>
            <?php if (!empty($alumni['address'])): ?>
                <div class="mb-1"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($alumni['address']); ?><?php if (!empty($alumni['city']) || !empty($alumni['country'])): ?>, <?php echo htmlspecialchars($alumni['city'] . ', ' . $alumni['country']); ?><?php endif; ?></div>
            <?php endif; ?>
            <div class="mb-1"><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($alumni['email']); ?></div>
        </div>
        <div class="flex justify-end mt-4">
            <button onclick="closeAlumniModal()" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">Close</button>
        </div>
    </div>
</div>
