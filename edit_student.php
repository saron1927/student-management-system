<?php
session_start();
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: list_students.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $photoName = $student['photo']; // Start with the current photo name

    // Check if a new file was uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $targetDir = "uploads/";
        $newPhotoName = time() . "_" . basename($_FILES["photo"]["name"]);
        $targetFilePath = $targetDir . $newPhotoName;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
            // OPTIONAL: Delete the old photo file from the folder (if it's not the default)
            if ($student['photo'] != 'default.png' && file_exists("uploads/" . $student['photo'])) {
                unlink("uploads/" . $student['photo']);
            }
            $photoName = $newPhotoName;
        }
    }

    $sql = "UPDATE students SET name = ?, email = ?, photo = ? WHERE id = ?";
    $pdo->prepare($sql)->execute([$name, $email, $photoName, $id]);

    header("Location: list_students.php?msg=updated");
    exit();
}
?>

<div class="container">
    <h2>Edit Student</h2>
    <form method="POST" enctype="multipart/form-data"> <label>Current Photo:</label><br>
        <img src="uploads/<?= $student['photo'] ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #3498db;"><br>

        <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
        
        <label>Change Photo (Optional):</label>
        <input type="file" name="photo" accept="image/*">
        
        <button type="submit">Update Student</button>
        <a href="list_students.php">Cancel</a>
    </form>
</div>