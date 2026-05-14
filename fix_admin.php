<?php
// fix_admin.php
require_once 'config/database.php';

try {
    // 1. Generate the secure hash for '123'
    $new_password = '123';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // 2. Update the admin user in the database
    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
    $stmt->execute([$hashed_password]);

    if ($stmt->rowCount() > 0) {
        echo "<h2 style='color: green;'>Success!</h2>";
        echo "Admin password has been reset to: <strong>123</strong><br>";
        echo "Username is: <strong>admin</strong><br><br>";
        echo "<a href='index.php'>Go to Login Page</a>";
    } else {
        // If admin doesn't exist, create it
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES ('admin', ?, 'admin', 'System Admin', 'admin@unitrack.com')");
        $stmt->execute([$hashed_password]);
        echo "<h2 style='color: blue;'>User Created!</h2>";
        echo "Admin user did not exist, so I created a new one.<br>";
        echo "Username: <strong>admin</strong> | Password: <strong>123</strong><br><br>";
        echo "<a href='index.php'>Go to Login Page</a>";
    }
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error!</h2>";
    echo "Could not update database: " . $e->getMessage();
}
?>
