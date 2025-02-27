<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-header {
            background-color: #2c3e50;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 1.5em;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo:hover {
            color: #ecf0f1;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .user-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
            min-width: 200px;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 8px 0;
        }

        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-graduation-cap"></i>
                Alenia Quiz
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <nav class="nav-menu">
                    <?php if ($_SESSION['role'] === 'professeur'): ?>
                        <a href="professor_dashboard.php" class="nav-link">
                            <i class="fas fa-chalkboard-teacher"></i>
                            Tableau de bord
                        </a>
                        <a href="create_quiz.php" class="nav-link">
                            <i class="fas fa-plus-circle"></i>
                            Créer un Quiz
                        </a>
                    <?php else: ?>
                        <a href="available_quizzes.php" class="nav-link">
                            <i class="fas fa-list"></i>
                            Quiz disponibles
                        </a>
                        <a href="my_results.php" class="nav-link">
                            <i class="fas fa-chart-bar"></i>
                            Mes résultats
                        </a>
                    <?php endif; ?>
                    
                    <div class="user-menu">
                        <button class="user-button" onclick="toggleDropdown()">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                Mon profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <script>
        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // Fermer le menu déroulant si l'utilisateur clique en dehors
        window.onclick = function(event) {
            if (!event.target.matches('.user-button') && !event.target.matches('.user-button *')) {
                var dropdowns = document.getElementsByClassName('dropdown-menu');
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>