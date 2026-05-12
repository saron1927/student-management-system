<?php
session_start();

// 1. Security Check: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Role Check: Only Admins can see the full list
if ($_SESSION['role'] !== 'admin') {
    header("Location: my_profile.php"); 
    exit();
}

require 'config.php';

$search = $_GET['search'] ?? '';

try {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE name LIKE ? OR class_name LIKE ? ORDER BY name ASC");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM students ORDER BY name ASC");
    }
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Directory | List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #2c3e50; color: white; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        tr:hover { background-color: #f9f9f9; }
        .avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; }
        .badge { background: #3498db; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .year-tag { background: #f1f8ff; color: #0366d6; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; border: 1px solid #c8e1ff; }
        .student-link { text-decoration: none; color: #2c3e50; font-weight: bold; }
        .student-link:hover { color: #3498db; }
        .contact-info { font-size: 0.8rem; color: #7f8c8d; line-height: 1.4; }
        
        /* Grade Colors */
        .grade-high { color: #27ae60; font-weight: bold; }
        .grade-mid { color: #f39c12; font-weight: bold; }
        .grade-low { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 20px;">
            <h2>Student Directory</h2>
            <a href="create_student.php" style="color: #3498db; text-decoration: none; font-weight: bold;">+ Add New Student</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <span class="badge">Total: <?= count($students) ?></span>
                <?php if ($search): ?>
                    <span style="color: #7f8c8d; margin-left: 10px;">Results for "<?= htmlspecialchars($search) ?>"</span>
                <?php endif; ?>
            </div>
            <a href="export_students.php" style="background: #27ae60; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 0.85rem;">📥 Export CSV</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div style="padding: 12px; border-radius: 5px; margin-bottom: 20px; background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; font-size: 0.9rem;">
                <?php
                    if($_GET['msg'] == 'added') echo "✓ New student successfully registered.";
                    if($_GET['msg'] == 'updated') echo "✏️ Profile updated successfully.";
                    if($_GET['msg'] == 'deleted') echo "🗑️ Student record removed.";
                ?>
            </div>
        <?php endif; ?>

        <form method="GET" style="margin-bottom: 25px; display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search by name or course..." value="<?= htmlspecialchars($search) ?>" style="padding: 10px; flex: 1; border: 1px solid #ddd; border-radius: 6px;">
            <button type="submit" style="padding: 10px 25px; cursor: pointer; background: #2c3e50; color: white; border: none; border-radius: 6px; font-weight: bold;">Search</button>
            <?php if ($search): ?>
                <a href="list_students.php" style="padding: 10px; color: #95a5a6; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Course/Year</th>
                    <th>GPA / Grade</th>
                    <th>Contact Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td>
                        <img src="uploads/<?= !empty($s['photo']) && file_exists("uploads/".$s['photo']) ? $s['photo'] : 'default.png' ?>" class="avatar">
                    </td>
                    <td>
                        <a href="view_student.php?id=<?= $s['id'] ?>" class="student-link">
                            <?php 
                                $name = htmlspecialchars($s['name']);
                                echo $search ? str_ireplace($search, "<mark>$search</mark>", $name) : $name;
                            ?>
                        </a>
                    </td>
                    <td>
                        <div style="font-weight: 500; color: #2c3e50;"><?= htmlspecialchars($s['class_name'] ?? 'General') ?></div>
                        <span class="year-tag"><?= htmlspecialchars($s['study_year'] ?? 'N/A') ?></span>
                    </td>
                    <td>
                        <?php 
                            $gpa = $s['gpa'] ?? 0.00;
                            $class = ($gpa >= 3.0) ? 'grade-high' : (($gpa >= 2.0) ? 'grade-mid' : 'grade-low');
                        ?>
                        <span class="<?= $class ?>" style="font-size: 1.1rem;">
                            <?= number_format($gpa, 2) ?>
                        </span>
                    </td>
                    <td class="contact-info">
                        <?= htmlspecialchars($s['email']) ?><br>
                        <span style="color: #bdc3c7;"><?= htmlspecialchars($s['phone'] ?? '---') ?></span>
                    </td>
                    <td>
                        <a href="edit_student.php?id=<?= $s['id'] ?>" style="color: #3498db; text-decoration: none; font-size: 0.9rem;">Edit</a>
                        <span style="color: #eee; margin: 0 5px;">|</span>
                        <a href="remove_student.php?id=<?= $s['id'] ?>" style="color: #e74c3c; text-decoration: none; font-size: 0.9rem;" onclick="return confirm('Permanent delete?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 30px; text-align: center;">
            <a href="dashboard.php" style="color: #95a5a6; text-decoration: none; font-size: 0.9rem;">← Return to Admin Dashboard</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>