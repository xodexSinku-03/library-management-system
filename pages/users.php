<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once dirname(__DIR__) . "/db.php";

// Restrict access to managers only
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'manager') {
    $_SESSION['error_message'] = "You must be a manager to access this page.";
    header("Location: ../index.php");
    exit;
}

// Handle add user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);
    $role     = $_POST['role'];

    if (empty($fullname) || empty($email) || empty($username) || empty($password) || empty($phone) || empty($role)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!in_array($role, ['user', 'manager'])) {
        $_SESSION['error_message'] = "Invalid role selected.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Username already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$fullname, $email, $username, $hashedPassword, $phone, $role])) {
                $_SESSION['success_message'] = "User added successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to add user.";
            }
        }
    }
    header("Location: users.php");
    exit;
}

// Handle edit user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $id       = $_POST['id'];
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = $_POST['role'];

    if (empty($fullname) || empty($email) || empty($phone) || empty($role)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!in_array($role, ['user', 'manager'])) {
        $_SESSION['error_message'] = "Invalid role selected.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, role = ? WHERE id = ?");
        if ($stmt->execute([$fullname, $email, $phone, $role, $id])) {
            $_SESSION['success_message'] = "User updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update user.";
        }
    }
    header("Location: users.php");
    exit;
}

// Handle delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $id   = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success_message'] = "User deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete user.";
    }
    header("Location: users.php");
    exit;
}

// Fetch all users
$stmt  = $pdo->query("SELECT id, fullname, email, username, phone, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Library Management</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/users.css">
</head>
<body>
    <header>
        <h1>Library Users</h1>
    </header>

    <nav>
        <a href="../index.php">Home</a>
        <a href="books.php">Books</a>
        <a href="users.php" class="active">Users</a>
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
        <h2>Manage Users</h2>

        <!-- Add User Form -->
        <div class="add-user-form">
    <h3>Add New User</h3>
    <form action="users.php" method="POST">
        <input type="hidden" name="add_user" value="1">
        <div class="form-group">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" placeholder="Enter full name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter email" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Select role</option>
                <option value="user">User</option>
                <option value="manager">Manager</option>
            </select>
        </div>
        <button type="submit">Add User</button>
    </form>
</div>

<style>
    .add-user-form {
        max-width: 500px; /* Slightly wider for side-by-side layout */
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .add-user-form h3 {
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
        width: 120px; /* Fixed width for labels */
        margin-right: 10px;
        font-weight: bold;
        text-align: left;
    }
    .form-group input,
    .form-group select {
        flex: 1; /* Takes remaining space */
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }
    .add-user-form button {
        width: 100%;
        padding: 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .add-user-form button:hover {
        background-color: #45a049;
    }
</style>

        <!-- User List -->
        <h3>User List</h3>
        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>User Details</th>
                        <th>Username</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($user['fullname']); ?><br>
                                <?= htmlspecialchars($user['email']); ?>
                            </td>
                            <td><?= htmlspecialchars($user['username']); ?></td>
                            <td><?= htmlspecialchars($user['phone']); ?></td>
                            <td><?= htmlspecialchars($user['role']); ?></td>
                            <td>
                                <form action="users.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="edit_user" value="1">
                                    <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                    <button type="button" onclick="showEditForm(<?= $user['id']; ?>)">Edit</button>
                                </form>
                                <form action="users.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="delete_user" value="1">
                                    <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-form-<?= $user['id']; ?>" style="display:none;">
                            <td colspan="5">
                                <form action="users.php" method="POST" class="add-user-form">
                                    <input type="hidden" name="edit_user" value="1">
                                    <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                    <label for="fullname-<?= $user['id']; ?>">Full Name</label>
                                    <input type="text" id="fullname-<?= $user['id']; ?>" name="fullname" value="<?= htmlspecialchars($user['fullname']); ?>" required>
                                    <label for="email-<?= $user['id']; ?>">Email</label>
                                    <input type="email" id="email-<?= $user['id']; ?>" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                                    <label for="phone-<?= $user['id']; ?>">Phone</label>
                                    <input type="tel" id="phone-<?= $user['id']; ?>" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>
                                    <label for="role-<?= $user['id']; ?>">Role</label>
                                    <select id="role-<?= $user['id']; ?>" name="role" required>
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                    </select>
                                    <button type="submit">Save</button>
                                    <button type="button" onclick="hideEditForm(<?= $user['id']; ?>)">Cancel</button>
                                </form>
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
        function showEditForm(id) {
            document.getElementById(`edit-form-${id}`).style.display = 'table-row';
        }

        function hideEditForm(id) {
            document.getElementById(`edit-form-${id}`).style.display = 'none';
        }

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