<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion au serveur MySQL sans sélectionner de base de données
$conn = new mysqli("localhost", "root", "");

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Supprimer la base de données si elle existe
$conn->query("DROP DATABASE IF EXISTS quizzapp");

// Créer la base de données
if (!$conn->query("CREATE DATABASE quizzapp")) {
    die("Erreur lors de la création de la base de données : " . $conn->error);
}

// Sélectionner la base de données
$conn->select_db("quizzapp");

// Créer la table users
$sql = "CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    role VARCHAR(22) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!$conn->query($sql)) {
    die("Erreur lors de la création de la table users : " . $conn->error);
}

// Créer la table quizzes
$sql = "CREATE TABLE quizzes (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    professor_id INT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (professor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!$conn->query($sql)) {
    die("Erreur lors de la création de la table quizzes : " . $conn->error);
}

// Créer la table questions
$sql = "CREATE TABLE questions (
    id INT NOT NULL AUTO_INCREMENT,
    question TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    quiz_id INT NOT NULL,
    timer INT NOT NULL DEFAULT 30,
    scenario TEXT,
    PRIMARY KEY (id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!$conn->query($sql)) {
    die("Erreur lors de la création de la table questions : " . $conn->error);
}

// Créer la table results
$sql = "CREATE TABLE results (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (!$conn->query($sql)) {
    die("Erreur lors de la création de la table results : " . $conn->error);
}

// Insérer les utilisateurs de test
$sql = "INSERT INTO users (username, password, role) VALUES
    ('admin@quiz.com', 'admin123', 'admin'),
    ('professeur@quiz.com', 'prof123', 'professeur'),
    ('eleve1@quiz.com', 'eleve123', 'eleve'),
    ('eleve2@quiz.com', 'eleve123', 'eleve'),
    ('eleve3@quiz.com', 'eleve123', 'eleve')";

if (!$conn->query($sql)) {
    die("Erreur lors de l'insertion des utilisateurs : " . $conn->error);
}

// Insérer un quiz de test
$sql = "INSERT INTO quizzes (title, description, category, professor_id) VALUES
    ('Quiz de test', 'Description du quiz de test', 'Général', 2)";

if (!$conn->query($sql)) {
    die("Erreur lors de l'insertion du quiz : " . $conn->error);
}

// Insérer des questions de test
$sql = "INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_answer, quiz_id, scenario) VALUES
    ('Quelle est la capitale de la France ?', 'Londres', 'Paris', 'Berlin', 'Madrid', 'B', 1, 'Un touriste vous demande la capitale.'),
    ('Combien font 2 + 2 ?', '3', '4', '5', '6', 'B', 1, 'Un calcul simple.')";

if (!$conn->query($sql)) {
    die("Erreur lors de l'insertion des questions : " . $conn->error);
}

echo "Base de données mise à jour avec succès !";
$conn->close();
