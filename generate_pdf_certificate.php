<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['cert_id']) || !isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$certId = (int)$_GET['cert_id'];

// Get certificate details
$stmt = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name, u.username, 
           course.title as course_title, course.instructor_id,
           instructor.first_name as instructor_first_name,
           instructor.last_name as instructor_last_name
    FROM certificates c
    JOIN users u ON c.user_id = u.user_id
    JOIN courses course ON c.course_id = course.course_id
    JOIN users instructor ON course.instructor_id = instructor.user_id
    WHERE c.certificate_id = ?
");
$stmt->execute([$certId]);
$certificate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certificate) {
    header('Location: index.php');
    exit();
}

// Check if the current user is the certificate owner
if ($certificate['user_id'] != getCurrentUserId()) {
    header('Location: index.php');
    exit();
}

// Since TCPDF is not available, redirect to the HTML certificate view
header('Location: view_certificate.php?cert_id=' . $certId);
exit();

exit();
?>