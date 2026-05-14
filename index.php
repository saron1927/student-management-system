<?php
// index.php
require_once 'includes/auth.php';
require_once 'config/database.php';

redirectIfLoggedIn();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        header("Location: " . $user['role'] . "/dashboard.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UniTrack SMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <i class="fas fa-graduation-cap fa-3x" style="color: var(--accent); margin-bottom: 1rem;"></i>
            <h2>Welcome Back</h2>
            <p>Please enter your details to sign in</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" style="width: 1rem; height: 1rem;"> Remember me
                </label>
                <a href="forgot-password.php" style="color: var(--accent); font-size: 0.875rem; text-decoration: none; font-weight: 500;">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary">
                Sign In <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div style="margin-top: 2rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
            Don't have an account? <a href="#" style="color: var(--accent); font-weight: 600; text-decoration: none;">Contact Admin</a>
        </div>
    </div>
</body>
</html>
