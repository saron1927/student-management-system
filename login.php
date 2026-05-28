<?php
session_start();
require 'config.php';

// 1. REDIRECT IF ALREADY LOGGED IN
if (isset($_SESSION['user_id'])) {
    header("Location: " . $_SESSION['role'] . "/dashboard.php");
    exit();
}

// 2. HANDLE LOGIN ATTEMPT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // Fetch user by username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            
            // SECURITY: Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['role'] = $user['role']; 

            // Redirect based on role
            header("Location: " . $user['role'] . "/dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Student Management System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <style>
        body { 
            background-color: #f0f2f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            font-family: 'Inter', -apple-system, sans-serif;
        }
        .login-card { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0px 10px 30px rgba(0,0,0,0.1); 
            width: 100%;
            max-width: 400px; 
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h2 { margin: 0; color: #1a202c; font-size: 1.8rem; }
        .login-header p { color: #718096; margin-top: 8px; }

        .error-msg {
            background-color: #fff5f5;
            color: #c53030;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #feb2b2;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; color: #4a5568; margin-bottom: 8px; }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: all 0.2s;
        }

        input:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
            outline: none;
        }

        button {
            width: 100%;
            background: #2d3748;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            transition: background 0.2s;
        }

        button:hover { background: #1a202c; }

        .footer-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
        }
        .footer-link a { color: #3182ce; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2>Portal Login</h2>
            <p>Welcome back! Please sign in.</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Your username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit">Sign In to Portal</button>
        </form>

        <div class="footer-link">
            <a href="index.php">← Back to Homepage</a>
        </div>
    </div>

</body>
</html>