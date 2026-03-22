<?php
require 'config.php';
$stmt = $pdo->query("SELECT * FROM students");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
require 'config.php'; // Make sure this file has the correct DB name

try {
    $stmt = $pdo->query("SELECT * FROM students");
    $students = $stmt->fetchAll();

    if (!$students) {
        echo "The table is empty. Try adding a student in create_student.php!";
    }
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Student List</h2>
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
                    <a href="remove_student.php?id=<?= $s['id'] ?>" class="delete-link">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <a href="index.html">Back to Home</a>
    </div>
    <script src="script.js"></script>
</body>
</html>