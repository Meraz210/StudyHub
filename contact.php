<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn()) {
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    $priority = sanitizeInput($_POST['priority']);
    $userId = getCurrentUserId();
    
    if (empty($subject) || empty($message)) {
        $error = 'Subject and message are required.';
    } else {
        try {
            // Insert support ticket
            $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message, priority, status) VALUES (?, ?, ?, ?, 'open')");
            $result = $stmt->execute([$userId, $subject, $message, $priority]);
            
            if ($result) {
                $success = 'Your support ticket has been submitted successfully. Our team will get back to you soon.';
            } else {
                $error = 'Error submitting support ticket. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error submitting support ticket: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - StudyHub</title>
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
                        <a class="nav-link active" href="contact.php">Contact</a>
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
                <h1>Contact Support</h1>
                <p class="text-muted">Have questions or need assistance? Our support team is here to help.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Submit a Support Ticket</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Submit Ticket</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- User's Support Tickets -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>My Support Tickets</h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $userTickets = getSupportTickets(getCurrentUserId());
                            if (count($userTickets) > 0):
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Ticket ID</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userTickets as $ticket): ?>
                                                <tr>
                                                    <td>#<?php echo $ticket['ticket_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                    <td>
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
                                                    </td>
                                                    <td>
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
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                                    <td>
                                                        <a href="view_ticket.php?ticket_id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">You haven't submitted any support tickets yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-lock fa-5x text-muted mb-3"></i>
                            <h5>Please Log In</h5>
                            <p class="text-muted">You need to be logged in to submit a support ticket.</p>
                            <a href="login.php" class="btn btn-primary">Login</a>
                            <a href="register.php" class="btn btn-outline-primary">Register</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <strong>Email:</strong><br>
                                <a href="mailto:support@studyhub.com">support@studyhub.com</a>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                <strong>Phone:</strong><br>
                                (123) 456-7890
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-clock me-2 text-primary"></i>
                                <strong>Hours:</strong><br>
                                Monday - Friday: 9am - 6pm EST
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Quick Links</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="faq.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-question-circle me-2"></i> Frequently Asked Questions
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2"></i> Knowledge Base
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-video me-2"></i> Video Tutorials
                        </a>
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