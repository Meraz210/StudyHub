<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get category filter
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get sort option
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get all categories for filter
$categories = getCategories();

// Get courses based on category filter
$courses = getCoursesByCategory($categoryId);

// Apply sorting based on selected option
switch($sort) {
    case 'popular':
        // Sort by number of enrollments
        usort($courses, function($a, $b) {
            global $pdo;
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
            $stmt->execute([$a['course_id']]);
            $a_enrollments = $stmt->fetchColumn();
            $stmt->execute([$b['course_id']]);
            $b_enrollments = $stmt->fetchColumn();
            return $b_enrollments - $a_enrollments;
        });
        break;
    case 'rating':
        usort($courses, function($a, $b) {
            return ($b['avg_rating'] ?? 0) <=> ($a['avg_rating'] ?? 0);
        });
        break;
    case 'price-low':
        usort($courses, function($a, $b) {
            return ($a['price'] ?? 0) <=> ($b['price'] ?? 0);
        });
        break;
    case 'price-high':
        usort($courses, function($a, $b) {
            return ($b['price'] ?? 0) <=> ($a['price'] ?? 0);
        });
        break;
    case 'newest':
    default:
        // Courses are already sorted by newest by default in the function
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - StudyHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">StudyHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['first_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (getCurrentUserRole() === 'student'): ?>
                                    <li><a class="dropdown-item" href="student/dashboard.php">My Dashboard</a></li>
                                    <li><a class="dropdown-item" href="student/my_courses.php">My Courses</a></li>
                                <?php elseif (getCurrentUserRole() === 'instructor'): ?>
                                    <li><a class="dropdown-item" href="instructor/dashboard.php">Instructor Dashboard</a></li>
                                    <li><a class="dropdown-item" href="instructor/my_courses.php">My Courses</a></li>
                                <?php elseif (getCurrentUserRole() === 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center">Our Courses</h1>
                <p class="text-center text-muted">Discover our wide range of courses designed to help you advance your skills</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Category Filter Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Filter by Category</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item <?php echo !$categoryId ? 'active' : ''; ?>">
                                <a href="courses.php?sort=<?php echo $sort; ?>" class="text-decoration-none <?php echo !$categoryId ? 'text-white' : 'text-dark'; ?>">
                                    All Courses
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                                <li class="list-group-item <?php echo $categoryId == $category['category_id'] ? 'active' : ''; ?>">
                                    <a href="courses.php?category=<?php echo $category['category_id']; ?>&sort=<?php echo $sort; ?>" class="text-decoration-none <?php echo $categoryId == $category['category_id'] ? 'text-white' : 'text-dark'; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Difficulty Filter -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Filter by Difficulty</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="beginner" id="difficultyBeginner">
                            <label class="form-check-label" for="difficultyBeginner">
                                Beginner
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="intermediate" id="difficultyIntermediate">
                            <label class="form-check-label" for="difficultyIntermediate">
                                Intermediate
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="advanced" id="difficultyAdvanced">
                            <label class="form-check-label" for="difficultyAdvanced">
                                Advanced
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Listings -->
            <div class="col-md-9">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h3>
                            <?php if ($categoryId): ?>
                                <?php 
                                $selectedCategory = array_filter($categories, function($cat) use ($categoryId) {
                                    return $cat['category_id'] == $categoryId;
                                });
                                $selectedCategory = reset($selectedCategory);
                                echo htmlspecialchars($selectedCategory['name']) . ' Courses';
                                ?>
                            <?php else: ?>
                                All Courses
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <select class="form-select w-auto" id="sortCourses" onchange="handleSortChange(this.value)">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="price-low" <?php echo $sort === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price-high" <?php echo $sort === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <?php if (count($courses) > 0): ?>
                    <div class="row">
                        <?php foreach ($courses as $course): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 course-card">
                                    <?php if ($course['thumbnail']): ?>
                                        <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>" style="height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="fas fa-book fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 120)) . '...'; ?></p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['first_name'] ?? 'Unknown Instructor') . ' ' . htmlspecialchars($course['last_name'] ?? ''); ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-star text-warning"></i> 
                                                    <?php echo $course['avg_rating'] ? number_format($course['avg_rating'], 1) : '0.0'; ?>
                                                    (<?php echo $course['review_count']; ?>)
                                                </small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-success"><?php echo htmlspecialchars($course['difficulty']); ?></span>
                                                <?php if ($course['price'] > 0): ?>
                                                    <span class="text-primary fw-bold">$<?php echo number_format($course['price'], 2); ?></span>
                                                <?php else: ?>
                                                    <span class="text-success fw-bold">Free</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary w-100">View Course</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-5x text-muted mb-3"></i>
                        <h4>No courses found</h4>
                        <p class="text-muted">There are no courses available in this category yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>StudyHub</h5>
                    <p>Your gateway to quality education and skill development.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="courses.php" class="text-white">Courses</a></li>
                        <li><a href="about.php" class="text-white">About</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p>Email: info@studyhub.com</p>
                    <p>Phone: (123) 456-7890</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2025 StudyHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to handle sort change
        function handleSortChange(sortValue) {
            // Get current category if exists
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category');
            
            // Build new URL with sort parameter
            let newUrl = 'courses.php';
            if (category) {
                newUrl += '?category=' + category + '&sort=' + sortValue;
            } else {
                newUrl += '?sort=' + sortValue;
            }
            
            // Redirect to new URL
            window.location.href = newUrl;
        }
        
        // Add event listeners for difficulty filters
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                console.log('Filtering by difficulty: ' + this.value);
            });
        });
    </script>
</body>
</html>