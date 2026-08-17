<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    require_once dirname(__FILE__) . "/pages/process-login.php";
}

// Check if the logged-in user is a manager
$isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Library Management System</h1>
        <p>Welcome to the Library! Libraries are the heart of knowledge and community.</p>
    </header>

    <nav>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="index.php">Home</a>
            <a href="pages/books.php" class="active">Books</a>
            <?php if ($isManager): ?>
                <a href="pages/users.php">Users</a>
                <a href="pages/transactions.php">Transactions</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="index.php" class="active">Home</a>
        <?php endif; ?>
        <a href="pages/contact.php">Contact</a>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="pages/logout.php">Logout</a>
        <?php else: ?>
            <a href="pages/register.php">Register</a>
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
        <h2>Welcome to the Library Management System</h2>
        <p>Manage your books, users, and transactions with ease.</p>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <div class="book-search">
                <h3>Search for Books</h3>
                <div class="search-bar">
                    <input type="text" id="searchInput" list="bookList" placeholder="Search by title">
                    <datalist id="bookList"></datalist>
                    <button id="searchButton">Search</button>
                </div>
            </div>
            <div id="searchResult" class="search-results"></div>

            <?php if ($isManager): ?>
                <div class="manager-controls">
                    <h3>Manager Dashboard</h3>
                    <p>Welcome, Manager! You have full control over the library system.</p>
                    <div class="manager-options">
                        <a href="pages/books.php" class="manager-btn">Manage Books</a>
                        <a href="pages/users.php" class="manager-btn">Manage Users</a>
                        <a href="pages/transactions.php" class="manager-btn">Manage Transactions</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Please log in to search for books.</p>
            <div class="login-container">
                <h2>Login</h2>
                <form id="loginForm" action="index.php" method="POST">
                    <input type="hidden" name="login" value="1">
                    <label for="username"><b>Username</b></label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    <label for="password"><b>Password</b></label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="submit">Sign In</button>
                    <a href="pages/forgot-password.html" class="forgot-password">Forgot Password?</a>
                </form>
            </div>
        <?php endif; ?>

        <div class="features">
            <div class="feature-card">
                <h3>Book Management</h3>
                <p>Effortlessly add, remove, and edit books in the library collection.</p>
            </div>
            <div class="feature-card">
                <h3>User Management</h3>
                <p>Manage library users and their borrowing activity.</p>
            </div>
            <div class="feature-card">
                <h3>Transaction History</h3>
                <p>Keep track of issued and returned books, and generate reports.</p>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2025 Library Management System | All Rights Reserved</p>
    </footer>

    <script src="assets/js/book-search.js"></script>
</body>
</html>