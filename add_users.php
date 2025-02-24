<?php
require_once 'db_connect.php';

// Insérer les utilisateurs de test
$users = [
    ['professeur@quiz.com', 'prof123', 1],  // is_professor = 1
    ['eleve1@quiz.com', 'eleve123', 0],     // is_professor = 0
    ['eleve2@quiz.com', 'eleve123', 0],
    ['eleve3@quiz.com', 'eleve123', 0]
];

foreach ($users as $user) {
    // Vérifier si l'utilisateur existe déjà
    $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check->bind_param("s", $user[0]);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows == 0) {
        // L'utilisateur n'existe pas, on l'ajoute
        $stmt = $conn->prepare("INSERT INTO users (username, password, is_professor) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $user[0], $user[1], $user[2]);
        
        if ($stmt->execute()) {
            echo "Utilisateur {$user[0]} ajouté avec succès<br>";
        } else {
            echo "Erreur lors de l'ajout de {$user[0]}: " . $conn->error . "<br>";
        }
    } else {
        echo "L'utilisateur {$user[0]} existe déjà<br>";
    }
}

echo "<br>Terminé ! Vous pouvez maintenant vous connecter avec :<br>";
echo "Professeur : professeur@quiz.com / prof123<br>";
echo "Élèves : eleve1@quiz.com / eleve123 (ou eleve2@quiz.com, eleve3@quiz.com)";
?>
