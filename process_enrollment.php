<?php
require_once 'includes/config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['course_id'])) {
    redirectWithMessage('courses.php', 'Invalid request', 'danger');
}

$courseId = (int)$_POST['course_id'];

// Check if course exists
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    redirectWithMessage('courses.php', 'Course not found', 'danger');
}

$userId = getCurrentUserId();

// Check if user is already enrolled
if (isEnrolled($userId, $courseId)) {
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'You are already enrolled in this course', 'info');
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert enrollment record
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, enrollment_date) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $courseId]);
    
    // If course is free, mark as completed immediately (for demo purposes)
    if ($course['price'] <= 0) {
        // Get total lessons to calculate completion percentage
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        $totalLessons = $stmt->fetchColumn();
        
        if ($totalLessons == 0) {
            // Mark as completed if no lessons
            $stmt = $pdo->prepare("UPDATE enrollments SET is_completed = 1, completed_at = NOW() WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$userId, $courseId]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Successfully enrolled in the course!', 'success');
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollback();
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Error enrolling in course: ' . $e->getMessage(), 'danger');
}
?>