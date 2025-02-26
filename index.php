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

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #6e8efb, #4a6ee0);
            color: white;
            margin-bottom: 40px;
        }
        .hero h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 1.2em;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .cta-button {
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .cta-primary {
            background: #4CAF50;
            color: white;
        }
        .cta-secondary {
            background: white;
            color: #4a6ee0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #4a6ee0;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 1.1em;
        }
        .features {
            padding: 40px 20px;
            background: #f5f5f5;
        }
        .features h2 {
            text-align: center;
            margin-bottom: 40px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .feature-card h3 {
            color: #4a6ee0;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <main>
        <section class="hero">
            <h1>Bienvenue sur Alenia Quiz</h1>
            <p>La plateforme interactive d'apprentissage qui rend l'éducation plus engageante et efficace.</p>
            <div class="cta-buttons">
                <a href="login.php" class="cta-button cta-primary">Se connecter</a>
                <a href="register.php" class="cta-button cta-secondary">S'inscrire</a>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
                <div class="stat-label">Quiz Créés</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_questions']; ?></div>
                <div class="stat-label">Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Étudiants Actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_attempts']; ?></div>
                <div class="stat-label">Quiz Complétés</div>
            </div>
        </section>

        <section class="features">
            <h2>Pourquoi choisir Alenia Quiz ?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <h3>Quiz Interactifs</h3>
                    <p>Participez à des quiz interactifs avec des questions variées et des scénarios réels.</p>
                </div>
                <div class="feature-card">
                    <h3>Suivi des Progrès</h3>
                    <p>Suivez vos progrès et visualisez votre évolution au fil du temps.</p>
                </div>
                <div class="feature-card">
                    <h3>Interface Intuitive</h3>
                    <p>Une interface moderne et facile à utiliser pour une expérience d'apprentissage optimale.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
<?php $conn->close(); ?>