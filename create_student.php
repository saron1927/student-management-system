<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// ... rest of your code ...
?>
<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $sql = "INSERT INTO students (name, email) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email]);

    header("Location: list_students.php?msg=added");
    exit(); // Always good practice to exit after a header redirect
}

// 1. Pull in the Navigation and CSS
include 'header.php'; 
?>

<div class="container">
    <h2>Add New Student</h2>
    
    <form method="POST" style="margin-top: 20px;">
        <div style="margin-bottom: 15px;">
            <label>Full Name:</label><br>
            <input type="text" name="name" placeholder="Enter full name" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Email Address:</label><br>
            <input type="email" name="email" placeholder="student@example.com" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>

        <button type="submit" class="btn">Save Student</button>
        <a href="list_students.php" style="margin-left: 10px; color: #666; text-decoration: none;">Cancel</a>
    </form>
</div>

<?php 
// 2. Pull in the Scripts and closing tags
include 'footer.php'; 
?>