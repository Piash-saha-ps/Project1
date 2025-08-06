<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "project"; // ✅ must match the name of your database in phpMyAdmin

$conn = new mysqli($host, $user, $password, $dbname);

// Error check
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Character set
$conn->set_charset("utf8mb4");
?>
