<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Vérification si l'email existe déjà
    $check_sql = "SELECT * FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<div class='error-message'>Cet email est déjà utilisé</div>";
    } elseif ($password != $confirm_password) {
        echo "<div class='error-message'>Les mots de passe ne correspondent pas</div>";
    } else {
        // Insérer le nouvel utilisateur
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $password, $role);

        if ($stmt->execute()) {
            echo "<div class='success-message'>Inscription réussie ! <a href='login.php'>Se connecter</a></div>";
        } else {
            echo "<div class='error-message'>Erreur lors de l'inscription</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription - Quiz App</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="role">Rôle :</label>
                <select id="role" name="role" required>
                    <option value="student">Étudiant</option>
                    <option value="professor">Professeur</option>
                </select>
            </div>

            <button type="submit" class="button">S'inscrire</button>
        </form>

        <div class="links">
            <a href="login.php">Déjà inscrit ? Se connecter</a>
        </div>
    </div>
</body>
</html>
