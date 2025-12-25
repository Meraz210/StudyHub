<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['payment_id']) || !isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$paymentId = (int)$_GET['payment_id'];
$userId = getCurrentUserId();

// Get payment details
$stmt = $pdo->prepare("
    SELECT p.*, c.title as course_title, c.course_id, u.first_name, u.last_name
    FROM payments p
    JOIN courses c ON p.course_id = c.course_id
    JOIN users u ON c.instructor_id = u.user_id
    WHERE p.payment_id = ? AND p.user_id = ?
");
$stmt->execute([$paymentId, $userId]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - StudyHub</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h2 class="text-success">Payment Successful!</h2>
                        <p class="lead">Thank you for your purchase. You have been enrolled in the course.</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Transaction Details</h5>
                                        <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                                        <p><strong>Payment Date:</strong> <?php echo date('F j, Y g:i A', strtotime($payment['payment_date'])); ?></p>
                                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></p>
                                        <p><strong>Status:</strong> <span class="badge bg-success"><?php echo ucfirst($payment['payment_status']); ?></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Order Summary</h5>
                                        <p><strong>Course:</strong> <?php echo htmlspecialchars($payment['course_title']); ?></p>
                                        <p><strong>Original Price:</strong> $<?php echo number_format($payment['amount'], 2); ?></p>
                                        <?php if ($payment['discount_amount'] > 0): ?>
                                            <p><strong>Discount:</strong> -$<?php echo number_format($payment['discount_amount'], 2); ?></p>
                                            <p><strong>Coupon:</strong> <?php echo htmlspecialchars($payment['coupon_code']); ?></p>
                                        <?php endif; ?>
                                        <p class="fw-bold"><strong>Total Paid:</strong> $<?php echo number_format($payment['final_amount'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="course_detail.php?course_id=<?php echo $payment['course_id']; ?>" class="btn btn-primary me-2">
                                <i class="fas fa-graduation-cap"></i> Start Learning
                            </a>
                            <a href="student/my_courses.php" class="btn btn-outline-primary">
                                <i class="fas fa-book"></i> My Courses
                            </a>
                        </div>
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