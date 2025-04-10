<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Validate content
    if (!isset($_POST['content']) || trim($_POST['content']) === '') {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
            throw new Exception('Post must contain either text or an image');
        }
    }

    $content = $_POST['content'] ?? '';
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // For testing purposes, we'll use user ID 1
        // In production, you should redirect to login
        $user_id = 1;
        
        // Check if user exists
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkUser->execute([$user_id]);
        if (!$checkUser->fetch()) {
            // Create a default user if none exists
            $createUser = $pdo->prepare("INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)");
            $createUser->execute([1, 'default_user', password_hash('password', PASSWORD_DEFAULT)]);
        }
    } else {
        $user_id = $_SESSION['user_id'];
    }

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = 'uploads/' . $image_name;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path)) {
            throw new Exception('Failed to upload image: ' . error_get_last()['message']);
        }
    }

    // Insert the post
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $content, $image_path]);
        
        echo json_encode([
            'success' => true,
            'post_id' => $pdo->lastInsertId(),
            'message' => 'Post created successfully'
        ]);
    } catch (PDOException $pdoEx) {
        throw new Exception('Database error: ' . $pdoEx->getMessage());
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'post' => $_POST,
            'files' => $_FILES,
            'session' => isset($_SESSION) ? array_keys($_SESSION) : 'No session',
            'pdo_error' => isset($pdo) ? $pdo->errorInfo() : 'No PDO connection'
        ]
    ]);
}
exit;
?>
