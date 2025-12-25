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
    <title>Student Dashboard - StudyHub</title>
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
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
                <h1>Welcome back, <?php echo $_SESSION['first_name']; ?>!</h1>
                <p class="text-muted">Your learning dashboard</p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($enrolledCourses); ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $completedCourses = array_filter($enrolledCourses, function($course) {
                            return $course['is_completed'];
                        });
                        echo count($completedCourses); 
                        ?>
                    </div>
                    <div class="stat-label">Completed Courses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $inProgress = array_filter($enrolledCourses, function($course) {
                            return $course['completion_percentage'] > 0 && !$course['is_completed'];
                        });
                        echo count($inProgress); 
                        ?>
                    </div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $avgProgress = 0;
                        if (count($enrolledCourses) > 0) {
                            $totalProgress = array_sum(array_column($enrolledCourses, 'completion_percentage'));
                            $avgProgress = round($totalProgress / count($enrolledCourses), 1);
                        }
                        echo $avgProgress; 
                        ?>%
                    </div>
                    <div class="stat-label">Avg Progress</div>
                </div>
            </div>
        </div>
        
        <!-- Continue Learning -->
        <div class="row">
            <div class="col-12">
                <h3>Continue Learning</h3>
                <div class="row">
                    <?php if (count($enrolledCourses) > 0): ?>
                        <?php 
                        $inProgressCourses = array_filter($enrolledCourses, function($course) {
                            return $course['completion_percentage'] > 0 && !$course['is_completed'];
                        });
                        
                        if (count($inProgressCourses) > 0):
                        ?>
                            <?php foreach (array_slice($inProgressCourses, 0, 4) as $course): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                                            <div class="progress mb-2" style="height: 10px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $course['completion_percentage']; ?>%" aria-valuenow="<?php echo $course['completion_percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted"><?php echo $course['completion_percentage']; ?>% complete</small>
                                                <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm">Continue</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="fas fa-book fa-5x text-muted mb-3"></i>
                                    <h4>No courses in progress</h4>
                                    <p class="text-muted">Start learning by enrolling in a course!</p>
                                    <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-book fa-5x text-muted mb-3"></i>
                                <h4>No enrolled courses</h4>
                                <p class="text-muted">Start learning by enrolling in a course!</p>
                                <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recently Enrolled -->
        <?php if (count($enrolledCourses) > 0): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3>Recently Enrolled</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Progress</th>
                                <th>Enrolled Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($enrolledCourses, 0, 5) as $course): ?>
                                <tr>
                                    <td>
                                        <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>">
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress" style="width: 100px; height: 10px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $course['completion_percentage']; ?>%" aria-valuenow="<?php echo $course['completion_percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="ms-2"><?php echo $course['completion_percentage']; ?>%</span>
                                        </div>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($course['enrollment_date'])); ?></td>
                                    <td>
                                        <?php if ($course['is_completed']): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
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