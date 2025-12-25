<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['course_id'])) {
    header('Location: courses.php');
    exit();
}

$courseId = (int)$_GET['course_id'];
$course = getCourseById($courseId);

if (!$course) {
    header('Location: courses.php');
    exit();
}

$lessons = getLessonsForCourse($courseId);
$quizzes = getQuizzesForCourse($courseId);
$discussions = getDiscussionsForCourse($courseId);
$reviews = getReviewsForCourse($courseId);
$avgRating = getCourseAverageRating($courseId);
$reviewCount = getCourseReviewCount($courseId);

$isEnrolled = false;
$enrollment = null;
$courseProgress = 0;

if (isLoggedIn()) {
    $isEnrolled = isEnrolled(getCurrentUserId(), $courseId);
    if ($isEnrolled) {
        $enrollment = getUserEnrollment(getCurrentUserId(), $courseId);
        $courseProgress = getCourseProgress(getCurrentUserId(), $courseId);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - StudyHub</title>
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

    <!-- Course Header -->
    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['title']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Course Header -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                        <p class="text-muted">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-success"><?php echo htmlspecialchars($course['difficulty']); ?></span>
                                <?php if ($course['price'] > 0): ?>
                                    <span class="h4 text-primary ms-2">$<?php echo number_format($course['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="h4 text-success ms-2">Free</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($avgRating)): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </span>
                                <span class="ms-1">(<?php echo $reviewCount; ?> reviews)</span>
                            </div>
                        </div>
                        
                        <?php if ($isEnrolled): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> You are enrolled in this course
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between">
                                        <span>Progress: <?php echo $courseProgress; ?>%</span>
                                        <div class="progress" style="height: 10px; width: 200px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $courseProgress; ?>%" aria-valuenow="<?php echo $courseProgress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Course Content -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Course Description</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        
                        <h6 class="mt-4">Requirements:</h6>
                        <p><?php echo nl2br(htmlspecialchars($course['requirements'])); ?></p>
                        
                        <h6 class="mt-4">What you'll learn:</h6>
                        <p><?php echo nl2br(htmlspecialchars($course['learning_outcomes'])); ?></p>
                        
                        <h6 class="mt-4">Syllabus:</h6>
                        <p><?php echo nl2br(htmlspecialchars($course['syllabus'])); ?></p>
                    </div>
                </div>

                <!-- Lessons/Curriculum -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Course Content</h5>
                        <span><?php echo count($lessons); ?> lessons</span>
                    </div>
                    <div class="card-body">
                        <?php if (count($lessons) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($lessons as $index => $lesson): ?>
                                    <?php 
                                    $isCompleted = false;
                                    $isCurrent = false;
                                    
                                    if ($isEnrolled) {
                                        $progress = getUserLessonProgress(getCurrentUserId(), $lesson['lesson_id']);
                                        $isCompleted = $progress && $progress['is_completed'];
                                        $isCurrent = $enrollment && $lesson['lesson_order'] == $enrollment['current_lesson_order'];
                                    }
                                    ?>
                                    <div class="list-group-item <?php echo $isCompleted ? 'completed' : ''; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-graduation-cap me-2"></i>
                                                <strong><?php echo $index + 1; ?>.</strong> 
                                                <?php echo htmlspecialchars($lesson['title']); ?>
                                                <?php if ($lesson['is_preview']): ?>
                                                    <span class="badge bg-info ms-2">Preview</span>
                                                <?php endif; ?>
                                                <?php if ($isCompleted): ?>
                                                    <i class="fas fa-check-circle text-success ms-2"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($lesson['duration']): ?>
                                                    <small class="text-muted"><?php echo $lesson['duration']; ?> min</small>
                                                <?php endif; ?>
                                                <?php if ($isEnrolled || $lesson['is_preview']): ?>
                                                    <a href="lesson.php?lesson_id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-sm btn-outline-primary ms-2">
                                                        <?php echo $isEnrolled ? 'Continue' : 'Preview'; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled>Locked</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No lessons available for this course yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quizzes -->
                <?php if (count($quizzes) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Quizzes</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($quizzes as $quiz): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-question-circle me-2"></i>
                                            <?php echo htmlspecialchars($quiz['title']); ?>
                                        </div>
                                        <div>
                                            <?php if ($isEnrolled): ?>
                                                <a href="quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    Take Quiz
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary" disabled>Locked</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars($quiz['description']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reviews -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Course Reviews</h5>
                        <?php if ($isEnrolled && !hasReviewedCourse(getCurrentUserId(), $courseId)): ?>
                            <a href="#add-review" class="btn btn-sm btn-primary">Write Review</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (count($reviews) > 0): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                        </div>
                                        <div>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="fas fa-star text-warning"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-warning"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="text-muted small"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                    <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No reviews yet. Be the first to review this course!</p>
                        <?php endif; ?>
                        
                        <?php if ($isEnrolled && !hasReviewedCourse(getCurrentUserId(), $courseId)): ?>
                            <div id="add-review" class="mt-4">
                                <h6>Add Your Review</h6>
                                <form method="POST" action="process_review.php">
                                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Rating</label>
                                        <select class="form-select" id="rating" name="rating" required>
                                            <option value="">Select Rating</option>
                                            <option value="5">5 Stars - Excellent</option>
                                            <option value="4">4 Stars - Very Good</option>
                                            <option value="3">3 Stars - Good</option>
                                            <option value="2">2 Stars - Fair</option>
                                            <option value="1">1 Star - Poor</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="review_text" class="form-label">Review</label>
                                        <textarea class="form-control" id="review_text" name="review_text" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Enrollment Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if ($isEnrolled): ?>
                            <div class="text-center">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>You're enrolled!</h5>
                                <p class="text-muted">Continue your learning journey</p>
                                <a href="student/my_courses.php" class="btn btn-primary w-100">My Courses</a>
                            </div>
                        <?php else: ?>
                            <h5 class="card-title">$<?php echo number_format($course['price'], 2); ?></h5>
                            <p class="card-text text-muted">Includes:</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Lifetime access</li>
                                <li><i class="fas fa-check text-success me-2"></i> <?php echo count($lessons); ?> lessons</li>
                                <li><i class="fas fa-check text-success me-2"></i> <?php echo count($quizzes); ?> quizzes</li>
                                <li><i class="fas fa-check text-success me-2"></i> Certificate of completion</li>
                            </ul>
                            
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="process_enrollment.php">
                                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <?php echo $course['price'] > 0 ? 'Enroll Now' : 'Enroll for Free'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary w-100">Login to Enroll</a>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <a href="#" class="btn btn-outline-primary w-100">Add to Wishlist</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Instructor Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>About the Instructor</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-user-circle fa-3x text-muted"></i>
                            </div>
                            <div>
                                <h6><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></h6>
                                <p class="text-muted small mb-0">Instructor</p>
                            </div>
                        </div>
                        <p><?php echo htmlspecialchars($course['instructor_bio']); ?></p>
                        <a href="instructor_profile.php?instructor_id=<?php echo $course['instructor_id']; ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="card">
                    <div class="card-header">
                        <h6>Course Info</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Level:</strong> <?php echo htmlspecialchars($course['difficulty']); ?></li>
                            <li class="mb-2"><strong>Duration:</strong> <?php echo $course['duration']; ?> min</li>
                            <li class="mb-2"><strong>Lessons:</strong> <?php echo count($lessons); ?></li>
                            <li class="mb-2"><strong>Students:</strong> 
                                <?php 
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
                                $stmt->execute([$courseId]);
                                echo $stmt->fetchColumn();
                                ?>
                            </li>
                            <li class="mb-2"><strong>Language:</strong> English</li>
                            <li class="mb-2"><strong>Certificate:</strong> Yes</li>
                        </ul>
                    </div>
                </div>
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
</body>
</html>