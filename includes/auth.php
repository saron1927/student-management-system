<?php
// includes/auth.php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkRole($roles) {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }
    
    if (is_array($roles)) {
        if (!in_array($_SESSION['role'], $roles)) {
            header("Location: ../unauthorized.php");
            exit();
        }
    } else {
        if ($_SESSION['role'] !== $roles) {
            header("Location: ../unauthorized.php");
            exit();
        }
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        $role = $_SESSION['role'];
        header("Location: $role/dashboard.php");
        exit();
    }
}
?>
