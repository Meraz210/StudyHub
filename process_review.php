<?php
require_once 'includes/config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['course_id'], $_POST['rating'], $_POST['review_text'])) {
    redirectWithMessage('index.php', 'Invalid request', 'danger');
}

$courseId = (int)$_POST['course_id'];
$rating = (int)$_POST['rating'];
$reviewText = sanitizeInput($_POST['review_text']);
$userId = getCurrentUserId();

// Validate rating
if ($rating < 1 || $rating > 5) {
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Invalid rating', 'danger');
}

// Check if user is enrolled in the course
if (!isEnrolled($userId, $courseId)) {
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'You must be enrolled in the course to review it', 'danger');
}

// Check if user has already reviewed this course
if (hasReviewedCourse($userId, $courseId)) {
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'You have already reviewed this course', 'info');
}

try {
    // Insert review
    $stmt = $pdo->prepare("INSERT INTO reviews (course_id, user_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$courseId, $userId, $rating, $reviewText]);
    
    if ($result) {
        redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Review submitted successfully!', 'success');
    } else {
        redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Error submitting review', 'danger');
    }
    
} catch (Exception $e) {
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Error submitting review: ' . $e->getMessage(), 'danger');
}
?>