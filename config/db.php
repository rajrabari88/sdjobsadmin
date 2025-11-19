<?php
// Database configuration
$servername = "localhost"; // usually localhost for XAMPP
$username = "root";        // default username in XAMPP
$password = "";            // default password is empty in XAMPP
$dbname = "sdjobs_db";        // tumhara database name (phpMyAdmin me jaisa diya hai)

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
