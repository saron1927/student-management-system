<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body style="display: flex; align-items: center; justify-content: center; height: 100vh; background: #f1f5f9;">
    <div style="text-align: center; background: white; padding: 3rem; border-radius: 1rem; box-shadow: var(--shadow);">
        <i class="fas fa-lock fa-4x" style="color: var(--danger); margin-bottom: 1rem;"></i>
        <h1 style="font-size: 2rem; margin-bottom: 1rem;">Access Denied</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">You do not have permission to access this page.</p>
        <a href="index.php" class="btn btn-primary">Go to Login</a>
    </div>
</body>
</html>
