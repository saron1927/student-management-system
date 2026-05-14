<?php
session_start();

// 1. SECURITY: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Initialize variables to prevent "Undefined variable" notices
$totalStudents = 0;
$latestStudent = 'No Records';

try {
    // 2. FETCH STATS
    // Count total students
    $totalStudents = $pdo->query("SELECT count(*) FROM students")->fetchColumn();
    
    // Get the name of the last person added
    // Added a check to see if the record actually exists
    $stmtLatest = $pdo->query("SELECT name FROM students ORDER BY id DESC LIMIT 1");
    $result = $stmtLatest->fetchColumn();
    
    if ($result) {
        $latestStudent = $result;
    }

} catch (PDOException $e) {
    // In production, log this to a file instead of echoing it to the user
    error_log("Database Error: " . $e->getMessage());
    $db_error = "Could not connect to the database.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Your existing styles are great, keeping them for consistency */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-box { background: white; padding: 25px; border-radius: 12px; text-align: center; border-top: 5px solid #3498db; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .stat-box h3 { margin: 0; color: #7f8c8d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-box p { font-size: 2.2rem; font-weight: 800; margin: 15px 0; color: #2c3e50; }
        .action-btn { display: block; background: #34495e; color: white; padding: 18px; text-decoration: none; border-radius: 8px; margin-top: 10px; transition: all 0.3s ease; text-align: center; font-weight: 600; }
        .action-btn:hover { background: #2c3e50; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .error-msg { color: #e74c3c; background: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 40px 20px;">
        
        <?php if (isset($db_error)): ?>
            <div class="error-msg"><?= htmlspecialchars($db_error) ?></div>
        <?php endif; ?>

        <h1 style="color: #2c3e50;">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h1>
        <p style="color: #7f8c8d; font-size: 1.1rem;">Overview of the Student Management System.</p>

        <div class="grid">
            <div class="stat-box">
                <h3>Total Enrollment</h3>
                <!-- Escaping the count just in case -->
                <p><?= htmlspecialchars($totalStudents) ?></p>
            </div>
            <div class="stat-box">
                <h3>Latest Admission</h3>
                <p style="font-size: 1.3rem; color: #3498db;">
                    <?= htmlspecialchars($latestStudent) ?>
                </p>
            </div>
            <div class="stat-box">
                <h3>System Status</h3>
                <p style="font-size: 1.3rem; color: #27ae60;">● Active</p>
            </div>
        </div>

        <h2 style="margin-top: 50px; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">Quick Management</h2>
        <div class="grid">
            <a href="create_student.php" class="action-btn">➕ Add New Student</a>
            <a href="list_students.php" class="action-btn">📋 View All Students</a>
            <a href="my_profile.php" class="action-btn" style="background: #2980b9;">👤 My Private Profile</a>
        </div>
    </div>
</body>
</html>