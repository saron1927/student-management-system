<?php
session_start();
require 'config.php';

// 1. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // 2. Fetch only the logged-in user's details
    // Assumes your admin/user table is named 'users'
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container" style="max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <h1>My Private Profile</h1>
        <hr>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Account Created:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        
        <div style="margin-top: 20px;">
            <a href="edit_profile.php" class="action-btn" style="background: #e67e22;">Edit My Details</a>
            <a href="dashboard.php" class="action-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>