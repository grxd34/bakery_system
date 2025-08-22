<?php
// config.php - Updated version
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'bakery_db';
$username = 'root';
$password = '';

// Define password hashing options
if (!defined('PASSWORD_ALGO')) {
    define('PASSWORD_ALGO', PASSWORD_BCRYPT);
}

if (!defined('PASSWORD_OPTIONS')) {
    define('PASSWORD_OPTIONS', ['cost' => 12]);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>