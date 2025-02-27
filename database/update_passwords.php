<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer tous les utilisateurs
$sql = "SELECT id, password FROM users";
$result = $conn->query($sql);

// Mettre à jour chaque mot de passe avec un hash
while ($user = $result->fetch_assoc()) {
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user['id']);
    $stmt->execute();
}

echo "Tous les mots de passe ont été hashés avec succès.";
$conn->close();
?>
