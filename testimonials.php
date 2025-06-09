<?php
// Only start session if it hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'components/connection.php';

// Check if user is logged in and is an Alumni
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Alumni') {
    header("Location: login.php");
    exit();
}

// Fetch user details from view_user_details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM view_user_details WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Construct full name
$fullname = trim($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']);

// Handle testimonial submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    $testimonial_text = mysqli_real_escape_string($conn, $_POST['testimonial_text']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    // Handle photo upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = './assets/uploads/testimonials/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            $photo = $new_filename;
        }
    }
    
    $insert_query = "INSERT INTO testimonials (user_id, name, photo, testimonial_text, rating, status, created_at) 
                    VALUES ('$user_id', '$name', '$photo', '$testimonial_text', '$rating', 'pending', NOW())";
    
    if (mysqli_query($conn, $insert_query)) {
        $success_message = "Your testimonial has been submitted and is pending approval.";
    } else {
        $error_message = "Error submitting testimonial. Please try again.";
    }
}

// Fetch approved testimonials
$testimonials_query = "SELECT t.*, u.firstname, u.lastname, u.image, u.batch, u.course_name 
                      FROM testimonials t 
                      JOIN view_user_details u ON t.user_id = u.user_id 
                      WHERE t.status = 'approved' 
                      ORDER BY t.created_at DESC";
$testimonials_result = mysqli_query($conn, $testimonials_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMSCST Alumni Portal - Testimonials</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .neon-glow {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }
        .testimonial-card {
            transition: transform 0.2s ease-in-out;
        }
        .testimonial-card:hover {
            transform: translateY(-2px);
        }
        .quote-icon {
            opacity: 0.1;
        }
        .star-rating {
            color: #FFD700;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen text-white">
    <?php include 'components/userHeader.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="glass rounded-xl p-6 border border-lighter/10 mb-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Alumni Testimonials</h1>
                        <p class="text-gray-400">Share your success story and inspire others.</p>
                    </div>
                    <button onclick="document.getElementById('testimonialModal').classList.remove('hidden')" 
                            class="px-6 py-2 bg-neon-blue hover:bg-neon-blue/80 text-white rounded-lg transition-colors">
                        Share Your Story <i class="fas fa-plus ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Testimonials Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (mysqli_num_rows($testimonials_result) > 0): ?>
                    <?php while ($testimonial = mysqli_fetch_assoc($testimonials_result)): ?>
                        <div class="testimonial-card glass rounded-xl p-6 border border-lighter/10">
                            <div class="relative">
                                <i class="fas fa-quote-left text-4xl text-neon-blue quote-icon absolute -top-2 -left-2"></i>
                                <p class="text-gray-300 mb-6 relative z-10"><?php echo nl2br(htmlspecialchars($testimonial['testimonial_text'])); ?></p>
                                
                                <!-- Star Rating -->
                                <div class="flex gap-1 mb-4">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'star-rating' : 'text-gray-600'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4 pt-4 border-t border-lighter/10">
                                <?php if (!empty($testimonial['photo'])): ?>
                                    <img src="./assets/uploads/testimonials/<?php echo htmlspecialchars($testimonial['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['name']); ?>" 
                                         class="w-12 h-12 rounded-full object-cover">
                                <?php elseif (!empty($testimonial['image'])): ?>
                                    <img src="./assets/uploads/alumni/<?php echo htmlspecialchars($testimonial['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['name']); ?>" 
                                         class="w-12 h-12 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-neon-blue/10 flex items-center justify-center">
                                        <i class="fas fa-user text-neon-blue"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div>
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($testimonial['name']); ?></h3>
                                    <p class="text-xs text-gray-500">
                                        Batch <?php echo htmlspecialchars($testimonial['batch']); ?> • 
                                        <?php echo htmlspecialchars($testimonial['course_name']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full">
                        <div class="glass rounded-xl p-8 border border-lighter/10 text-center">
                            <i class="fas fa-comments text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No Testimonials Yet</h3>
                            <p class="text-gray-400">Be the first to share your success story!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Testimonial Modal -->
    <div id="testimonialModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="glass rounded-xl p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Share Your Story</h2>
                <button onclick="document.getElementById('testimonialModal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="bg-neon-green/20 border border-neon-green/30 rounded-lg p-4 mb-6">
                    <p class="text-neon-green"><?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-500/20 border border-red-500/30 rounded-lg p-4 mb-6">
                    <p class="text-red-500"><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Your Name</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($fullname); ?>"
                           class="w-full bg-light/20 text-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                           placeholder="Your full name">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Your Testimonial</label>
                    <textarea name="testimonial_text" rows="6" required
                              class="w-full bg-light/20 text-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all"
                              placeholder="Share your experience and success story..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Rating</label>
                    <div class="flex gap-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" required class="hidden">
                                <i class="fas fa-star text-2xl text-gray-600 hover:text-yellow-500 transition-colors"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Your Photo (Optional)</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full bg-light/20 text-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Max file size: 2MB. Supported formats: JPG, PNG, GIF</p>
                </div>
                
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" 
                            onclick="document.getElementById('testimonialModal').classList.add('hidden')"
                            class="px-6 py-2 bg-light/20 hover:bg-light/30 text-gray-300 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="submit_testimonial"
                            class="px-6 py-2 bg-neon-blue hover:bg-neon-blue/80 text-white rounded-lg transition-colors">
                        Submit Testimonial
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Close modal when clicking outside
        document.getElementById('testimonialModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Star rating interaction
        const starInputs = document.querySelectorAll('input[name="rating"]');
        const starIcons = document.querySelectorAll('.fa-star');

        starInputs.forEach((input, index) => {
            input.addEventListener('change', () => {
                starIcons.forEach((icon, i) => {
                    if (i <= index) {
                        icon.classList.add('text-yellow-500');
                        icon.classList.remove('text-gray-600');
                    } else {
                        icon.classList.remove('text-yellow-500');
                        icon.classList.add('text-gray-600');
                    }
                });
            });
        });
    </script>
</body>
</html>
