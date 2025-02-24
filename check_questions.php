<?php
require_once 'db_connect.php';

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir l'encodage
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification des Questions</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Questions dans la base de données</h1>
    <?php
    $sql = "SELECT * FROM questions";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Question</th><th>Options</th><th>Réponse Correcte</th><th>Scénario</th></tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['question']) . "</td>";
            echo "<td><pre>" . htmlspecialchars($row['options']) . "</pre></td>";
            echo "<td>" . htmlspecialchars($row['correct_letter']) . "</td>";
            echo "<td>" . htmlspecialchars($row['scenario']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Aucune question trouvée dans la base de données.</p>";
    }
    ?>
</body>
</html>
