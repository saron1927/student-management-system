<?php
require 'config.php';

if (isset($_GET['id'])) {
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['id']]);
}

header("Location: list_students.php?msg=added");
?>
<?php
require 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    // This is the most important line! It sends the "deleted" signal back.
    header("Location: list_students.php?msg=deleted");
    exit();
}
?>