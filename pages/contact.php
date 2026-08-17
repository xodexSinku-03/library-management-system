<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add isManager check
$isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Library Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .flash-message {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .flash-message.success {
            background-color: #4CAF50;
            color: #fff;
        }

        .flash-message.error {
            background-color: #f44336;
            color: #fff;
        }

        .contact-form-container {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .contact-form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            width: 120px;
            margin-right: 10px;
            font-weight: bold;
            text-align: left;
        }

        .form-group input,
        .form-group textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .contact-form-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .contact-form-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <header>
        <h1>Contact Us</h1>
    </header>

    <nav>
        <a href="../index.php">Home</a>
        <?php if ($isManager): ?>
            <a href="users.php">Users</a>
        <?php endif; ?>
        <a href="transactions.php">Transactions</a>
        <a href="contact.php" class="active">Contact</a>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="flash-message success" id="flashMessage">
            <?= $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="flash-message error" id="flashMessage">
            <?= $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="content">
        <h2>Get in Touch</h2>
        <p>We'd love to hear from you! Please use the form below or email us directly at
            <a href="mailto:iit.aspirant10@gmail.com">iit.aspirant10@gmail.com</a>.
        </p>

        <div class="contact-form-container">
            <h2>Send a Message</h2>
            <form action="process_contact.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" placeholder="Your message..." required></textarea>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <footer>
        <p>© 2025 Library Management System | All Rights Reserved</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach((message) => {
                setTimeout(() => {
                    message.style.opacity = "0";
                    setTimeout(() => message.remove(), 500);
                }, 3000);
            });
        });
    </script>
</body>

</html>