<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quiz_app";  // Changé de quizzapp à quiz_app

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
