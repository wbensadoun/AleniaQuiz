<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<header id="main-header">
    <div class="container">
        <a href="index.php" class="site-title">Alenia Quiz</a>
        <nav class="nav-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'professeur'): ?>
                    <a href="professor_dashboard.php" class="nav-link">Tableau de bord</a>
                    <a href="create_quiz.php" class="nav-link">Créer un Quiz</a>
                    <a href="manage_quizzes.php" class="nav-link">Gérer les Quiz</a>
                <?php else: ?>
                    <a href="available_quizzes.php" class="nav-link">Quizzes disponibles</a>
                    <a href="my_results.php" class="nav-link">Mes résultats</a>
                <?php endif; ?>
                
                <div class="user-info">
                    <span class="username">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline">Déconnexion</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Se connecter</a>
                <a href="register.php" class="btn btn-outline">S'inscrire</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<style>
header {
    background-color: #f5f5f5; /* Couleur de fond du header */
    padding: 20px 0; /* Ajoutez du padding en haut et en bas du header */
}

.container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px; /* Padding à l'intérieur du conteneur */
    max-width: 12000px; /* Limiter la largeur du conteneur */
    margin: 0 auto; /* Centrer le conteneur */
}

.site-title {
    font-size: 24px; /* Taille de la police */
    color: #333; /* Couleur du texte */
    text-decoration: none;
}

.nav-menu {
    display: flex;
    align-items: center;
    margin-left: auto; /* Pousse le contenu à droite */
}

.nav-link {
    margin-left: 20px; /* Espacement entre les liens */
    text-decoration: none;
    color: #333;
}

.nav-link:hover {
    color: #666;
}

.user-info {
    display: flex;
    align-items: center;
    margin-left: 20px; /* Espacement entre les liens */
}

.username {
    font-weight: bold;
    margin-right: 10px;
}

.btn {
    margin-left: 10px; /* Espacement entre les boutons */
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-primary {
    background-color: #4CAF50;
    color: #fff;
}

.btn-primary:hover {
    background-color: #3e8e41;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #4CAF50;
    color: #4CAF50;
}

.btn-outline:hover {
    background-color: #4CAF50;
    color: #fff;
}
</style>