<?php
require 'config.php';

// 1. Logic for Search
$search = $_GET['search'] ?? '';

try {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE name LIKE ?");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM students");
    }
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Student List</h2>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'added'): ?>
                <p style="color: green; font-weight: bold; background: #e8f5e9; padding: 10px; border: 1px solid green; border-radius: 5px;">
                    ✓ Student Added Successfully!
                </p>
            <?php elseif ($_GET['msg'] == 'deleted'): ?>
                <p style="color: #721c24; font-weight: bold; background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px;">
                    🗑️ Student Removed Successfully!
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <form method="GET" action="list_students.php" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <a href="list_students.php" style="text-decoration: none; margin-left: 10px; color: #666;">Reset</a>
        </form>

        <?php if (!$students): ?>
            <p>No students found. <a href="create_student.php">Add one now?</a></p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td>
                        <a href="edit_student.php?id=<?= $s['id'] ?>">Edit</a> | 
                        <a href="remove_student.php?id=<?= $s['id'] ?>" 
                           style="color: red; text-decoration: none;" 
                           onclick="return confirm('Stop! Are you sure you want to delete <?= htmlspecialchars($s['name']) ?>?');">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <br>
        <a href="index.html">Back to Home</a>
    </div>
    <script src="script.js"></script>
</body>
</html>