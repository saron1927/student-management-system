<?php
session_start();

// 1. SECURITY CHECK: Protect the page from non-logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// 2. SEARCH LOGIC: Get search term if it exists
$search = $_GET['search'] ?? '';

try {
    if ($search) {
        // Search by name using LIKE for partial matches
        $stmt = $pdo->prepare("SELECT * FROM students WHERE name LIKE ? ORDER BY name ASC");
        $stmt->execute(["%$search%"]);
    } else {
        // Fetch all students if no search is active
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
    <title>Student Management System | List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #34495e; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc; }
        .stats-container { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .badge { background: #3498db; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9rem; }
        /* Professional link styling */
        .student-link { text-decoration: none; color: #2c3e50; font-weight: bold; transition: color 0.2s; }
        .student-link:hover { color: #3498db; text-decoration: underline; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <h2>Student Directory</h2>

        <div class="stats-container">
            <div>
                <span class="badge">Total Students: <?= count($students) ?></span>
                <?php if ($search): ?>
                    <span style="color: #7f8c8d; margin-left: 10px;">Results for "<?= htmlspecialchars($search) ?>"</span>
                <?php endif; ?>
            </div>
            <a href="export_students.php" style="background: #27ae60; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                📥 Export to CSV
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php 
                $msgClass = "flash-message";
                $style = "padding: 10px; border-radius: 5px; margin-bottom: 15px; font-weight: bold;";
                if ($_GET['msg'] == 'added') {
                    echo "<p class='$msgClass' style='$style color: green; background: #e8f5e9; border: 1px solid green;'>✓ Student Added Successfully!</p>";
                } elseif ($_GET['msg'] == 'deleted') {
                    echo "<p class='$msgClass' style='$style color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb;'>🗑️ Student Removed Successfully!</p>";
                } elseif ($_GET['msg'] == 'updated') {
                    echo "<p class='$msgClass' style='$style color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb;'>✏️ Student Updated Successfully!</p>";
                }
            ?>
        <?php endif; ?>

        <form method="GET" action="list_students.php" style="margin-bottom: 25px;">
            <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>" style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
            <button type="submit" style="padding: 10px 20px; cursor: pointer;">Search</button>
            <?php if ($search): ?>
                <a href="list_students.php" style="margin-left: 10px; color: #e74c3c; text-decoration: none;">Clear Search</a>
            <?php endif; ?>
        </form>

        <?php if (!$students): ?>
            <div style="text-align: center; padding: 50px; background: #f9f9f9; border-radius: 10px;">
                <p>No students found in the database.</p>
                <a href="create_student.php" class="button" style="display:inline-block; padding: 10px 20px; background:#3498db; color:white; text-decoration:none; border-radius:5px;">Add Your First Student</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td style="width: 60px;">
                            <?php 
                                $imagePath = "uploads/" . (!empty($s['photo']) ? $s['photo'] : 'default.png');
                                // Fallback check to ensure image actually exists on server
                                if (!file_exists($imagePath)) {
                                    $imagePath = "uploads/default.png";
                                }
                            ?>
                            <img src="<?= $imagePath ?>" class="avatar" alt="Profile">
                        </td>

                        <td><?= $s['id'] ?></td>

                        <td>
                            <a href="view_student.php?id=<?= $s['id'] ?>" class="student-link">
                                <?php 
                                $name = htmlspecialchars($s['name']);
                                if ($search) {
                                    // Highlight search match
                                    echo str_ireplace($search, "<mark style='background: #f1c40f; padding: 2px;'>$search</mark>", $name);
                                } else {
                                    echo $name;
                                }
                                ?>
                            </a>
                        </td>

                        <td><?= htmlspecialchars($s['email']) ?></td>

                        <td>
                            <a href="edit_student.php?id=<?= $s['id'] ?>" style="color: #3498db; text-decoration: none; font-weight: bold;">Edit</a>
                            <span style="color: #ccc; margin: 0 5px;">|</span>
                            <a href="remove_student.php?id=<?= $s['id'] ?>" 
                               class="delete-link" 
                               style="color: #e74c3c; text-decoration: none; font-weight: bold;"
                               onclick="return confirm('Are you sure you want to delete this student?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="dashboard.php" style="color: #666; text-decoration: none;">← Back to Dashboard</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>