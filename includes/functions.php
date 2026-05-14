<?php
// includes/functions.php

/**
 * Calculate GPA from marks
 * Basic scale: 90+ = 4.0, 80+ = 3.5, 70+ = 3.0, 60+ = 2.5, 50+ = 2.0, <50 = 0.0
 */
function calculateGPA($marks) {
    if ($marks >= 90) return 4.0;
    if ($marks >= 80) return 3.5;
    if ($marks >= 70) return 3.0;
    if ($marks >= 60) return 2.5;
    if ($marks >= 50) return 2.0;
    return 0.0;
}

/**
 * Format Grade from marks
 */
function getGradeLetter($marks) {
    if ($marks >= 90) return 'A+';
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';
    return 'F';
}

/**
 * Securely clean input
 */
function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
