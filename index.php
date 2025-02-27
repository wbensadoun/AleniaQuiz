<?php
session_start();
include 'includes/header.php';

// Rediriger vers le tableau de bord approprié si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'professor') {
        header('Location: professor_dashboard.php');
        exit();
    } else {
        header('Location: available_quizzes.php');
        exit();
    }
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer quelques statistiques pour la page d'accueil
$stats = [
    'total_quizzes' => 0,
    'total_questions' => 0,
    'total_users' => 0,
    'total_attempts' => 0
];

// Nombre total de quiz
$result = $conn->query("SELECT COUNT(*) as count FROM quizzes");
if ($result) {
    $stats['total_quizzes'] = $result->fetch_assoc()['count'];
}

// Nombre total de questions
$result = $conn->query("SELECT COUNT(*) as count FROM questions");
if ($result) {
    $stats['total_questions'] = $result->fetch_assoc()['count'];
}

// Nombre total d'utilisateurs
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
if ($result) {
    $stats['total_users'] = $result->fetch_assoc()['count'];
}

// Nombre total de tentatives
$result = $conn->query("SELECT COUNT(*) as count FROM results");
if ($result) {
    $stats['total_attempts'] = $result->fetch_assoc()['count'];
}

// Récupérer les quiz déjà faits par l'utilisateur
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT quiz_id FROM results WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $completed_quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $completed_quiz_ids = array_column($completed_quizzes, 'quiz_id');
} else {
    $completed_quiz_ids = [];
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alenia Quiz - Plateforme d'apprentissage interactive</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .custom-header {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 20px;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 3.5em;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .hero-subtitle {
            font-size: 1.5em;
            margin-bottom: 40px;
            line-height: 1.6;
            font-weight: 300;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .cta-button {
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 1.1em;
            text-decoration: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .cta-primary {
            background-color: #4CAF50;
            color: white;
            border: none;
        }

        .cta-primary:hover {
            background-color: #45a049;
        }

        .cta-secondary {
            background-color: white;
            color: #764ba2;
            border: none;
        }

        .cta-secondary:hover {
            background-color: #f8f9fa;
        }

        .features {
            padding: 80px 20px;
            background: white;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #764ba2;
        }

        .feature-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .feature-description {
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5em;
            }

            .hero-subtitle {
                font-size: 1.2em;
            }

            .cta-buttons {
                flex-direction: column;
                gap: 15px;
            }

            .cta-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="custom-header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-graduation-cap"></i>
                Alenia Quiz
            </a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-book-reader"></i>
                    Bienvenue sur Alenia Quiz
                </h1>
                <p class="hero-subtitle">La plateforme interactive d'apprentissage qui rend l'éducation plus engageante et efficace.</p>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="login.php" class="cta-button cta-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Se connecter
                    </a>
                    <a href="register.php" class="cta-button cta-secondary">
                        <i class="fas fa-user-plus"></i>
                        S'inscrire
                    </a>
                </div>
                <?php else: ?>
                <div class="cta-buttons">
                    <a href="dashboard.php" class="cta-button cta-primary">
                        <i class="fas fa-tachometer-alt"></i>
                        Accéder à mon tableau de bord
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="features">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="feature-title">
                        <i class="fas fa-lightbulb"></i>
                        Apprentissage Interactif
                    </h3>
                    <p class="feature-description">Des quiz engageants et interactifs pour une meilleure rétention des connaissances.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">
                        <i class="fas fa-chart-bar"></i>
                        Suivi des Progrès
                    </h3>
                    <p class="feature-description">Visualisez vos performances et suivez votre progression en temps réel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="feature-title">
                        <i class="fas fa-tasks"></i>
                        Objectifs Personnalisés
                    </h3>
                    <p class="feature-description">Des parcours d'apprentissage adaptés à vos besoins et objectifs.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
<?php $conn->close(); ?>