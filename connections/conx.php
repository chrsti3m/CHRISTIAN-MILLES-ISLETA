<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if it's not already started
}
// Database credentials
$host = 'localhost'; // or your host IP
$dbname = 'dandg';
$username = 'root';
$password = '';

// Attempt to connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}



