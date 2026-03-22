<?php
require 'config.php';

if (isset($_GET['id'])) {
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['id']]);
}

header("Location: list_students.php");
?>