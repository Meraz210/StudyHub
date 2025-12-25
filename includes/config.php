<?php
// Application Configuration
session_start();

// Include database connection
require_once 'db_connection.php';

// Include utility functions
require_once 'functions.php';

// Base URL
define('BASE_URL', 'http://localhost/StudyHub');

// Site name
define('SITE_NAME', 'StudyHub');

// Roles
define('ROLE_STUDENT', 'student');
define('ROLE_INSTRUCTOR', 'instructor');
define('ROLE_ADMIN', 'admin');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current user role
function getCurrentUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Check user role
function hasRole($role) {
    return getCurrentUserRole() === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

// Redirect if not authorized
function requireRole($role) {
    if (!hasRole($role)) {
        header('Location: ../index.php');
        exit();
    }
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Generate unique certificate code
function generateCertificateCode($userId, $courseId) {
    return strtoupper('CERT-' . $userId . '-' . $courseId . '-' . time() . '-' . rand(1000, 9999));
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}
?>