<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['course_id']) || !isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$courseId = (int)$_GET['course_id'];
$userId = getCurrentUserId();

// Check if user is enrolled and has completed the course
$enrollment = getUserEnrollment($userId, $courseId);

if (!$enrollment || !$enrollment['is_completed']) {
    redirectWithMessage('student/my_courses.php', 'You must complete the course to generate a certificate', 'danger');
}

// Get course and user details
$course = getCourseById($courseId);
$user = $pdo->query("SELECT * FROM users WHERE user_id = $userId")->fetch(PDO::FETCH_ASSOC);

if (!$course || !$user) {
    header('Location: index.php');
    exit();
}

// Check if certificate already exists
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE user_id = ? AND course_id = ?");
$stmt->execute([$userId, $courseId]);
$existingCertificate = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingCertificate) {
    // Redirect to view existing certificate
    header('Location: view_certificate.php?cert_id=' . $existingCertificate['certificate_id']);
    exit();
}

// Generate certificate code
$certificateCode = generateCertificateCode($userId, $courseId);

// Create certificate record
$stmt = $pdo->prepare("INSERT INTO certificates (user_id, course_id, certificate_code, issue_date, file_path) VALUES (?, ?, ?, NOW(), ?)");
$stmt->execute([$userId, $courseId, $certificateCode, "certificates/" . $certificateCode . ".pdf"]);

// Get the new certificate ID
$certificateId = $pdo->lastInsertId();

// Redirect to certificate generation
header('Location: view_certificate.php?cert_id=' . $certificateId);
exit();
?>