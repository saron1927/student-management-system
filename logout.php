<?php
session_start();
session_destroy(); // Clears all your login data
header("Location: login.php");
exit();
?>