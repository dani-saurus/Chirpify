<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch current user info
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$currentUser = $userStmt->fetch();

try {
    $stmt = $pdo->query("
        SELECT posts.*, users.username
        FROM posts
        LEFT JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $likeCounts = [];
    $likeStmt = $pdo->query("
        SELECT post_id, COUNT(*) as count
        FROM likes
        GROUP BY post_id
    ");
    while ($row = $likeStmt->fetch()) {
        $likeCounts[$row['post_id']] = $row['count'];
    }
    
    $commentCounts = [];
    $commentStmt = $pdo->query("
        SELECT post_id, COUNT(*) as count
        FROM comments
        GROUP BY post_id
    ");
    while ($row = $commentStmt->fetch()) {
        $commentCounts[$row['post_id']] = $row['count'];
    }
    
    $allComments = [];
    $commentsStmt = $pdo->query("
        SELECT comments.*, users.username
        FROM comments
        LEFT JOIN users ON comments.user_id = users.id
        ORDER BY comments.created_at ASC
    ");
    while ($row = $commentsStmt->fetch()) {
        if (!isset($allComments[$row['post_id']])) {
            $allComments[$row['post_id']] = [];
        }
        $allComments[$row['post_id']][] = $row;
    }
    
    $userLikes = [];
    if (isset($_SESSION['user_id'])) {
        $userLikeStmt = $pdo->prepare("
            SELECT post_id FROM likes WHERE user_id = ?
        ");
        $userLikeStmt->execute([$_SESSION['user_id']]);
        while ($row = $userLikeStmt->fetch()) {
            $userLikes[$row['post_id']] = true;
        }
    }
} catch(PDOException $e) {
    die("Error fetching posts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="chirp.css">
    <script defer src="chirp.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="Chirpify_logo.jpeg" alt="Logo" class="logo-img">
        </div>
        <button class="sidebar-button">Home</button>
        <button class="sidebar-button">Notifications</button>
        <div class="profile">
            <div class="pfp">
                <?php echo htmlspecialchars(substr($currentUser['username'], 0, 1)); ?>
            </div>
            <span class="profile-name"><?php echo htmlspecialchars($currentUser['username']); ?></span>
            <a href="profile.php" class="edit-profile-link">Edit Profile</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </div>
    <div class="main-content">
        <div class="post-box">
            <div class="pfp">PFP</div>
            <form id="post-form" enctype="multipart/form-data" method="post">
                <div class="input-container">
                    <input id="tweet-input" type="text" name="content" placeholder="What's on your mind?">
                    <input id="image-input" type="file" name="image" accept="image/*" style="display: none;">
                    <div class="button-container">
                        <button type="button" id="upload-button" class="action-button">Upload Image</button>
                        <button type="submit" id="post-button" class="action-button">Post</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="tweets-container">
            <?php foreach ($posts as $post):
                $postId = $post['id'];
                $likeCount = $likeCounts[$postId] ?? 0;
                $commentCount = $commentCounts[$postId] ?? 0;
                $isLiked = isset($userLikes[$postId]);
            ?>
                <div class="tweet" data-post-id="<?php echo $postId; ?>">
                    <div class="pfp">PFP</div>
                    <div class="tweet-content">
                        <span class="username"><?php echo htmlspecialchars($post['username'] ?? 'Anonymous'); ?></span>
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <?php if ($post['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" style="max-width: 100%; border-radius: 10px;">
                        <?php endif; ?>
                        <div class="tweet-meta">
                            <small><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></small>
                        </div>
                        <div class="tweet-actions">
                            <button class="like-button <?php echo $isLiked ? 'liked' : ''; ?>">
                                <?php echo $isLiked ? 'Liked' : 'Like'; ?>
                            </button>
                            <span class="like-count"><?php echo $likeCount; ?> Likes</span>
                            <button class="comment-button">Comment</button>
                            <span class="comment-count"><?php echo $commentCount; ?> Comments</span>
                        </div>
                        <div class="comments-container">
                            <?php if (isset($allComments[$postId])): ?>
                                <?php foreach ($allComments[$postId] as $comment): ?>
                                    <div class="comment">
                                        <span class="comment-username"><?php echo htmlspecialchars($comment['username'] ?? 'Anonymous'); ?></span>
                                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>