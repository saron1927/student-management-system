<?php
require 'config.php';

// 1. GET THE STUDENT'S CURRENT DATA
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found!");
    }
}

// 2. HANDLE THE UPDATE (When the form is submitted)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    try {
        $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $id]);
        
        // Redirect back with a NEW message type: 'updated'
        header("Location: list_students.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        echo "Update failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Student Details</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $student['id'] ?>">

            <label>Name:</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required><br><br>

            <button type="submit">Update Student</button>
            <a href="list_students.php">Cancel</a>
        </form>
    </div>
</body>
</html>