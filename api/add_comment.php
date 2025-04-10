<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }


    $post_id = $_POST['post_id'] ?? null;
    $content = $_POST['content'] ?? null;

    if (!$post_id) {
        throw new Exception('Post ID is required');
    }

    if (!$content || trim($content) === '') {
        throw new Exception('Comment content is required');
    }


    if (!isset($_SESSION['user_id'])) {
        $user_id = 1;
        

        $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkUser->execute([$user_id]);
        if (!$checkUser->fetch()) {

            $createUser = $pdo->prepare("INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)");
            $createUser->execute([1, 'default_user', password_hash('password', PASSWORD_DEFAULT)]);
        }
    } else {
        $user_id = $_SESSION['user_id'];
    }


    $checkPost = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
    $checkPost->execute([$post_id]);
    if (!$checkPost->fetch()) {
        throw new Exception('Post not found');
    }


    $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $post_id, $content]);
    $comment_id = $pdo->lastInsertId();


    $userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $userStmt->execute([$user_id]);
    $username = $userStmt->fetch()['username'] ?? 'Anonymous';


    $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
    $countStmt->execute([$post_id]);
    $commentCount = $countStmt->fetch()['count'];

    echo json_encode([
        'success' => true,
        'comment_id' => $comment_id,
        'username' => $username,
        'content' => $content,
        'comment_count' => $commentCount,
        'message' => 'Comment added successfully'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'post' => $_POST,
            'session' => isset($_SESSION) ? array_keys($_SESSION) : 'No session'
        ]
    ]);
}
exit;
?>