<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - UniTrack SMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <i class="fas fa-key fa-3x" style="color: var(--accent); margin-bottom: 1rem;"></i>
            <h2>Forgot Password?</h2>
            <p>Enter your email and we'll send you instructions to reset your password.</p>
        </div>

        <form>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-control" placeholder="name@university.edu" required>
            </div>
            <button type="submit" class="btn btn-primary">
                Send Reset Link
            </button>
        </form>

        <div style="margin-top: 2rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
            Remembered your password? <a href="index.php" style="color: var(--accent); font-weight: 600; text-decoration: none;">Back to Login</a>
        </div>
    </div>
</body>
</html>
