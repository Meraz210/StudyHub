<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['lesson_id'])) {
    header('Location: index.php');
    exit();
}

$lessonId = (int)$_GET['lesson_id'];

// Get lesson details
$stmt = $pdo->prepare("
    SELECT l.*, c.title as course_title, c.course_id, u.first_name, u.last_name
    FROM lessons l
    JOIN courses c ON l.course_id = c.course_id
    JOIN users u ON c.instructor_id = u.user_id
    WHERE l.lesson_id = ?
");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    header('Location: index.php');
    exit();
}

// Check if user is enrolled or if it's a preview lesson
$isEnrolled = false;
$canAccess = false;

if (isLoggedIn()) {
    $isEnrolled = isEnrolled(getCurrentUserId(), $lesson['course_id']);
}

if ($isEnrolled || $lesson['is_preview']) {
    $canAccess = true;
} else {
    // Redirect to course page if not enrolled and not a preview
    header('Location: course_detail.php?course_id=' . $lesson['course_id']);
    exit();
}

// Get course progress
$courseProgress = 0;
if ($isEnrolled) {
    $courseProgress = getCourseProgress(getCurrentUserId(), $lesson['course_id']);
}

// Get all lessons in the course for navigation
$allLessons = getLessonsForCourse($lesson['course_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - StudyHub</title>
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

    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar with course content -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h6><?php echo htmlspecialchars($lesson['course_title']); ?></h6>
                        <div class="progress mt-2" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $courseProgress; ?>%" aria-valuenow="<?php echo $courseProgress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">Progress: <?php echo $courseProgress; ?>%</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($allLessons as $index => $courseLesson): ?>
                                <?php 
                                $isCompleted = false;
                                if ($isEnrolled) {
                                    $progress = getUserLessonProgress(getCurrentUserId(), $courseLesson['lesson_id']);
                                    $isCompleted = $progress && $progress['is_completed'];
                                }
                                ?>
                                <a href="lesson.php?lesson_id=<?php echo $courseLesson['lesson_id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $courseLesson['lesson_id'] == $lessonId ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fas fa-graduation-cap me-2"></i>
                                            <?php echo $index + 1; ?>. <?php echo htmlspecialchars($courseLesson['title']); ?>
                                        </div>
                                        <?php if ($isCompleted): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="course_detail.php?course_id=<?php echo $lesson['course_id']; ?>" class="btn btn-outline-primary w-100">
                        <i class="fas fa-arrow-left"></i> Back to Course
                    </a>
                </div>
            </div>
            
            <!-- Main lesson content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><?php echo htmlspecialchars($lesson['title']); ?></h5>
                        <span class="badge bg-secondary"><?php echo $lesson['duration']; ?> min</span>
                    </div>
                    <div class="card-body">
                        <?php if ($lesson['video_url']): ?>
                            <div class="ratio ratio-16x9 mb-4">
                                <iframe src="<?php echo htmlspecialchars($lesson['video_url']); ?>" title="<?php echo htmlspecialchars($lesson['title']); ?>" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                        
                        <div class="lesson-content">
                            <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                        </div>
                        
                        <?php if ($canAccess && $isEnrolled): ?>
                            <div class="mt-4">
                                <?php
                                $userLessonProgress = getUserLessonProgress(getCurrentUserId(), $lessonId);
                                $isCompleted = $userLessonProgress && $userLessonProgress['is_completed'];
                                ?>
                                
                                <?php if (!$isCompleted): ?>
                                    <form method="POST" action="process_lesson_completion.php">
                                        <input type="hidden" name="lesson_id" value="<?php echo $lessonId; ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $lesson['course_id']; ?>">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Mark as Complete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> Lesson completed
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Navigation to next lesson -->
                <?php if ($isEnrolled): ?>
                    <div class="d-flex justify-content-between mt-3">
                        <?php 
                        $currentLessonIndex = -1;
                        foreach ($allLessons as $index => $courseLesson) {
                            if ($courseLesson['lesson_id'] == $lessonId) {
                                $currentLessonIndex = $index;
                                break;
                            }
                        }
                        
                        if ($currentLessonIndex > 0) {
                            $prevLesson = $allLessons[$currentLessonIndex - 1];
                        } else {
                            $prevLesson = null;
                        }
                        
                        if ($currentLessonIndex < count($allLessons) - 1) {
                            $nextLesson = $allLessons[$currentLessonIndex + 1];
                        } else {
                            $nextLesson = null;
                        }
                        ?>
                        
                        <?php if ($prevLesson): ?>
                            <a href="lesson.php?lesson_id=<?php echo $prevLesson['lesson_id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Previous Lesson
                            </a>
                        <?php else: ?>
                            <div></div> <!-- Empty div to maintain spacing -->
                        <?php endif; ?>
                        
                        <?php if ($nextLesson): ?>
                            <a href="lesson.php?lesson_id=<?php echo $nextLesson['lesson_id']; ?>" class="btn btn-primary">
                                Next Lesson <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
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
</body>
</html>