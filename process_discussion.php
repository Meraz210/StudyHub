<?php
require_once 'includes/config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['course_id'], $_POST['title'], $_POST['content'])) {
    redirectWithMessage('courses.php', 'Invalid request', 'danger');
}

$courseId = (int)$_POST['course_id'];
$title = sanitizeInput($_POST['title']);
$content = sanitizeInput($_POST['content']);
$userId = getCurrentUserId();

// Check if user is enrolled in the course
if (!isEnrolled($userId, $courseId)) {
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'You must be enrolled in the course to post discussions', 'danger');
}

try {
    // Insert discussion
    $stmt = $pdo->prepare("INSERT INTO discussions (course_id, user_id, title, content, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$courseId, $userId, $title, $content]);
    
    if ($result) {
        redirectWithMessage('discussions.php?course_id=' . $courseId, 'Discussion posted successfully!', 'success');
    } else {
        redirectWithMessage('discussions.php?course_id=' . $courseId, 'Error posting discussion', 'danger');
    }
    
} catch (Exception $e) {
    redirectWithMessage('discussions.php?course_id=' . $courseId, 'Error posting discussion: ' . $e->getMessage(), 'danger');
}
?>