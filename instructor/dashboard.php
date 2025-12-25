<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();
if (getCurrentUserRole() !== 'instructor') {
    header('Location: ../index.php');
    exit();
}

$userId = getCurrentUserId();
$instructorCourses = getInstructorCourses($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - StudyHub</title>
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
                            <li><a class="dropdown-item" href="dashboard.php">Instructor Dashboard</a></li>
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
                <h1>Welcome back, Instructor <?php echo $_SESSION['first_name']; ?>!</h1>
                <p class="text-muted">Your instructor dashboard</p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($instructorCourses); ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $totalStudents = 0;
                        foreach ($instructorCourses as $course) {
                            $totalStudents += $course['student_count'];
                        }
                        echo $totalStudents; 
                        ?>
                    </div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $totalEnrollments = 0;
                        foreach ($instructorCourses as $course) {
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
                            $stmt->execute([$course['course_id']]);
                            $totalEnrollments += $stmt->fetchColumn();
                        }
                        echo $totalEnrollments; 
                        ?>
                    </div>
                    <div class="stat-label">Total Enrollments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $avgRating = 0;
                        $ratingCount = 0;
                        foreach ($instructorCourses as $course) {
                            if ($course['avg_rating'] > 0) {
                                $avgRating += $course['avg_rating'];
                                $ratingCount++;
                            }
                        }
                        echo $ratingCount > 0 ? number_format($avgRating / $ratingCount, 1) : '0.0'; 
                        ?>
                    </div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="create_course.php" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle"></i> Create Course
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="my_courses.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-book"></i> My Courses
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-chart-bar"></i> Analytics
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-comments"></i> Discussions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Courses -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>My Courses</h5>
                        <a href="my_courses.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($instructorCourses) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Students</th>
                                            <th>Rating</th>
                                            <th>Enrollments</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($instructorCourses, 0, 5) as $course): ?>
                                            <tr>
                                                <td>
                                                    <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>">
                                                        <?php echo htmlspecialchars($course['title']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo $course['student_count']; ?></td>
                                                <td>
                                                    <?php if ($course['avg_rating'] > 0): ?>
                                                        <?php echo number_format($course['avg_rating'], 1); ?>
                                                        <i class="fas fa-star text-warning ms-1"></i>
                                                    <?php else: ?>
                                                        No ratings
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
                                                    $stmt->execute([$course['course_id']]);
                                                    echo $stmt->fetchColumn();
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="edit_course.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h5>No courses yet</h5>
                                <p class="text-muted">Start by creating your first course!</p>
                                <a href="create_course.php" class="btn btn-primary">Create Course</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Discussions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Discussions</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">No recent discussions</p>
                        <!-- This would show recent discussions related to your courses -->
                    </div>
                </div>
                
                <!-- Course Performance -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Course Performance</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" width="400" height="200"></canvas>
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
                        <li><a href="../index.php" class="text-white">Home</a></li>
                        <li><a href="../courses.php" class="text-white">Courses</a></li>
                        <li><a href="dashboard.php" class="text-white">Instructor Dashboard</a></li>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Simple chart for course performance
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Course 1', 'Course 2', 'Course 3'],
                datasets: [{
                    label: 'Students Enrolled',
                    data: [12, 19, 3],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>