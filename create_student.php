<?php
session_start();
require 'config.php';

// 1. SECURITY CHECK: Only Admins can create students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $class_name = trim($_POST['class_name']);
    $study_year = $_POST['study_year'];
    $address = trim($_POST['address']);
    $photoName = 'default.png';

    try {
        $pdo->beginTransaction();

        // A. CHECK IF USER ALREADY EXISTS
        $username = strtolower(str_replace(' ', '', $name)); 
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            // If username exists, add a random number to make it unique
            $username .= rand(10, 99);
        }

        // B. CREATE LOGIN ACCOUNT
        $password = password_hash("student123", PASSWORD_DEFAULT);
        $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
        $stmtUser->execute([$username, $password]);
        
        // IMPORTANT: Get the ID of the user we just created
        $new_user_id = $pdo->lastInsertId();

        // C. HANDLE PHOTO UPLOAD
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            
            $photoName = time() . "_" . basename($_FILES["photo"]["name"]);
            move_uploaded_file($_FILES["photo"]["tmp_name"], $targetDir . $photoName);
        }

        // D. INSERT STUDENT RECORD (Linked via user_id)
        $sql = "INSERT INTO students (name, email, phone, class_name, study_year, address, photo, user_id, gpa) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00)";
        $stmtStudent = $pdo->prepare($sql);
        $stmtStudent->execute([$name, $email, $phone, $class_name, $study_year, $address, $photoName, $new_user_id]);

        $pdo->commit();
        
        // SUCCESS: Redirect to the list, NOT the profile
        header("Location: list_students.php?msg=added");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

include 'header.php'; 
?>

<div class="container" style="max-width: 600px; margin-top: 30px;">
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">Register New Student</h2>
        
        <?php if (isset($error)): ?>
            <div style="background: #fff5f5; color: #c53030; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <p style="text-align: center; color: #7f8c8d; font-size: 0.9rem; margin-bottom: 30px;">
            This will create a login with password: <strong>student123</strong>
        </p>

        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Full Name</label>
                <input type="text" name="name" required placeholder="e.g. John Doe" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Email Address</label>
                <input type="email" name="email" required placeholder="email@example.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Course/Class</label>
                    <input type="text" name="class_name" placeholder="e.g. Computer Science" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Study Year</label>
                    <select name="study_year" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                        <option value="Year 1">Year 1</option>
                        <option value="Year 2">Year 2</option>
                        <option value="Year 3">Year 3</option>
                        <option value="Year 4">Year 4</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Phone Number</label>
                <input type="text" name="phone" placeholder="+123 456 789" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Home Address</label>
                <textarea name="address" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Profile Photo</label>
                <input type="file" name="photo" accept="image/*" style="margin-top: 5px;">
            </div>

            <button type="submit" style="width: 100%; background: #27ae60; color: white; padding: 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1rem;">Create Student Profile</button>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="list_students.php" style="color: #95a5a6; text-decoration: none; font-size: 0.9rem;">Cancel and Return</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>