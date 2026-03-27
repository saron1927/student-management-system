<?php
session_start();
require 'config.php';

// If already logged in, skip the login page
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // FIX: Redirect to dashboard.php instead of list_students.php
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Student System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { 
            background-color: #f4f4f4; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            font-family: Arial, sans-serif;
        }
        .login-card { 
            background: white; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1); 
            width: 350px; 
        }
        .error-msg {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Ensures padding doesn't break width */
        }
        button {
            width: 100%;
            background: #2c3e50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background: #34495e;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 30px;">Admin Login</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required>
            
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
            
            <button type="submit">Login to Dashboard</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            <a href="index.html" style="color: #7f8c8d; text-decoration: none; font-size: 0.85rem;">← Back to Home</a>
        </p>
    </div>

</body>
</html>