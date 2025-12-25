<?php
require_once 'includes/config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['course_id'])) {
    redirectWithMessage('courses.php', 'Invalid request', 'danger');
}

$courseId = (int)$_POST['course_id'];
$userId = getCurrentUserId();

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    redirectWithMessage('courses.php', 'Course not found', 'danger');
}

// Check if user is already enrolled
if (isEnrolled($userId, $courseId)) {
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'You are already enrolled in this course', 'info');
}

// Get payment details
$amount = $course['price'];
$paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'demo');
$transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);
$couponCode = sanitizeInput($_POST['coupon_code'] ?? '');
$discountAmount = 0;

// Check if coupon is valid
if (!empty($couponCode)) {
    // In a real system, you would validate the coupon
    // For demo purposes, we'll just apply a 10% discount if the code is 'SAVE10'
    if ($couponCode === 'SAVE10') {
        $discountAmount = $amount * 0.10;
    }
}

$finalAmount = $amount - $discountAmount;

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert payment record
    $stmt = $pdo->prepare("
        INSERT INTO payments (user_id, course_id, amount, payment_method, transaction_id, coupon_code, discount_amount, final_amount, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed')
    ");
    $stmt->execute([$userId, $courseId, $amount, $paymentMethod, $transactionId, $couponCode, $discountAmount, $finalAmount]);
    
    // Insert enrollment record
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, enrollment_date) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $courseId]);
    
    // Commit transaction
    $pdo->commit();
    
    redirectWithMessage('payment_success.php?payment_id=' . $pdo->lastInsertId(), 'Payment completed successfully! You are now enrolled in the course.', 'success');
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollback();
    redirectWithMessage('course_detail.php?course_id=' . $courseId, 'Error processing payment: ' . $e->getMessage(), 'danger');
}
?>