<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['discussion_id'])) {
    echo json_encode([]);
    exit();
}

$discussionId = (int)$_GET['discussion_id'];

try {
    $replies = getRepliesForDiscussion($discussionId);
    
    // Format replies for JSON response
    $formattedReplies = [];
    foreach ($replies as $reply) {
        $formattedReplies[] = [
            'reply_id' => $reply['reply_id'],
            'first_name' => $reply['first_name'],
            'last_name' => $reply['last_name'],
            'content' => $reply['content'],
            'created_at' => date('M j, Y g:i A', strtotime($reply['created_at'])),
            'is_best_answer' => $reply['is_best_answer']
        ];
    }
    
    echo json_encode($formattedReplies);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>