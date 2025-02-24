<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Créer la connexion
    $conn = new mysqli($servername, $username, $password);
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Lire le fichier SQL
    $sql = file_get_contents('database.sql');

    // Exécuter les commandes SQL
    if ($conn->multi_query($sql)) {
        do {
            // Stocker le premier résultat
            if ($result = $conn->store_result()) {
                $result->free();
            }
            // Passer au prochain résultat
        } while ($conn->more_results() && $conn->next_result());
    }

    echo "Base de données créée avec succès !";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
