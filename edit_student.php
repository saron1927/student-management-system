<?php
session_start();
require 'config.php';

// 1. SECURITY CHECK: Only Admins can edit
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. FETCH STUDENT DATA
if (!isset($_GET['id'])) {
    header("Location: list_students.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found!");
}

// 3. HANDLE UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $class_name = $_POST['class_name'];
    $study_year = $_POST['study_year'];
    $address = $_POST['address'];
    $gpa = $_POST['gpa']; // The grade field

    // Check if a new photo was uploaded
    $photoName = $student['photo']; // Keep old photo by default
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $targetDir = "uploads/";
        $photoName = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $targetDir . $photoName);
    }

    // UPDATE DATABASE
    $sql = "UPDATE students SET name=?, email=?, phone=?, class_name=?, study_year=?, address=?, photo=?, gpa=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email, $phone, $class_name, $study_year, $address, $photoName, $gpa, $id]);

    header("Location: list_students.php?msg=updated");
    exit();
}

include 'header.php';
?>

<div class="container" style="max-width: 600px; margin-top: 30px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #2c3e50;">Edit Student Profile</h2>
        <hr>

        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 15px;">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label>Course/Class</label>
                    <input type="text" name="class_name" value="<?= htmlspecialchars($student['class_name']) ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div style="flex: 1;">
                    <label>Study Year</label>
                    <select name="study_year" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Year 1" <?= ($student['study_year'] == 'Year 1') ? 'selected' : '' ?>>Year 1</option>
                        <option value="Year 2" <?= ($student['study_year'] == 'Year 2') ? 'selected' : '' ?>>Year 2</option>
                        <option value="Year 3" <?= ($student['study_year'] == 'Year 3') ? 'selected' : '' ?>>Year 3</option>
                        <option value="Year 4" <?= ($student['study_year'] == 'Year 4') ? 'selected' : '' ?>>Year 4</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 5px solid #3498db;">
                <label style="font-weight: bold; color: #2c3e50;">Current GPA / Grade (0.00 - 4.00)</label>
                <input type="number" name="gpa" step="0.01" min="0" max="4" value="<?= $student['gpa'] ?? '0.00' ?>" style="width:100%; padding:12px; border-radius:5px; border:1px solid #ccc; font-size: 1.1rem; font-weight: bold;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Home Address</label>
                <textarea name="address" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;"><?= htmlspecialchars($student['address']) ?></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label>Update Photo (Optional)</label><br>
                <input type="file" name="photo" accept="image/*">
            </div>

            <button type="submit" style="width:100%; background:#2c3e50; color:white; padding:12px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Update Student Record</button>
            <a href="list_students.php" style="display:block; text-align:center; margin-top:15px; color:#7f8c8d; text-decoration:none;">Cancel and Go Back</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>