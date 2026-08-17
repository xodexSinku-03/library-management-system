<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . "/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm-password"];
    $phone = trim($_POST["phone"]);

    if ($password !== $confirmPassword) {
        $_SESSION['error_message'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Username already exists! Please choose a different username.";
            header("Location: register.php");
            exit();
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, phone) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$fullname, $email, $username, $hashedPassword, $phone])) {
                $_SESSION['success_message'] = "Registration successful! You can now login.";
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Registration failed. Please try again later.";
                header("Location: register.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Register</h1>
    </header>

    <nav>
        <a href="../index.php">Home</a>
        <a href="users.php">Users</a>
        <a href="transactions.php">Transactions</a>
        <a href="contact.php">Contact</a>
        <a href="register.php" class="active">Register</a>
    </nav>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="flash-message error" id="flashMessage">
            <?= $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="content">
        <div class="register-container">
            <h2>Create an Account</h2>
            <form action="register.php" method="POST">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <footer>
        <p>© 2025 Library Management System | All Rights Reserved</p>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>