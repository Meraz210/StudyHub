<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['cert_id']) || !isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$certId = (int)$_GET['cert_id'];

// Get certificate details
$stmt = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name, u.username, 
           course.title as course_title, course.instructor_id,
           instructor.first_name as instructor_first_name,
           instructor.last_name as instructor_last_name
    FROM certificates c
    JOIN users u ON c.user_id = u.user_id
    JOIN courses course ON c.course_id = course.course_id
    JOIN users instructor ON course.instructor_id = instructor.user_id
    WHERE c.certificate_id = ?
");
$stmt->execute([$certId]);
$certificate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certificate) {
    header('Location: index.php');
    exit();
}

// Check if the current user is the certificate owner
if ($certificate['user_id'] != getCurrentUserId()) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($certificate['course_title']); ?> - StudyHub</title>
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
                        <a class="nav-link" href="student/dashboard.php">Dashboard</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['first_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="student/dashboard.php">My Dashboard</a></li>
                            <li><a class="dropdown-item" href="student/my_courses.php">My Courses</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Certificate</h1>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Certificate
                    </button>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="certificate-template">
                    <div class="certificate-content">
                        <h1 class="certificate-title">Certificate of Completion</h1>
                        
                        <p class="lead">This is to certify that</p>
                        
                        <div class="certificate-name">
                            <?php echo htmlspecialchars($certificate['first_name'] . ' ' . $certificate['last_name']); ?>
                        </div>
                        
                        <p class="lead">has successfully completed the course</p>
                        
                        <div class="certificate-course">
                            <?php echo htmlspecialchars($certificate['course_title']); ?>
                        </div>
                        
                        <p class="lead">on <?php echo date('F j, Y', strtotime($certificate['issue_date'])); ?></p>
                        
                        <div class="row mt-5">
                            <div class="col-md-6 text-start">
                                <p class="mb-1">Instructor:</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($certificate['instructor_first_name'] . ' ' . $certificate['instructor_last_name']); ?></p>
                                <hr style="width: 200px;">
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1">Certificate Code:</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($certificate['certificate_code']); ?></p>
                                <hr style="width: 200px; margin-left: auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="student/my_courses.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to My Courses
                </a>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Certificate
                </button>
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