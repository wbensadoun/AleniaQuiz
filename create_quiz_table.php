<?php
require_once 'db_connect.php';

// Supprimer les anciennes tables si elles existent
$sql = "DROP TABLE IF EXISTS results;";
$conn->query($sql);

$sql = "DROP TABLE IF EXISTS quizzes;";
$conn->query($sql);

// Créer la table quizzes avec la colonne question_timer
$sql = "CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    questions_json LONGTEXT NOT NULL,
    total_questions INT NOT NULL,
    question_timer INT NOT NULL DEFAULT 30, -- Temps par défaut : 30 secondes
    UNIQUE KEY unique_file_path (file_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table quizzes créée avec succès\n";
} else {
    echo "Erreur lors de la création de la table quizzes: " . $conn->error . "\n";
}

// Créer la table results avec la référence au quiz
$sql = "CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table results créée avec succès\n";
} else {
    echo "Erreur lors de la création de la table results: " . $conn->error . "\n";
}

$conn->close();
?>
