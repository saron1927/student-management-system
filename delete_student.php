<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Check if an ID was sent via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Use a prepared statement for security
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect back to the list with a success message
        header("Location: list_students.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        die("Error deleting record: " . $e->getMessage());
    }
} else {
    // If no ID is found, just go back
    header("Location: list_students.php");
    exit();
}