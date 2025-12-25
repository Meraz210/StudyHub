<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['ticket_id']) || !isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$ticketId = (int)$_GET['ticket_id'];
$userId = getCurrentUserId();

// Get ticket details
$stmt = $pdo->prepare("
    SELECT st.*, u.first_name, u.last_name
    FROM support_tickets st
    JOIN users u ON st.user_id = u.user_id
    WHERE st.ticket_id = ? AND st.user_id = ?
");
$stmt->execute([$ticketId, $userId]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $ticket['ticket_id']; ?> - StudyHub</title>
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
                        <a class="nav-link" href="faq.php">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
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
                        <li class="breadcrumb-item"><a href="contact.php">Contact</a></li>
                        <li class="breadcrumb-item active">Ticket #<?php echo $ticket['ticket_id']; ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Ticket #<?php echo $ticket['ticket_id']; ?></h5>
                        <div>
                            <span class="badge 
                                <?php 
                                    if ($ticket['status'] === 'open') echo 'bg-warning';
                                    elseif ($ticket['status'] === 'in_progress') echo 'bg-info';
                                    elseif ($ticket['status'] === 'resolved') echo 'bg-success';
                                    else echo 'bg-secondary';
                                ?>
                            ">
                                <?php echo ucfirst($ticket['status']); ?>
                            </span>
                            <span class="badge 
                                <?php 
                                    if ($ticket['priority'] === 'low') echo 'bg-secondary';
                                    elseif ($ticket['priority'] === 'medium') echo 'bg-primary';
                                    elseif ($ticket['priority'] === 'high') echo 'bg-warning';
                                    else echo 'bg-danger';
                                ?>
                            ">
                                <?php echo ucfirst($ticket['priority']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($ticket['subject']); ?></h6>
                        <div class="d-flex justify-content-between text-muted mb-3">
                            <small>
                                Created by: <?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?>
                            </small>
                            <small>
                                <?php echo date('F j, Y g:i A', strtotime($ticket['created_at'])); ?>
                            </small>
                        </div>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
                    </div>
                </div>
                
                <!-- Replies Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Replies</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">No replies yet.</p>
                        <!-- In a real implementation, you would fetch replies from the support_replies table -->
                    </div>
                </div>
                
                <!-- Add Reply Form (for admin only) -->
                <?php if (getCurrentUserRole() === 'admin'): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Add Reply</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <textarea class="form-control" rows="4" placeholder="Type your reply here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Reply</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Ticket Details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong>Ticket ID:</strong><br>
                                #<?php echo $ticket['ticket_id']; ?>
                            </li>
                            <li class="mb-2">
                                <strong>Status:</strong><br>
                                <span class="badge 
                                    <?php 
                                        if ($ticket['status'] === 'open') echo 'bg-warning';
                                        elseif ($ticket['status'] === 'in_progress') echo 'bg-info';
                                        elseif ($ticket['status'] === 'resolved') echo 'bg-success';
                                        else echo 'bg-secondary';
                                    ?>
                                ">
                                    <?php echo ucfirst($ticket['status']); ?>
                                </span>
                            </li>
                            <li class="mb-2">
                                <strong>Priority:</strong><br>
                                <span class="badge 
                                    <?php 
                                        if ($ticket['priority'] === 'low') echo 'bg-secondary';
                                        elseif ($ticket['priority'] === 'medium') echo 'bg-primary';
                                        elseif ($ticket['priority'] === 'high') echo 'bg-warning';
                                        else echo 'bg-danger';
                                    ?>
                                ">
                                    <?php echo ucfirst($ticket['priority']); ?>
                                </span>
                            </li>
                            <li class="mb-2">
                                <strong>Created:</strong><br>
                                <?php echo date('F j, Y g:i A', strtotime($ticket['created_at'])); ?>
                            </li>
                            <li class="mb-2">
                                <strong>Last Updated:</strong><br>
                                <?php echo date('F j, Y g:i A', strtotime($ticket['updated_at'])); ?>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-danger w-100 mb-2">Close Ticket</button>
                        <a href="contact.php" class="btn btn-outline-primary w-100">Back to Tickets</a>
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
                        <li><a href="faq.php" class="text-white">FAQ</a></li>
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