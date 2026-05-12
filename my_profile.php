<?php
// 1. MUST BE FIRST: Start the session before doing anything else
session_start();

// 2. Include database configuration
require 'config.php';

/* DEBUG TIP: If you are still seeing "Profile Not Found", 
   uncomment the line below to see what ID PHP is using:
*/
// die("DEBUG: I am looking for a student with user_id: " . ($_SESSION['user_id'] ?? 'EMPTY SESSION'));

// 3. SECURITY: Only logged-in Students can access this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

try {
    // 4. FETCH DATA: Match the session ID to the student's user_id column
    $stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $s = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

include 'header.php'; 
?>

<div class="container" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <?php if (!$s): ?>
        <div style="max-width: 550px; margin: 50px auto; background:#fff; border: 1px solid #ffccd5; border-radius:15px; overflow:hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="background: #ff4d4d; color: white; padding: 20px; text-align: center;">
                <h2 style="margin:0;">Profile Link Missing</h2>
            </div>
            <div style="padding: 30px; text-align: center;">
                <p style="color: #666; font-size: 1.1rem;">
                    We found your login account (ID: <strong><?= (int)$_SESSION['user_id'] ?></strong>), 
                    but your student details aren't connected in the database.
                </p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: left; border-left: 5px solid #ff4d4d;">
                    <p style="margin:0; font-weight:bold; color: #333;">Technical Fix for Admins:</p>
                    <code style="display:block; margin-top:10px; color: #d63384;">
                        UPDATE students SET user_id = <?= (int)$_SESSION['user_id'] ?> WHERE name = 'Your Name';
                    </code>
                </div>

                <a href="logout.php" style="text-decoration: none; color: white; background: #333; padding: 12px 25px; border-radius: 8px; display: inline-block; font-weight: bold;">Return to Login</a>
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
                <img src="<?= $displayPhoto ?>" 
                     style="width:130px; height:130px; border-radius:50%; object-fit:cover; border: 5px solid white; background: #eee; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">

                <h1 style="margin: 15px 0 5px 0; color: #2c3e50;"><?= htmlspecialchars($s['name']) ?></h1>
                <p style="color:#7f8c8d; font-weight: 500; font-size: 1.1rem; margin-bottom: 25px;">
                    <?= htmlspecialchars($s['class_name'] ?? 'Unassigned Course') ?> — <?= htmlspecialchars($s['study_year'] ?? 'N/A') ?>
                </p>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left;">
                    <div style="background: #f0f7ff; padding: 20px; border-radius: 15px; border: 1px solid #d0e7ff; text-align: center;">
                        <span style="display: block; color: #3498db; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">Current GPA</span>
                        <h2 style="font-size: 3rem; margin: 10px 0; color: #2c3e50;"><?= number_format($s['gpa'] ?? 0.00, 2) ?></h2>
                        <?php $statusColor = ($s['gpa'] >= 2.0) ? '#27ae60' : '#e74c3c'; ?>
                        <span style="background: <?= $statusColor ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                            <?= ($s['gpa'] >= 2.0) ? 'GOOD STANDING' : 'PROBATION' ?>
                        </span>
                    </div>

                    <div style="padding: 10px 0;">
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #7f8c8d; font-size: 0.8rem; text-transform: uppercase;">Email Address</strong>
                            <p style="margin: 5px 0; color: #2c3e50;"><?= htmlspecialchars($s['email']) ?></p>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #7f8c8d; font-size: 0.8rem; text-transform: uppercase;">Phone</strong>
                            <p style="margin: 5px 0; color: #2c3e50;"><?= htmlspecialchars($s['phone'] ?? 'Not set') ?></p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; padding: 20px; background: #fdfdfd; border-radius: 10px; border: 1px solid #eee; text-align: left;">
                    <strong style="color: #7f8c8d; font-size: 0.8rem; text-transform: uppercase;">Mailing Address</strong>
                    <p style="margin: 10px 0 0 0; color: #2c3e50; line-height: 1.5;"><?= nl2br(htmlspecialchars($s['address'] ?? 'No address provided.')) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>