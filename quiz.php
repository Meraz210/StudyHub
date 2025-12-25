<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['quiz_id'])) {
    header('Location: index.php');
    exit();
}

$quizId = (int)$_GET['quiz_id'];

// Get quiz details
$stmt = $pdo->prepare("
    SELECT q.*, c.title as course_title, c.course_id
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    WHERE q.quiz_id = ?
");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    header('Location: index.php');
    exit();
}

// Check if user is enrolled in the course
$isEnrolled = false;
if (isLoggedIn()) {
    $isEnrolled = isEnrolled(getCurrentUserId(), $quiz['course_id']);
}

if (!$isEnrolled) {
    header('Location: course_detail.php?course_id=' . $quiz['course_id']);
    exit();
}

// Get quiz questions
$questions = getQuizQuestions($quizId);

// Check if user has already taken this quiz
$userQuizResult = null;
if (isLoggedIn()) {
    $userQuizResult = getUserQuizResult(getCurrentUserId(), $quizId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - StudyHub</title>
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
                        <li class="breadcrumb-item"><a href="course_detail.php?course_id=<?php echo $quiz['course_id']; ?>"><?php echo htmlspecialchars($quiz['course_title']); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($quiz['title']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <?php if ($userQuizResult): ?>
            <!-- Show quiz results if already taken -->
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h4>Quiz Results</h4>
                        </div>
                        <div class="card-body text-center">
                            <h1 class="display-1 text-<?php echo $userQuizResult['score'] >= $quiz['passing_score'] ? 'success' : 'danger'; ?>">
                                <?php echo number_format($userQuizResult['score'], 1); ?>%
                            </h1>
                            <p class="lead">
                                <?php if ($userQuizResult['score'] >= $quiz['passing_score']): ?>
                                    <i class="fas fa-check-circle text-success"></i> Congratulations! You passed the quiz.
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger"></i> You need to score at least <?php echo $quiz['passing_score']; ?>% to pass.
                                <?php endif; ?>
                            </p>
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <h5>Score</h5>
                                    <p><?php echo $userQuizResult['total_points']; ?>/<?php echo $userQuizResult['max_points']; ?> points</p>
                                </div>
                                <div class="col-md-4">
                                    <h5>Time Taken</h5>
                                    <p><?php echo gmdate('H:i:s', $userQuizResult['time_taken']); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h5>Date</h5>
                                    <p><?php echo date('F j, Y', strtotime($userQuizResult['completed_at'])); ?></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="quiz.php?quiz_id=<?php echo $quizId; ?>&retake=1" class="btn btn-primary">Retake Quiz</a>
                                <a href="course_detail.php?course_id=<?php echo $quiz['course_id']; ?>" class="btn btn-secondary">Back to Course</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Quiz Form -->
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                            <?php if ($quiz['time_limit']): ?>
                                <div class="quiz-timer" id="quiz-timer">
                                    <i class="fas fa-clock"></i> <span id="timer">00:00</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <p class="text-muted">
                                <i class="fas fa-question-circle"></i> <?php echo $quiz['total_questions']; ?> questions
                                <?php if ($quiz['time_limit']): ?>
                                    | <i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?> minutes
                                <?php endif; ?>
                                | <i class="fas fa-check-circle"></i> <?php echo $quiz['passing_score']; ?>% to pass
                            </p>
                            
                            <form id="quiz-form" method="POST" action="process_quiz.php">
                                <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
                                <input type="hidden" name="start_time" id="start_time" value="<?php echo time(); ?>">
                                
                                <?php foreach ($questions as $index => $question): ?>
                                    <div class="quiz-question">
                                        <h5><?php echo ($index + 1); ?>. <?php echo htmlspecialchars($question['question_text']); ?></h5>
                                        
                                        <?php 
                                        $options = json_decode($question['options'], true);
                                        if ($options && is_array($options)):
                                        ?>
                                            <?php foreach ($options as $optionIndex => $option): ?>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="question_<?php echo $question['question_id']; ?>" id="q<?php echo $question['question_id']; ?>_o<?php echo $optionIndex; ?>" value="<?php echo $option; ?>">
                                                    <label class="form-check-label" for="q<?php echo $question['question_id']; ?>_o<?php echo $optionIndex; ?>">
                                                        <?php echo htmlspecialchars($option); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg">Submit Quiz</button>
                                    <a href="course_detail.php?course_id=<?php echo $quiz['course_id']; ?>" class="btn btn-secondary">Back to Course</a>
                                </div>
                            </form>
                        </div>
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
        <?php if ($quiz['time_limit'] && !$userQuizResult): ?>
        // Quiz timer functionality
        let timeLimit = <?php echo $quiz['time_limit'] * 60; ?>; // Convert to seconds
        let timeRemaining = timeLimit;
        let timerInterval;
        
        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return minutes.toString().padStart(2, '0') + ':' + remainingSeconds.toString().padStart(2, '0');
        }
        
        function updateTimer() {
            document.getElementById('timer').textContent = formatTime(timeRemaining);
            
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                // Auto-submit the quiz when time runs out
                document.getElementById('quiz-form').submit();
            }
            
            timeRemaining--;
        }
        
        // Start the timer
        timerInterval = setInterval(updateTimer, 1000);
        updateTimer(); // Initial call to show the starting time
        
        // Add time taken to form when submitted
        document.getElementById('quiz-form').addEventListener('submit', function() {
            clearInterval(timerInterval);
            const endTime = Math.floor(Date.now() / 1000);
            const startTime = document.getElementById('start_time').value;
            const timeTaken = endTime - parseInt(startTime);
            
            // Add time taken to form as hidden input
            const timeTakenInput = document.createElement('input');
            timeTakenInput.type = 'hidden';
            timeTakenInput.name = 'time_taken';
            timeTakenInput.value = timeTaken;
            this.appendChild(timeTakenInput);
        });
        <?php endif; ?>
    </script>
</body>
</html>