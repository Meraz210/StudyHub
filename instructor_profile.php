<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['instructor_id'])) {
    header('Location: index.php');
    exit();
}

$instructorId = (int)$_GET['instructor_id'];

// Get instructor details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'instructor'");
$stmt->execute([$instructorId]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    header('Location: index.php');
    exit();
}

// Get instructor's courses
$instructorCourses = getInstructorCourses($instructorId);

// Calculate instructor stats
$totalStudents = 0;
$totalReviews = 0;
$avgRating = 0;

foreach ($instructorCourses as $course) {
    $totalStudents += $course['student_count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE course_id = ?");
    $stmt->execute([$course['course_id']]);
    $reviewsCount = $stmt->fetchColumn();
    $totalReviews += $reviewsCount;
    
    if ($reviewsCount > 0) {
        $stmt = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE course_id = ?");
        $stmt->execute([$course['course_id']]);
        $courseAvgRating = $stmt->fetchColumn();
        $avgRating += $courseAvgRating;
    }
}

if (count($instructorCourses) > 0) {
    $avgRating = $avgRating / count($instructorCourses);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?> - Instructor - StudyHub</title>
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
                        <a class="nav-link" href="courses.php">Courses</a>
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <!-- Instructor Profile Header -->
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="mb-4">
                    <?php if ($instructor['profile_image']): ?>
                        <img src="<?php echo htmlspecialchars($instructor['profile_image']); ?>" alt="Instructor Profile" class="rounded-circle" style="width: 200px; height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-10x text-muted"></i>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h1><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></h1>
                        <p class="text-muted">Instructor</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4><?php echo count($instructorCourses); ?></h4>
                                    <p class="text-muted">Courses</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4><?php echo $totalStudents; ?></h4>
                                    <p class="text-muted">Students</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4>
                                        <?php if ($avgRating > 0): ?>
                                            <?php echo number_format($avgRating, 1); ?>
                                        <?php else: ?>
                                            0.0
                                        <?php endif; ?>
                                    </h4>
                                    <p class="text-muted">Avg Rating</p>
                                </div>
                            </div>
                        </div>
                        
                        <h5>About</h5>
                        <p><?php echo nl2br(htmlspecialchars($instructor['bio'])); ?></p>
                        
                        <?php if (isLoggedIn() && getCurrentUserRole() === 'student'): ?>
                            <div class="mt-4">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#messageInstructorModal">
                                    <i class="fas fa-envelope"></i> Message Instructor
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructor's Courses -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>Courses by <?php echo htmlspecialchars($instructor['first_name']); ?></h3>
                
                <?php if (count($instructorCourses) > 0): ?>
                    <div class="row">
                        <?php foreach ($instructorCourses as $course): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <?php if ($course['thumbnail']): ?>
                                        <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>" style="height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="fas fa-book fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-star text-warning"></i> 
                                                    <?php echo $course['avg_rating'] ? number_format($course['avg_rating'], 1) : '0.0'; ?>
                                                    (<?php echo $course['student_count']; ?> students)
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> <?php echo $course['student_count']; ?> students
                                                </small>
                                            </div>
                                            <div class="mt-2">
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
                        <h4>No courses available</h4>
                        <p class="text-muted">This instructor hasn't published any courses yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Message Instructor Modal -->
    <?php if (isLoggedIn() && getCurrentUserRole() === 'student'): ?>
    <div class="modal fade" id="messageInstructorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Instructor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="messageSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="messageSubject" placeholder="Enter subject">
                        </div>
                        <div class="mb-3">
                            <label for="messageContent" class="form-label">Message</label>
                            <textarea class="form-control" id="messageContent" rows="4" placeholder="Enter your message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                        <li><a href="student/dashboard.php" class="text-white">Student Dashboard</a></li>
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
</body>
</html>