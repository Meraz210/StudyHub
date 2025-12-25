<?php
// Utility functions for the StudyHub platform

// Function to check if user is enrolled in a course
function isEnrolled($userId, $courseId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetchColumn() > 0;
}

// Function to get user's course progress
function getCourseProgress($userId, $courseId) {
    global $pdo;
    
    // Get total lessons in the course
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $totalLessons = $stmt->fetchColumn();
    
    if ($totalLessons == 0) {
        return 0;
    }
    
    // Get completed lessons for the user in this course
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM progress 
        WHERE user_id = ? AND course_id = ? AND is_completed = 1
    ");
    $stmt->execute([$userId, $courseId]);
    $completedLessons = $stmt->fetchColumn();
    
    // Calculate percentage
    return round(($completedLessons / $totalLessons) * 100, 2);
}

// Function to get course average rating
function getCourseAverageRating($courseId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $avgRating = $stmt->fetchColumn();
    return $avgRating ? round($avgRating, 1) : 0;
}

// Function to get course review count
function getCourseReviewCount($courseId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE course_id = ?");
    $stmt->execute([$courseId]);
    return $stmt->fetchColumn();
}

// Function to get user's role
function getUserRole($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['role'] : null;
}

// Function to get all categories
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get courses by category
function getCoursesByCategory($categoryId = null) {
    global $pdo;
    
    if ($categoryId) {
        $stmt = $pdo->prepare("
            SELECT c.*, u.first_name, u.last_name, 
                   (SELECT AVG(rating) FROM reviews WHERE course_id = c.course_id) as avg_rating,
                   (SELECT COUNT(*) FROM reviews WHERE course_id = c.course_id) as review_count
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.user_id
            WHERE c.category_id = ? AND c.is_published = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$categoryId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT c.*, u.first_name, u.last_name, 
                   (SELECT AVG(rating) FROM reviews WHERE course_id = c.course_id) as avg_rating,
                   (SELECT COUNT(*) FROM reviews WHERE course_id = c.course_id) as review_count
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.user_id
            WHERE c.is_published = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get a single course by ID
function getCourseById($courseId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, u.first_name, u.last_name, u.bio as instructor_bio,
               (SELECT AVG(rating) FROM reviews WHERE course_id = c.course_id) as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE course_id = c.course_id) as review_count
        FROM courses c
        JOIN users u ON c.instructor_id = u.user_id
        WHERE c.course_id = ?
    ");
    $stmt->execute([$courseId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get lessons for a course
function getLessonsForCourse($courseId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY lesson_order");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get user's enrollment status for a course
function getUserEnrollment($userId, $courseId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get user's progress for a specific lesson
function getUserLessonProgress($userId, $lessonId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$userId, $lessonId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to mark lesson as completed
function markLessonCompleted($userId, $lessonId, $courseId) {
    global $pdo;
    
    // Check if progress record exists
    $stmt = $pdo->prepare("SELECT progress_id FROM progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$userId, $lessonId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($progress) {
        // Update existing record
        $stmt = $pdo->prepare("UPDATE progress SET is_completed = 1, completed_at = NOW() WHERE progress_id = ?");
        $stmt->execute([$progress['progress_id']]);
    } else {
        // Insert new record
        $stmt = $pdo->prepare("INSERT INTO progress (user_id, lesson_id, course_id, is_completed, completed_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute([$userId, $lessonId, $courseId]);
    }
    
    // Update course completion percentage
    $completion = getCourseProgress($userId, $courseId);
    $stmt = $pdo->prepare("UPDATE enrollments SET completion_percentage = ? WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$completion, $userId, $courseId]);
    
    // Check if course is completed (100%)
    if ($completion >= 100) {
        $stmt = $pdo->prepare("UPDATE enrollments SET is_completed = 1, completed_at = NOW() WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        return true; // Course completed
    }
    
    return false; // Course not completed yet
}

// Function to get quizzes for a course
function getQuizzesForCourse($courseId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY created_at");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get quiz questions
function getQuizQuestions($quizId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order");
    $stmt->execute([$quizId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to submit quiz result
function submitQuizResult($userId, $quizId, $score, $totalPoints, $maxPoints, $timeTaken) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO quiz_results (user_id, quiz_id, score, total_points, max_points, time_taken) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$userId, $quizId, $score, $totalPoints, $maxPoints, $timeTaken]);
}

// Function to check if user has taken a quiz
function hasTakenQuiz($userId, $quizId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$userId, $quizId]);
    return $stmt->fetchColumn() > 0;
}

// Function to get user's quiz result
function getUserQuizResult($userId, $quizId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
    $stmt->execute([$userId, $quizId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get course discussions
function getDiscussionsForCourse($courseId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT d.*, u.first_name, u.last_name, u.profile_image
        FROM discussions d
        JOIN users u ON d.user_id = u.user_id
        WHERE d.course_id = ?
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get replies for a discussion
function getRepliesForDiscussion($discussionId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, u.first_name, u.last_name, u.profile_image, u.role
        FROM replies r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.discussion_id = ? AND r.parent_reply_id IS NULL
        ORDER BY r.created_at ASC
    ");
    $stmt->execute([$discussionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get nested replies
function getNestedReplies($parentReplyId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, u.first_name, u.last_name, u.profile_image, u.role
        FROM replies r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.parent_reply_id = ?
        ORDER BY r.created_at ASC
    ");
    $stmt->execute([$parentReplyId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all reviews for a course
function getReviewsForCourse($courseId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, u.first_name, u.last_name, u.profile_image
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.course_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to check if user has reviewed a course
function hasReviewedCourse($userId, $courseId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetchColumn() > 0;
}

// Function to get user's enrolled courses
function getUserEnrolledCourses($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, e.completion_percentage, e.enrollment_date, e.is_completed,
               (SELECT AVG(rating) FROM reviews WHERE course_id = c.course_id) as avg_rating
        FROM courses c
        JOIN enrollments e ON c.course_id = e.course_id
        WHERE e.user_id = ?
        ORDER BY e.enrollment_date DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get instructor's courses
function getInstructorCourses($instructorId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, 
               (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as student_count,
               (SELECT AVG(rating) FROM reviews WHERE course_id = c.course_id) as avg_rating
        FROM courses c
        WHERE c.instructor_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$instructorId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all users by role
function getUsersByRole($role) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
    $stmt->execute([$role]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all FAQs
function getFaqs($category = null) {
    global $pdo;
    
    if ($category) {
        $stmt = $pdo->prepare("SELECT * FROM faqs WHERE category = ? AND is_active = 1 ORDER BY question");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM faqs WHERE is_active = 1 ORDER BY question");
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get support tickets
function getSupportTickets($userId = null) {
    global $pdo;
    
    if ($userId) {
        $stmt = $pdo->prepare("
            SELECT st.*, u.first_name, u.last_name
            FROM support_tickets st
            JOIN users u ON st.user_id = u.user_id
            WHERE st.user_id = ?
            ORDER BY st.created_at DESC
        ");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT st.*, u.first_name, u.last_name
            FROM support_tickets st
            JOIN users u ON st.user_id = u.user_id
            ORDER BY st.created_at DESC
        ");
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get certificate for user and course
function getCertificate($userId, $courseId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT cert.*, c.title as course_title, u.first_name, u.last_name
        FROM certificates cert
        JOIN courses c ON cert.course_id = c.course_id
        JOIN users u ON cert.user_id = u.user_id
        WHERE cert.user_id = ? AND cert.course_id = ?
    ");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>