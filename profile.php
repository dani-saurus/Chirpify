<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT name, bio, profile_picture FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'profile-name', FILTER_SANITIZE_STRING);
    $bio = filter_input(INPUT_POST, 'profile-bio', FILTER_SANITIZE_STRING);
    
    if (isset($_FILES['profile-pic']) && $_FILES['profile-pic']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile-pic']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $newname = uniqid() . '.' . $filetype;
            $uploadPath = 'uploads/' . $newname;
            
            if (move_uploaded_file($_FILES['profile-pic']['tmp_name'], $uploadPath)) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, profile_picture = ? WHERE id = ?");
                $stmt->execute([$name, $bio, $uploadPath, $_SESSION['user_id']]);
            }
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $_SESSION['user_id']]);
    }
    
    header('Location: profile.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-container">
        <h1>Edit Profile</h1>
        <div class="profile-preview">
            <img id="profile-pic-preview" src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'default-pfp.jpg'); ?>" alt="Profile Picture">
            <h2 id="profile-name-preview"><?php echo htmlspecialchars($user['name'] ?? 'Your Name'); ?></h2>
            <p id="profile-bio-preview"><?php echo htmlspecialchars($user['bio'] ?? 'Your bio will appear here...'); ?></p>
        </div>
        <form id="profile-form" method="POST" enctype="multipart/form-data">
            <label for="profile-pic">Profile Picture:</label>
            <input type="file" id="profile-pic" name="profile-pic" accept="image/*">
            
            <label for="profile-name">Name:</label>
            <input type="text" id="profile-name" name="profile-name" placeholder="Enter your name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
            
            <label for="profile-bio">Bio:</label>
            <textarea id="profile-bio" name="profile-bio" placeholder="Write something about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            
            <button type="submit" class="save-button">Save Changes</button>
        </form>
        <a href="mainpage.php" class="back-to-main">Back to Main Page</a>
    </div>
</body>
</html>
