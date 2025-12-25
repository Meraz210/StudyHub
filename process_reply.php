<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['discussion_id'], $_POST['reply_content'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$discussionId = (int)$_POST['discussion_id'];
$replyContent = sanitizeInput($_POST['reply_content']);
$userId = getCurrentUserId();

try {
    // Insert reply
    $stmt = $pdo->prepare("INSERT INTO replies (discussion_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$discussionId, $userId, $replyContent]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reply posted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error posting reply']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error posting reply: ' . $e->getMessage()]);
}
?>