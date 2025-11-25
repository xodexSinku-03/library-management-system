<?php
$password = "manager123"; // Replace with your desired password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashedPassword . "\n";
?>