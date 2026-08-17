<?php
$host   = getenv('DB_HOST') ?: "localhost";
$dbname = getenv('DB_NAME') ?: "library"; 
$user   = getenv('DB_USER') ?: "root";
$pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ""; 

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>