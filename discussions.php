<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['course_id'])) {
    header('Location: courses.php');
    exit();
}

$courseId = (int)$_GET['course_id'];

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: courses.php');
    exit();
}

// Check if user is enrolled in the course
$isEnrolled = false;
if (isLoggedIn()) {
    $isEnrolled = isEnrolled(getCurrentUserId(), $courseId);
}

// Get discussions for the course
$discussions = getDiscussionsForCourse($courseId);

// Get instructor details
$stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$stmt->execute([$course['instructor_id']]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussions - <?php echo htmlspecialchars($course['title']); ?> - StudyHub</title>
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
                        <a class="nav-link" href="course_detail.php?course_id=<?php echo $courseId; ?>"><?php echo htmlspecialchars($course['title']); ?></a>
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
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                        <li class="breadcrumb-item"><a href="course_detail.php?course_id=<?php echo $courseId; ?>"><?php echo htmlspecialchars($course['title']); ?></a></li>
                        <li class="breadcrumb-item active">Discussions</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Course Discussions</h2>
                    <?php if ($isEnrolled): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDiscussionModal">
                            <i class="fas fa-plus"></i> New Discussion
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if (count($discussions) > 0): ?>
                    <?php foreach ($discussions as $discussion): ?>
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($discussion['first_name'] . ' ' . $discussion['last_name']); ?></strong>
                                    <span class="text-muted ms-2"><?php echo date('M j, Y g:i A', strtotime($discussion['created_at'])); ?></span>
                                    <?php if ($discussion['is_resolved']): ?>
                                        <span class="badge bg-success ms-2">Resolved</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isEnrolled): ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="showReplies(<?php echo $discussion['discussion_id']; ?>)">
                                        <i class="fas fa-comments"></i> View Replies
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($discussion['title']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($discussion['content'])); ?></p>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-comment"></i> 
                                            <?php 
                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM replies WHERE discussion_id = ?");
                                            $stmt->execute([$discussion['discussion_id']]);
                                            echo $stmt->fetchColumn();
                                            ?> replies
                                        </small>
                                    </div>
                                    <?php if ($isEnrolled): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="replyToDiscussion(<?php echo $discussion['discussion_id']; ?>)">
                                            <i class="fas fa-reply"></i> Reply
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Replies section (initially hidden) -->
                            <div id="replies-<?php echo $discussion['discussion_id']; ?>" class="replies-section" style="display: none;">
                                <div class="card-body border-top">
                                    <h6>Replies:</h6>
                                    <div id="replies-content-<?php echo $discussion['discussion_id']; ?>">
                                        <!-- Replies will be loaded here via AJAX -->
                                    </div>
                                    
                                    <?php if ($isEnrolled): ?>
                                        <form class="mt-3" id="reply-form-<?php echo $discussion['discussion_id']; ?>">
                                            <input type="hidden" name="discussion_id" value="<?php echo $discussion['discussion_id']; ?>">
                                            <div class="mb-3">
                                                <textarea class="form-control" name="reply_content" rows="3" placeholder="Write your reply..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Post Reply</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-5x text-muted mb-3"></i>
                        <h4>No discussions yet</h4>
                        <p class="text-muted">Be the first to start a discussion in this course!</p>
                        <?php if ($isEnrolled): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDiscussionModal">
                                <i class="fas fa-plus"></i> Start a Discussion
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>About This Course</h5>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($course['title']); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        <p class="text-muted">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                        </p>
                        <a href="course_detail.php?course_id=<?php echo $courseId; ?>" class="btn btn-sm btn-outline-primary">View Course</a>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Course Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span>Discussions:</span>
                            <strong>
                                <?php 
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM discussions WHERE course_id = ?");
                                $stmt->execute([$courseId]);
                                echo $stmt->fetchColumn();
                                ?>
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Replies:</span>
                            <strong>
                                <?php 
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM replies WHERE discussion_id IN (SELECT discussion_id FROM discussions WHERE course_id = ?)");
                                $stmt->execute([$courseId]);
                                echo $stmt->fetchColumn();
                                ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Discussion Modal -->
    <?php if ($isEnrolled): ?>
    <div class="modal fade" id="newDiscussionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Start a New Discussion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="process_discussion.php">
                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="discussion_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="discussion_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="discussion_content" class="form-label">Content</label>
                            <textarea class="form-control" id="discussion_content" name="content" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Post Discussion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
    <script>
        // Function to show/hide replies
        function showReplies(discussionId) {
            const repliesSection = document.getElementById(`replies-${discussionId}`);
            const repliesContent = document.getElementById(`replies-content-${discussionId}`);
            
            if (repliesSection.style.display === 'none') {
                // Load replies via AJAX
                fetch(`get_replies.php?discussion_id=${discussionId}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.length > 0) {
                            data.forEach(reply => {
                                html += `
                                    <div class="reply-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <strong>${reply.first_name} ${reply.last_name}</strong>
                                            <small class="text-muted">${reply.created_at}</small>
                                        </div>
                                        <p class="mb-1">${reply.content}</p>
                                        ${reply.is_best_answer ? '<span class="badge bg-success">Best Answer</span>' : ''}
                                    </div>
                                `;
                            });
                        } else {
                            html = '<p class="text-muted">No replies yet.</p>';
                        }
                        repliesContent.innerHTML = html;
                        repliesSection.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error loading replies:', error);
                        repliesContent.innerHTML = '<p class="text-danger">Error loading replies.</p>';
                        repliesSection.style.display = 'block';
                    });
            } else {
                repliesSection.style.display = 'none';
            }
        }
        
        // Function to handle replying to a discussion
        function replyToDiscussion(discussionId) {
            showReplies(discussionId);
            
            // Scroll to the reply form
            const replyForm = document.getElementById(`reply-form-${discussionId}`);
            replyForm.scrollIntoView({ behavior: 'smooth' });
        }
        
        // Handle reply form submission
        document.querySelectorAll('[id^="reply-form-"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const discussionId = formData.get('discussion_id');
                
                fetch('process_reply.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reset form and reload replies
                        this.reset();
                        showReplies(discussionId);
                    } else {
                        alert('Error posting reply: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error posting reply');
                });
            });
        });
    </script>
</body>
</html>