<?php
if (!isset($_GET['id'])) {
    echo '<div id="testimonialModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"><div class="glass rounded-xl p-8"><p class="text-red-500">No testimonial selected.</p><button onclick="closeTestimonialModal()" class="mt-4 px-4 py-2 bg-gray-700 rounded text-white">Close</button></div></div>';
    exit;
}
include_once '../../components/connection.php';
$testimonial_id = (int)$_GET['id'];
$query = "SELECT t.*, u.firstname, u.lastname, u.image, u.batch, u.course_name FROM testimonials t JOIN view_user_details u ON t.user_id = u.user_id WHERE t.testimonial_id = $testimonial_id LIMIT 1";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div id="testimonialModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"><div class="glass rounded-xl p-8"><p class="text-red-500">Testimonial not found.</p><button onclick="closeTestimonialModal()" class="mt-4 px-4 py-2 bg-gray-700 rounded text-white">Close</button></div></div>';
    exit;
}
$testimonial = mysqli_fetch_assoc($result);
?>
<div id="testimonialModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="glass rounded-xl p-6 w-full max-w-lg mx-4 relative shadow-2xl">
        <button onclick="closeTestimonialModal()" aria-label="Close" class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl">
            <i class="fas fa-times"></i>
        </button>
        <div class="relative mb-6">
            <i class="fas fa-quote-left text-4xl text-neon-blue quote-icon absolute -top-2 -left-2"></i>
            <p class="text-gray-300 mb-6 relative z-10"><?php echo nl2br(htmlspecialchars($testimonial['testimonial_text'])); ?></p>
            <div class="flex gap-1 mb-4">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'star-rating' : 'text-gray-600'; ?>"></i>
                <?php endfor; ?>
            </div>
        </div>
        <div class="flex items-center gap-4 pt-4 border-t border-lighter/10">
            <?php if (!empty($testimonial['photo'])): ?>
                <img src="assets/uploads/testimonials/<?php echo htmlspecialchars($testimonial['photo']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="w-12 h-12 rounded-full object-cover">
            <?php elseif (!empty($testimonial['image'])): ?>
                <img src="assets/uploads/alumni/<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="w-12 h-12 rounded-full object-cover">
            <?php else: ?>
                <div class="w-12 h-12 rounded-full bg-neon-blue/10 flex items-center justify-center">
                    <i class="fas fa-user text-neon-blue"></i>
                </div>
            <?php endif; ?>
            <div>
                <h3 class="font-semibold"><?php echo htmlspecialchars($testimonial['name']); ?></h3>
                <p class="text-xs text-gray-500">Batch <?php echo htmlspecialchars($testimonial['batch']); ?> • <?php echo htmlspecialchars($testimonial['course_name']); ?></p>
            </div>
        </div>
        <div class="flex justify-end mt-4">
            <button onclick="closeTestimonialModal()" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white">Close</button>
        </div>
    </div>
</div>
