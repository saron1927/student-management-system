<?php
// admin/department-delete.php
require_once '../includes/auth.php';
checkRole('admin');
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: departments.php?error=" . urlencode("Invalid department ID!"));
    exit();
}

try {
    // Attempt to delete
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: departments.php?success=" . urlencode("Department deleted successfully!"));
    exit();
} catch (PDOException $e) {
    // Check if error is due to foreign key constraints (SQLSTATE 23000)
    if ($e->getCode() == 23000) {
        $error = "Cannot delete this department because there are courses, teachers, or students registered under it. Please reassign or delete those records first.";
    } else {
        $error = "Database error: " . $e->getMessage();
    }
    header("Location: departments.php?error=" . urlencode($error));
    exit();
}
?>
