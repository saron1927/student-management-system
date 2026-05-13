<?php
session_start();
require 'config.php';

// 1. SECURITY: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// 2. PROCESS FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $class_name = $_POST['class_name'];
    $gpa = $_POST['gpa'];
    $address = $_POST['address'];
    
    // Handle Photo Upload
    $photo = "default.png"; // Default if no file uploaded
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        // Create folder if it doesn't exist
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $photo = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_id"], $target_dir . $photo);
    }

    try {
        // 3. INSERT DATA
        // Note: We leave user_id as NULL or 0 if this is a general entry, 
        // or you can set it to $_SESSION['user_id'] if the student is adding themselves.
        $sql = "INSERT INTO students (name, email, phone, class_name, gpa, address, photo, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $phone, $class_name, $gpa, $address, $photo, 0]);

        $message = "<div style='color: #27ae60; padding: 10px; background: #eafaf1; border-radius: 5px; margin-bottom: 20px;'>
                        ✅ Student registered successfully! <a href='list_students.php'>View List</a>
                    </div>";
    } catch (PDOException $e) {
        $message = "<div style='color: #e74c3c; padding: 10px; background: #fdf2f2; border-radius: 5px; margin-bottom: 20px;'>
                        ❌ Error: " . $e->getMessage() . "
                    </div>";
    }
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-card { max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #34495e; }
        input[type="text"], input[type="email"], input[type="number"], textarea, input[type="file"] {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;
        }
        .submit-btn { background: #3498db; color: white; border: none; padding: 15px 25px; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: bold; width: 100%; }
        .submit-btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2 style="margin-top: 0; color: #2c3e50;">Register New Student</h2>
        <p style="color: #7f8c8d; margin-bottom: 25px;">Enter the details below to add a student to the database.</p>
        
        <?= $message ?>

        <form action="create_student.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="e.g. John Doe">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="student@example.com">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="0123456789">
                </div>
                <div class="form-group">
                    <label>Class/Course</label>
                    <input type="text" name="class_name" required placeholder="BSCS - 3A">
                </div>
            </div>

            <div class="form-group">
                <label>Current GPA (0.00 - 4.00)</label>
                <input type="number" step="0.01" name="gpa" min="0" max="4" required value="0.00">
            </div>

            <div class="form-group">
                <label>Home Address</label>
                <textarea name="address" rows="3" placeholder="Enter complete address..."></textarea>
            </div>

            <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="photo" accept="image/*">
            </div>

            <button type="submit" class="submit-btn">Save Student Record</button>
        </form>
    </div>
</body>
</html>