<?php
session_start();
require 'config.php';

// 1. SECURITY: Only logged-in Students can access this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

try {
    // 2. PRIMARY ATTEMPT: Search by user_id
    $stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $s = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. FALLBACK ATTEMPT: If user_id link is broken, search by matching email
    if (!$s) {
        // We look for a student whose email matches the logged-in user's username/email
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$_SESSION['username']]); 
        $s = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If we found them by email, let's fix the user_id link automatically!
        if ($s) {
            $update = $pdo->prepare("UPDATE students SET user_id = ? WHERE id = ?");
            $update->execute([$_SESSION['user_id'], $s['id']]);
        }
    }

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

include 'header.php'; 
?>

<div class="container" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <?php if (!$s): ?>
        <div style="max-width: 550px; margin: 50px auto; background:#fff; border: 1px solid #ffccd5; border-radius:15px; overflow:hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="background: #ff4d4d; color: white; padding: 20px; text-align: center;">
                <h2 style="margin:0;">Profile Not Found</h2>
            </div>
            <div style="padding: 30px; text-align: center;">
                <p style="color: #666; font-size: 1.1rem;">
                    Account ID <strong><?= (int)$_SESSION['user_id'] ?></strong> is not linked to a student.
                </p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: left; border-left: 5px solid #ff4d4d;">
                    <strong>Manual Fix:</strong><br>
                    Go to phpMyAdmin and set <code>user_id</code> to <code><?= (int)$_SESSION['user_id'] ?></code> in the students table.
                </div>
                <a href="logout.php" style="text-decoration:none; color:white; background:#333; padding:12px 25px; border-radius:8px; display:inline-block;">Return to Login</a>
            </div>
        </div>

    <?php else: ?>
        <div style="max-width: 700px; margin: 40px auto; background: white; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="height: 120px; background: linear-gradient(135deg, #3498db, #2c3e50);"></div>
            <div style="padding: 0 40px 40px 40px; margin-top: -60px; text-align: center;">
                <?php 
                    $photoPath = "uploads/" . $s['photo'];
                    $displayPhoto = (!empty($s['photo']) && file_exists($photoPath)) ? $photoPath : 'default.png';
                ?>
                <img src="<?= $displayPhoto ?>" style="width:130px; height:130px; border-radius:50%; object-fit:cover; border: 5px solid white; background: #eee; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">

                <h1 style="margin: 15px 0 5px 0; color: #2c3e50;"><?= htmlspecialchars($s['name']) ?></h1>
                <p style="color:#7f8c8d; font-weight: 500; font-size: 1.1rem; margin-bottom: 25px;">
                    <?= htmlspecialchars($s['class_name'] ?? 'Student') ?> — <?= htmlspecialchars($s['study_year'] ?? 'Active') ?>
                </p>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left;">
                    <div style="background: #f0f7ff; padding: 20px; border-radius: 15px; border: 1px solid #d0e7ff; text-align: center;">
                        <span style="display: block; color: #3498db; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">GPA</span>
                        <h2 style="font-size: 3rem; margin: 10px 0; color: #2c3e50;"><?= number_format($s['gpa'] ?? 0.00, 2) ?></h2>
                    </div>

                    <div style="padding: 10px 0;">
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #7f8c8d; font-size: 0.8rem;">EMAIL</strong>
                            <p style="margin: 5px 0; color: #2c3e50;"><?= htmlspecialchars($s['email']) ?></p>
                        </div>
                        <div>
                            <strong style="color: #7f8c8d; font-size: 0.8rem;">PHONE</strong>
                            <p style="margin: 5px 0; color: #2c3e50;"><?= htmlspecialchars($s['phone'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>