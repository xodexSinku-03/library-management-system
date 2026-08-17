<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . "/db.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error_message'] = "You must be logged in to buy a book.";
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: ../index.php");
    exit;
}
$user_id = $user['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_index'])) {
    $book_index = (int)$_POST['book_index'];
    $books = json_decode(file_get_contents('../data/books.json'), true);
    if (!isset($books[$book_index])) {
        $_SESSION['error_message'] = "Book not found.";
        header("Location: ../index.php");
        exit;
    }

    $book = $books[$book_index];
    $price = $book['price'] ?? 0.00;

    if ($price <= 0) {
        $_SESSION['error_message'] = "This book is not available for purchase.";
        header("Location: ../index.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM books WHERE title = ? AND author = ?");
    $stmt->execute([$book['title'], $book['author']]);
    $book_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $book_id = $book_row ? $book_row['id'] : null;

    if (!$book_id) {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, description, link, price, added_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$book['title'], $book['author'], $book['description'], $book['link'], $price, $user_id]);
        $book_id = $pdo->lastInsertId();
    }

    $purchase_date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO purchases (user_id, book_id, purchase_date, price) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $book_id, $purchase_date, $price])) {
        $_SESSION['success_message'] = "Book purchased successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to process purchase.";
    }
    header("Location: ../index.php");
    exit;
}

header("Location: ../index.php");
exit;
?>