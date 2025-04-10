<?php
require_once 'database.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS comments");
    $pdo->exec("DROP TABLE IF EXISTS likes");
    $pdo->exec("DROP TABLE IF EXISTS posts");
    $pdo->exec("DROP TABLE IF EXISTS users");

    $pdo->exec("
        CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100),
            bio TEXT,
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE posts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            content TEXT,
            image_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE likes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            post_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (post_id) REFERENCES posts(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE comments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            post_id INT,
            content TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (post_id) REFERENCES posts(id)
        )
    ");

    echo "Database tables created successfully!";
} catch(PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?>
