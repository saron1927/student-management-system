<?php
// admin/course-delete.php
require_once '../includes/auth.php';
checkRole('admin');
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: courses.php?error=" . urlencode("Invalid course ID!"));
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: courses.php?success=" . urlencode("Course deleted successfully!"));
    exit();
} catch (PDOException $e) {
    header("Location: courses.php?error=" . urlencode("Error deleting course: " . $e->getMessage()));
    exit();
}
?>
