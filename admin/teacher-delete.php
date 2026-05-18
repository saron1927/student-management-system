<?php
// admin/teacher-delete.php
require_once '../includes/auth.php';
checkRole('admin');
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0); // This is users.id
if ($id <= 0) {
    header("Location: teachers.php?error=" . urlencode("Invalid teacher ID!"));
    exit();
}

try {
    // Delete user from users table (cascades to teachers table)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'");
    $stmt->execute([$id]);
    
    header("Location: teachers.php?success=" . urlencode("Teacher account and profile deleted successfully!"));
    exit();
} catch (PDOException $e) {
    header("Location: teachers.php?error=" . urlencode("Error deleting teacher: " . $e->getMessage()));
    exit();
}
?>
