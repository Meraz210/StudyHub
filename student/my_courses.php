<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
if (getCurrentUserRole() !== 'student') {
    header('Location: ../index.php');
    exit();
}

$userId = getCurrentUserId();
$enrolledCourses = getUserEnrolledCourses($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - StudyHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">StudyHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['first_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dashboard.php">My Dashboard</a></li>
                            <li><a class="dropdown-item" href="my_courses.php">My Courses</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1>My Courses</h1>
                <p class="text-muted">All the courses you've enrolled in</p>
            </div>
        </div>
        
        <!-- Course Tabs -->
        <ul class="nav nav-tabs mb-4" id="courseTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All Courses</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="in-progress-tab" data-bs-toggle="tab" data-bs-target="#in-progress" type="button" role="tab">In Progress</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">Completed</button>
            </li>
        </ul>
        
        <div class="tab-content" id="courseTabsContent">
            <!-- All Courses Tab -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <?php if (count($enrolledCourses) > 0): ?>
                    <div class="row">
                        <?php foreach ($enrolledCourses as $course): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                                        <div class="mt-auto">
                                            <div class="progress mb-2" style="height: 10px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $course['completion_percentage']; ?>%" aria-valuenow="<?php echo $course['completion_percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted"><?php echo $course['completion_percentage']; ?>% complete</small>
                                                <?php if ($course['is_completed']): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">In Progress</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary w-100">Continue Learning</a>
                                        <?php if ($course['is_completed']): ?>
                                            <a href="../generate_certificate.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-success w-100 mt-2">
                                                <i class="fas fa-certificate"></i> Get Certificate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-5x text-muted mb-3"></i>
                        <h4>You haven't enrolled in any courses yet</h4>
                        <p class="text-muted">Start your learning journey by enrolling in a course!</p>
                        <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- In Progress Tab -->
            <div class="tab-pane fade" id="in-progress" role="tabpanel">
                <?php 
                $inProgressCourses = array_filter($enrolledCourses, function($course) {
                    return $course['completion_percentage'] > 0 && !$course['is_completed'];
                });
                ?>
                <?php if (count($inProgressCourses) > 0): ?>
                    <div class="row">
                        <?php foreach ($inProgressCourses as $course): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                                        <div class="mt-auto">
                                            <div class="progress mb-2" style="height: 10px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $course['completion_percentage']; ?>%" aria-valuenow="<?php echo $course['completion_percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted"><?php echo $course['completion_percentage']; ?>% complete</small>
                                                <span class="badge bg-info">In Progress</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary w-100">Continue Learning</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-5x text-muted mb-3"></i>
                        <h4>No courses in progress</h4>
                        <p class="text-muted">Start learning by continuing one of your enrolled courses!</p>
                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Completed Tab -->
            <div class="tab-pane fade" id="completed" role="tabpanel">
                <?php 
                $completedCourses = array_filter($enrolledCourses, function($course) {
                    return $course['is_completed'];
                });
                ?>
                <?php if (count($completedCourses) > 0): ?>
                    <div class="row">
                        <?php foreach ($completedCourses as $course): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">100% complete</small>
                                                <span class="badge bg-success">Completed</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary w-100">Review Course</a>
                                        <a href="../generate_pdf_certificate.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-success w-100 mt-2">
                                            <i class="fas fa-certificate"></i> Get Certificate
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-5x text-muted mb-3"></i>
                        <h4>No completed courses yet</h4>
                        <p class="text-muted">Keep learning to complete your first course!</p>
                        <a href="dashboard.php" class="btn btn-primary">Continue Learning</a>
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
                        <li><a href="../index.php" class="text-white">Home</a></li>
                        <li><a href="../courses.php" class="text-white">Courses</a></li>
                        <li><a href="dashboard.php" class="text-white">Student Dashboard</a></li>
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