<?php
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
    <title>Mise à jour de la base de données</title>
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
        <h1>Mise à jour de la base de données</h1>
        
        <?php
        // Création de la table users
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'professeur', 'eleve') NOT NULL
        )";

        if ($conn->query($sql)) {
            echo "✅ Table users créée avec succès<br>";
        } else {
            echo "❌ Erreur lors de la création de la table users: " . $conn->error . "<br>";
        }

        // Vérification de l'existence des utilisateurs de test
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        $username = 'admin@quiz.com';
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];

        // Création des utilisateurs de test s'ils n'existent pas
        if ($count == 0) {
            // Hash des mots de passe
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $prof_password = password_hash('prof123', PASSWORD_DEFAULT);
            $eleve_password = password_hash('eleve123', PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, password, role) VALUES
                ('admin@quiz.com', ?, 'admin'),
                ('professeur@quiz.com', ?, 'professeur'),
                ('eleve1@quiz.com', ?, 'eleve'),
                ('eleve2@quiz.com', ?, 'eleve'),
                ('eleve3@quiz.com', ?, 'eleve')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $admin_password, $prof_password, $eleve_password, $eleve_password, $eleve_password);
            
            if ($stmt->execute()) {
                echo "✅ Utilisateurs de test créés avec succès<br>";
            } else {
                echo "❌ Erreur lors de la création des utilisateurs de test: " . $conn->error . "<br>";
            }
        }

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
        ?>

        <a href="index.php" class="back-link">Retour à l'accueil</a>
    </div>
</body>
</html>
