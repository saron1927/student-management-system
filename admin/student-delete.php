<?php
// admin/student-delete.php
require_once '../includes/auth.php';
checkRole('admin');
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0); // This is users.id
if ($id <= 0) {
    header("Location: students.php?error=" . urlencode("Invalid student ID!"));
    exit();
}

try {
    // Delete user from users table (cascades to students table and all related academic records)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$id]);
    
    header("Location: students.php?success=" . urlencode("Student account and academic record deleted successfully!"));
    exit();
} catch (PDOException $e) {
    header("Location: students.php?error=" . urlencode("Error deleting student: " . $e->getMessage()));
    exit();
}
?>
