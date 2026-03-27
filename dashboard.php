<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'config.php';

// Fetch Stats
$totalStudents = $pdo->query("SELECT count(*) FROM students")->fetchColumn();
$latestStudent = $pdo->query("SELECT name FROM students ORDER BY id DESC LIMIT 1")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-box { background: white; padding: 20px; border-radius: 10px; text-align: center; border-top: 5px solid #3498db; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-box h3 { margin: 0; color: #7f8c8d; font-size: 0.9rem; text-transform: uppercase; }
        .stat-box p { font-size: 2rem; font-weight: bold; margin: 10px 0; color: #2c3e50; }
        .action-btn { display: block; background: #34495e; color: white; padding: 15px; text-decoration: none; border-radius: 5px; margin-top: 10px; transition: 0.3s; }
        .action-btn:hover { background: #2c3e50; transform: translateY(-3px); }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <p style="color: #7f8c8d;">Here is what's happening with your student database today.</p>

        <div class="grid">
            <div class="stat-box">
                <h3>Total Students</h3>
                <p><?= $totalStudents ?></p>
            </div>
            <div class="stat-box">
                <h3>Latest Joiner</h3>
                <p style="font-size: 1.2rem;"><?= $latestStudent ? $latestStudent : 'None' ?></p>
            </div>
            <div class="stat-box">
                <h3>System Status</h3>
                <p style="font-size: 1.2rem; color: #27ae60;">Online</p>
            </div>
        </div>

        <h2 style="margin-top: 40px;">Quick Actions</h2>
        <div class="grid">
            <a href="create_student.php" class="action-btn">➕ Add New Student</a>
            <a href="list_students.php" class="action-btn">📋 View Full List</a>
            <a href="list_students.php" class="action-btn" style="background: #27ae60;">🔍 Search Database</a>
        </div>
    </div>
</body>
</html>