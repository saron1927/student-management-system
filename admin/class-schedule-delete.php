<?php
// admin/class-schedule-delete.php
require_once '../includes/header.php';
checkRole('admin');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: class-schedule.php?error=' . urlencode('Invalid schedule ID.'));
    exit;
}

$stmt = $pdo->prepare("DELETE FROM class_schedule WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    header('Location: class-schedule.php?success=' . urlencode('Schedule entry deleted successfully.'));
} else {
    header('Location: class-schedule.php?error=' . urlencode('Schedule entry not found or already deleted.'));
}
exit;
