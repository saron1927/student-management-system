<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: list_students.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
    <div class="container" style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); width: 350px;">
        <h2 style="text-align: center;">Admin Login</h2>
        
        <?php if (isset($error)): ?>
            <p style="color: red; text-align: center;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Username:</label><br>
            <input type="text" name="username" required style="width: 100%; padding: 10px; margin: 10px 0;"><br>
            
            <label>Password:</label><br>
            <input type="password" name="password" required style="width: 100%; padding: 10px; margin: 10px 0;"><br>
            
            <button type="submit" style="width: 100%; background: #2c3e50; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Login</button>
        </form>
    </div>
</body>
</html>