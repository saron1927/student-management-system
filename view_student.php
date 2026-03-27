<?php
session_start();
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: list_students.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_GET['id']]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile: <?= htmlspecialchars($student['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            margin: 40px auto;
        }
        .profile-img-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f0f0f0;
            margin-bottom: 20px;
        }
        @media print {
            .nav, .btn-back { display: none; } /* Hide buttons when printing */
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="profile-card">
            <img src="uploads/<?= $student['photo'] ?>" class="profile-img-large">
            <h1 style="margin: 10px 0;"><?= htmlspecialchars($student['name']) ?></h1>
            <p style="color: #7f8c8d; font-size: 1.1rem;"><?= htmlspecialchars($student['email']) ?></p>
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
            
            <p><strong>Student ID:</strong> #<?= $student['id'] ?></p>
            
            <div style="margin-top: 30px;">
                <button onclick="window.print()" style="background: #34495e; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    🖨️ Print Profile
                </button>
                <br><br>
                <a href="edit_student.php?id=<?= $student['id'] ?>" style="color: #3498db;">Edit Info</a> | 
                <a href="list_students.php" class="btn-back" style="color: #666;">Back to List</a>
            </div>
        </div>
    </div>
</body>
</html>