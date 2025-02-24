<?php
require_once 'db_connect.php';

// Supprimer la table questions si elle existe
$sql = "DROP TABLE IF EXISTS questions";
$conn->query($sql);

// Créer la nouvelle table questions
$sql = "CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    options TEXT NOT NULL,
    correct_letter CHAR(1) NOT NULL,
    scenario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Supprimer la table results si elle existe
$sql = "DROP TABLE IF EXISTS results";
$conn->query($sql);

// Créer la nouvelle table results
$sql = "CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
$conn->query($sql);

echo "Base de données mise à jour avec succès !";
?>
