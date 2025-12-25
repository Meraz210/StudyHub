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
                <div class="d-flex justify-content-between align-items-center">
                    <h1>My Courses</h1>
                    <a href="create_course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Course
                    </a>
                </div>
                <p class="text-muted">Manage your courses and track their performance</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
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
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($instructorCourses as $course): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($course['thumbnail']): ?>
                                                            <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="Course Thumbnail" style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                                        <?php else: ?>
                                                            <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                                <i class="fas fa-book text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>">
                                                                <?php echo htmlspecialchars($course['title']); ?>
                                                            </a>
                                                            <div class="text-muted small">
                                                                <?php echo htmlspecialchars(substr($course['description'], 0, 50)) . '...'; ?>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                                    <?php if ($course['is_published']): ?>
                                                        <span class="badge bg-success">Published</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Draft</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="edit_course.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="../course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="course_analytics.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-chart-bar"></i> Analytics
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-book fa-5x text-muted mb-3"></i>
                                <h4>No courses yet</h4>
                                <p class="text-muted">Start by creating your first course!</p>
                                <a href="create_course.php" class="btn btn-primary">Create Course</a>
                            </div>
                        <?php endif; ?>
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
</body>
</html>