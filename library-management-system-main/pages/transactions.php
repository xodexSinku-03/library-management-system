<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . "/db.php";

// Restrict to logged-in users only
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error_message'] = "You must be logged in to access this page.";
    header("Location: ../index.php");
    exit;
}

$isManager     = isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
$currentUserId = null;

// Fetch current user's ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    $currentUserId = $user['id'];
} else {
    $_SESSION['error_message'] = "User not found.";
    header("Location: ../index.php");
    exit;
}

// Fetch users for dropdown (manager only)
$users = [];
if ($isManager) {
    $usersStmt = $pdo->query("SELECT id, username FROM users ORDER BY username ASC");
    $users     = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch books from JSON file
$booksJsonPath = dirname(__DIR__) . "/data/books.json";
$books         = [];
if (file_exists($booksJsonPath)) {
    $books = json_decode(file_get_contents($booksJsonPath), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $_SESSION['error_message'] = "Error reading books.json: " . json_last_error_msg();
        $books = [];
    } elseif (empty($books)) {
        $_SESSION['error_message'] = "No books found in books.json.";
    }
} else {
    $_SESSION['error_message'] = "books.json file not found.";
}

// Auto-return overdue books
$currentDate = date('Y-m-d');
$stmt = $pdo->prepare("UPDATE transactions 
                       SET status = 'returned' 
                       WHERE status = 'issued' 
                       AND return_date IS NOT NULL 
                       AND return_date < ?");
$stmt->execute([$currentDate]);
if ($stmt->rowCount() > 0) {
    $_SESSION['success_message'] = "Some books were automatically returned as their return dates expired.";
}

// Handle add transaction (manager only)
if ($isManager && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    $user_id    = trim($_POST['user_id']);
    $book_id    = isset($_POST['book_id']) ? (int)$_POST['book_id'] : null;
    $issue_date = trim($_POST['issue_date']);
    $return_date = trim($_POST['return_date']);
    $price      = trim($_POST['price']);

    if (empty($user_id) || $book_id === null || empty($issue_date) || empty($return_date) || empty($price)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $_SESSION['error_message'] = "Price must be a positive number.";
    } elseif ($issue_date > $return_date) {
        $_SESSION['error_message'] = "Return date must be after issue date.";
    } elseif (!isset($books[$book_id])) {
        $_SESSION['error_message'] = "Selected book does not exist.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error_message'] = "Selected user does not exist.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, book_id, issue_date, return_date, price, status) 
                                       VALUES (?, ?, ?, ?, ?, 'issued')");
                $success = $stmt->execute([$user_id, $book_id, $issue_date, $return_date, $price]);
                if ($success) {
                    $_SESSION['success_message'] = "Book '{$books[$book_id]['title']}' issued successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to issue book.";
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        }
    }
    header("Location: transactions.php");
    exit;
}

// Handle manual return book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_book'])) {
    $transaction_id = $_POST['transaction_id'];
    $return_date    = trim($_POST['return_date']);

    if (empty($return_date)) {
        $_SESSION['error_message'] = "Return date is required.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM transactions WHERE id = ? AND status = 'issued'");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            if ($isManager || $transaction['user_id'] == $currentUserId) {
                $stmt = $pdo->prepare("UPDATE transactions SET return_date = ?, status = 'returned' WHERE id = ?");
                if ($stmt->execute([$return_date, $transaction_id])) {
                    $_SESSION['success_message'] = "Book returned successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to return book.";
                }
            } else {
                $_SESSION['error_message'] = "You can only return your own books.";
            }
        } else {
            $_SESSION['error_message'] = "Transaction not found or already returned.";
        }
    }
    header("Location: transactions.php");
    exit;
}

// Fetch transactions with book filter
$bookFilter = isset($_GET['book_id']) && !empty($_GET['book_id']) ? (int)$_GET['book_id'] : null;
$query      = "SELECT t.id, t.user_id, u.username, t.book_id, t.issue_date, t.return_date, t.price, t.status
               FROM transactions t
               JOIN users u ON t.user_id = u.id";
$where      = [];
$params     = [];

if ($bookFilter !== null) {
    $where[]  = "t.book_id = ?";
    $params[] = $bookFilter;
}

if (!$isManager) {
    $where[]  = "t.user_id = ?";
    $params[] = $currentUserId;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY t.issue_date DESC";
$stmt   = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map book titles from JSON to transactions
foreach ($transactions as &$transaction) {
    $bookIndex = $transaction['book_id'];
    $transaction['book_title'] = isset($books[$bookIndex]['title']) ? $books[$bookIndex]['title'] : "Unknown Book (ID: $bookIndex)";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Library Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .transaction-form {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            max-width: 400px;
        }
        .transaction-form label {
            margin-top: 10px;
            font-weight: bold;
        }
        .transaction-form input,
        .transaction-form select {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
        }
        .transaction-form button {
            margin-top: 15px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .transaction-form button:hover {
            background-color: #45a049;
        }
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .transaction-table th,
        .transaction-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .transaction-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .transaction-table tr:hover {
            background-color: #f9f9f9;
        }
        .transaction-table button {
            padding: 5px 10px;
            background-color: #00796B;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .transaction-table button:hover {
            background-color: #004D40;
        }
        .filter-form {
            margin: 20px 0;
            max-width: 400px;
        }
        .filter-form select {
            padding: 8px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Library Transactions</h1>
    </header>

    <nav>
        <a href="../index.php">Home</a>
        <a href="books.php">Books</a>
        <?php if ($isManager): ?>
            <a href="users.php">Users</a>
        <?php endif; ?>
        <a href="transactions.php" class="active">Transactions</a>
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
        <h2>Transaction History</h2>

        <!-- Issue Book Form (Manager Only) -->
        <?php if ($isManager): ?>
            <div class="transaction-form">
                <h3>Issue a Book</h3>
                <form action="transactions.php" method="POST">
                    <input type="hidden" name="add_transaction" value="1">
                    <label for="user_id">User</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="book_id">Book</label>
                    <select id="book_id" name="book_id" required>
                        <option value="">Select Book</option>
                        <?php if (empty($books)): ?>
                            <option value="" disabled>No books available</option>
                        <?php else: ?>
                            <?php foreach ($books as $index => $book): ?>
                                <option value="<?= $index; ?>"><?= htmlspecialchars($book['title']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <label for="issue_date">Issue Date</label>
                    <input type="date" id="issue_date" name="issue_date" required max="<?= date('Y-m-d'); ?>">
                    <label for="return_date">Return Date</label>
                    <input type="date" id="return_date" name="return_date" required min="<?= date('Y-m-d'); ?>">
                    <label for="price">Price (e.g., rental fee)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                    <button type="submit">Issue Book</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Book Filter Form -->
        <div class="filter-form">
            <h3>Filter by Book</h3>
            <form action="transactions.php" method="GET">
                <select id="book_filter" name="book_id" onchange="this.form.submit()">
                    <option value="">All Books</option>
                    <?php foreach ($books as $index => $book): ?>
                        <option value="<?= $index; ?>" <?= $bookFilter === $index ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($book['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Transaction History -->
        <?php if (empty($transactions)): ?>
            <p>No transactions found.</p>
        <?php else: ?>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <?php if ($isManager): ?>
                            <th>User</th>
                        <?php endif; ?>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Return Date</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <?php if ($isManager): ?>
                                <td><?= htmlspecialchars($transaction['username']); ?></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($transaction['book_title']); ?></td>
                            <td><?= htmlspecialchars($transaction['issue_date']); ?></td>
                            <td><?= htmlspecialchars($transaction['return_date']) ?: 'Not Returned'; ?></td>
                            <td>$<?= number_format($transaction['price'], 2); ?></td>
                            <td><?= htmlspecialchars($transaction['status']); ?></td>
                            <td>
                                <?php if ($transaction['status'] === 'issued' && ($isManager || $transaction['user_id'] == $currentUserId)): ?>
                                    <form action="transactions.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="return_book" value="1">
                                        <input type="hidden" name="transaction_id" value="<?= $transaction['id']; ?>">
                                        <input type="date" name="return_date" required max="<?= date('Y-m-d'); ?>" style="padding: 5px; margin-right: 5px;">
                                        <button type="submit">Return</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
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