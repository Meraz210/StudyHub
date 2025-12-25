<?php
require_once 'includes/config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['lesson_id'], $_POST['course_id'])) {
    redirectWithMessage('index.php', 'Invalid request', 'danger');
}

$lessonId = (int)$_POST['lesson_id'];
$courseId = (int)$_POST['course_id'];
$userId = getCurrentUserId();

// Check if user is enrolled in the course
if (!isEnrolled($userId, $courseId)) {
    redirectWithMessage('index.php', 'You are not enrolled in this course', 'danger');
}

try {
    // Mark lesson as completed
    $isCourseCompleted = markLessonCompleted($userId, $lessonId, $courseId);
    
    if ($isCourseCompleted) {
        redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Congratulations! You have completed the course!', 'success');
    } else {
        redirectWithMessage('lesson.php?lesson_id=' . $lessonId, 'Lesson marked as completed!', 'success');
    }
    
} catch (Exception $e) {
    redirectWithMessage('lesson.php?lesson_id=' . $lessonId, 'Error marking lesson as complete: ' . $e->getMessage(), 'danger');
}
?>