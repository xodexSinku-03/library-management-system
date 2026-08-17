<?php
require_once "../db.php";

$fullname = "Library Manager";
$email = "manager@library.com";
$username = "manager";
$password = "manager123"; // Change this as needed
$phone = "1234567890";
$role = "manager";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if username already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->rowCount() > 0) {
    echo "Manager already exists!\n";
} else {
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$fullname, $email, $username, $hashedPassword, $phone, $role])) {
        echo "Manager created successfully!\n";
        echo "Username: manager\nPassword: $password\n";
    } else {
        echo "Failed to create manager.\n";
    }
}
?>