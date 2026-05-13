<?php
session_start();
require 'config.php';

// 1. SECURITY: Ensure only logged-in users can view the list
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // 2. THE FIX: Fetch ALL students from the database
    // We remove the "WHERE user_id = ?" to show the entire list
    $stmt = $pdo->query("SELECT * FROM students ORDER BY name ASC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Directory</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .list-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .table-card { background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        th { padding: 15px; text-align: left; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
        tr:hover { background: #fbfcfe; }
        
        .avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; }
        .gpa-badge { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.9rem; }
        .btn-edit { color: #3b82f6; text-decoration: none; font-weight: 600; margin-right: 15px; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 600; }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .add-btn { background: #27ae60; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.2s; }
        .add-btn:hover { background: #219150; }
    </style>
</head>
<body>
    <div class="list-container">
        <div class="header-flex">
            <div>
                <h1 style="margin:0; color: #1e293b;">Student Directory</h1>
                <p style="margin:5px 0 0 0; color: #64748b;">Managing <?= count($students) ?> total records</p>
            </div>
            <a href="create_student.php" class="add-btn">+ Register New Student</a>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Student Name</th>
                        <th>Class/Course</th>
                        <th>GPA</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 50px; color: #94a3b8;">
                                No students found in the database. <a href="create_student.php">Add one now.</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $row): ?>
                        <tr>
                            <td>
                                <?php 
                                    $photo = !empty($row['photo']) && file_exists("uploads/" . $row['photo']) 
                                             ? "uploads/" . $row['photo'] 
                                             : "default.png";
                                ?>
                                <img src="<?= $photo ?>" class="avatar" alt="Profile">
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($row['name']) ?></div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">ID: #<?= $row['id'] ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['class_name'] ?? 'N/A') ?></td>
                            <td>
                                <span class="gpa-badge"><?= number_format($row['gpa'], 2) ?></span>
                            </td>
                            <td style="font-size: 0.85rem;">
                                <div><?= htmlspecialchars($row['email']) ?></div>
                                <div style="color: #94a3b8;"><?= htmlspecialchars($row['phone'] ?? '') ?></div>
                            </td>
                            <td>
                                <a href="edit_student.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                <a href="delete_student.php?id=<?= $row['id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>