<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $sql = "INSERT INTO students (name, email) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email]);

    header("Location: list_students.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Add Student</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit">Save Student</button>
        </form>
        <a href="index.html">Back to Home</a>
    </div>
</body>
</html>