<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['quiz_id'])) {
    redirectWithMessage('index.php', 'Invalid request', 'danger');
}

$quizId = (int)$_POST['quiz_id'];
$userId = getCurrentUserId();

// Get quiz details
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    redirectWithMessage('index.php', 'Quiz not found', 'danger');
}

// Check if user is enrolled in the course
if (!isEnrolled($userId, $quiz['course_id'])) {
    redirectWithMessage('index.php', 'You are not enrolled in this course', 'danger');
}

// Check if user has already taken this quiz
if (hasTakenQuiz($userId, $quizId)) {
    redirectWithMessage('quiz.php?quiz_id=' . $quizId, 'You have already taken this quiz', 'info');
}

// Get quiz questions
$questions = getQuizQuestions($quizId);

// Calculate score
$correctAnswers = 0;
$totalPoints = 0;
$maxPoints = 0;

foreach ($questions as $question) {
    $maxPoints += $question['points'];
    
    $questionId = $question['question_id'];
    $userAnswer = isset($_POST['question_' . $questionId]) ? $_POST['question_' . $questionId] : null;
    $correctAnswer = $question['correct_answer'];
    
    if ($userAnswer && $userAnswer === $correctAnswer) {
        $correctAnswers++;
        $totalPoints += $question['points'];
    }
}

// Calculate percentage score
$percentageScore = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 2) : 0;

// Get time taken (if provided)
$timeTaken = isset($_POST['time_taken']) ? (int)$_POST['time_taken'] : 0;

try {
    // Submit quiz result
    $result = submitQuizResult($userId, $quizId, $percentageScore, $totalPoints, $maxPoints, $timeTaken);
    
    if ($result) {
        $message = 'Quiz submitted successfully! Your score: ' . $percentageScore . '%';
        $messageType = $percentageScore >= $quiz['passing_score'] ? 'success' : 'info';
        
        if ($percentageScore >= $quiz['passing_score']) {
            $message .= ' You passed the quiz!';
        } else {
            $message .= ' You need at least ' . $quiz['passing_score'] . '% to pass.';
        }
        
        redirectWithMessage('quiz.php?quiz_id=' . $quizId, $message, $messageType);
    } else {
        redirectWithMessage('quiz.php?quiz_id=' . $quizId, 'Error submitting quiz', 'danger');
    }
    
} catch (Exception $e) {
    redirectWithMessage('quiz.php?quiz_id=' . $quizId, 'Error submitting quiz: ' . $e->getMessage(), 'danger');
}
?>