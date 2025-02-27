<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

function executeSQLUpdate($conn, $sql, $description) {
    try {
        if ($conn->query($sql) === TRUE) {
            echo "<div style='color: green;'>✓ Succès : " . $description . "</div>";
        } else {
            echo "<div style='color: red;'>✗ Erreur : " . $description . " - " . $conn->error . "</div>";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "<div style='color: orange;'>⚠ Note : " . $description . " - La table existe déjà</div>";
        } else {
            echo "<div style='color: red;'>✗ Erreur : " . $description . " - " . $e->getMessage() . "</div>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration de la base de données - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        div {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Configuration de la base de données</h1>
        
        <?php
        // Création de la table users
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            role ENUM('etudiant', 'professeur') NOT NULL DEFAULT 'etudiant',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        executeSQLUpdate($conn, $sql, "Création de la table users");

        // Création de la table quizzes
        $sql = "CREATE TABLE IF NOT EXISTS quizzes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            professor_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (professor_id) REFERENCES users(id)
        )";
        executeSQLUpdate($conn, $sql, "Création de la table quizzes");

        // Création de la table questions
        $sql = "CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            question TEXT NOT NULL,
            option_a VARCHAR(255) NOT NULL,
            option_b VARCHAR(255) NOT NULL,
            option_c VARCHAR(255) NOT NULL,
            option_d VARCHAR(255) NOT NULL,
            correct_answer CHAR(1) NOT NULL,
            scenario TEXT,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
        )";
        executeSQLUpdate($conn, $sql, "Création de la table questions");

        // Création de la table results
        $sql = "CREATE TABLE IF NOT EXISTS results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            quiz_id INT NOT NULL,
            score INT NOT NULL,
            total_questions INT NOT NULL,
            answers_detail JSON,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        )";
        executeSQLUpdate($conn, $sql, "Création de la table results");

        // Création de la table quiz_retake_permissions
        $sql = "CREATE TABLE IF NOT EXISTS quiz_retake_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            quiz_id INT NOT NULL,
            granted_by INT NOT NULL,
            granted_at DATETIME NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id),
            FOREIGN KEY (granted_by) REFERENCES users(id)
        )";
        executeSQLUpdate($conn, $sql, "Création de la table quiz_retake_permissions");

        // Création d'un compte professeur par défaut si aucun n'existe
        $sql = "INSERT IGNORE INTO users (username, password, email, role) 
                SELECT 'admin', '$2y$10\$8MgZx/FxfuuNRuGHqxqfL.XL1h.SAh3.9gXvHDRdZnpwWH5dGxXYO', 'admin@example.com', 'professeur'
                WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'professeur' LIMIT 1)";
        executeSQLUpdate($conn, $sql, "Création du compte professeur par défaut (admin/admin)");
        ?>

        <a href="index.php" class="back-link">Retour à l'accueil</a>
    </div>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>
