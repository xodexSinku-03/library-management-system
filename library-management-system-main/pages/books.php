<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . "/db.php";

// Restrict to managers only
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'manager') {
    $_SESSION['error_message'] = "You must be a manager to access this page.";
    header("Location: ../index.php");
    exit;
}

// Handle book addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $link = trim($_POST['link']);
    $added_by = $_SESSION['username'];

    if (empty($title) || empty($author) || empty($description) || empty($link)) {
        $_SESSION['error_message'] = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, description, link, added_by) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $author, $description, $link, $added_by])) {
            $_SESSION['success_message'] = "Book added successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to add book.";
        }
    }
    header("Location: books.php");
    exit;
}

// Fetch all books
$stmt = $pdo->query("SELECT * FROM books");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Library Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Manage Books</h1>
    </header>

    <nav>
        <a href="../index.php">Home</a>
        <a href="books.php" class="active">Books</a>
        <a href="users.php">Users</a>
        <a href="transactions.php">Transactions</a>
        <a href="contact.php">Contact</a>
        <a href="logout.php">Logout</a>
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
        <h2>Add a New Book</h2>
        <form action="books.php" method="POST">
            <input type="hidden" name="add_book" value="1">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
            <label for="author">Author</label>
            <input type="text" id="author" name="author" required>
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" required></textarea>
            <label for="link">Link</label>
            <input type="url" id="link" name="link" required>
            <button type="submit">Add Book</button>
        </form>

        <h2>Book List</h2>
        <?php if (empty($books)): ?>
            <p>No books available.</p>
        <?php else: ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Description</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']); ?></td>
                            <td><?= htmlspecialchars($book['author']); ?></td>
                            <td><?= htmlspecialchars($book['description']); ?></td>
                            <td><a href="<?= htmlspecialchars($book['link']); ?>" target="_blank">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2025 Library Management System | All Rights Reserved</p>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>