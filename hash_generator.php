<?php
// hash_generator.php
$password = '123'; // Change this to whatever you want
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Password Hash Generator</h3>";
echo "<strong>Password:</strong> " . $password . "<br>";
echo "<strong>Hashed Password:</strong> <pre>" . $hashed_password . "</pre>";
echo "<br><small>Copy the hashed password above and paste it into your database 'password' field.</small>";
?>
