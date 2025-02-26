<?php
session_start();
include 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'ID du résultat est fourni
if (!isset($_GET['id'])) {
    header('Location: available_quizzes.php');
    exit();
}

$result_id = (int)$_GET['id'];

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les informations du résultat et du quiz
$stmt = $conn->prepare("
    SELECT 
        r.*,
        q.title as quiz_title,
        q.description as quiz_description,
        u.username as student_name
    FROM results r
    JOIN quizzes q ON r.quiz_id = q.id
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ? AND r.user_id = ?
");

$stmt->bind_param("ii", $result_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    header('Location: available_quizzes.php');
    exit();
}

// Calculer le pourcentage
$percentage = ($result['score'] / $result['total_questions']) * 100;

// Déterminer le message et la classe CSS en fonction du score
if ($percentage >= 80) {
    $message = "Excellent travail !";
    $class = "excellent";
} elseif ($percentage >= 60) {
    $message = "Bon travail !";
    $class = "good";
} elseif ($percentage >= 40) {
    $message = "Continuez vos efforts !";
    $class = "average";
} else {
    $message = "N'abandonnez pas, réessayez !";
    $class = "needs-improvement";
}

// Récupérer l'historique des tentatives pour ce quiz
$stmt = $conn->prepare("
    SELECT 
        score,
        total_questions,
        completed_at
    FROM results
    WHERE user_id = ? AND quiz_id = ?
    ORDER BY completed_at DESC
");

$stmt->bind_param("ii", $_SESSION['user_id'], $result['quiz_id']);
$stmt->execute();
$attempts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat du Quiz - <?php echo htmlspecialchars($result['quiz_title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .result-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .score-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin: 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        .excellent {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }
        .good {
            background: linear-gradient(135deg, #2196F3, #1976D2);
        }
        .average {
            background: linear-gradient(135deg, #FF9800, #F57C00);
        }
        .needs-improvement {
            background: linear-gradient(135deg, #f44336, #d32f2f);
        }
        .result-message {
            text-align: center;
            font-size: 1.5em;
            margin: 20px 0;
            color: #333;
        }
        .result-details {
            margin: 20px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .result-details p {
            margin: 10px 0;
            color: #666;
        }
        .attempts-history {
            margin-top: 30px;
        }
        .attempt-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .attempt-item:last-child {
            border-bottom: none;
        }
        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background: #2196F3;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="result-container">
            <h1 class="text-center"><?php echo htmlspecialchars($result['quiz_title']); ?></h1>
            
            <div class="score-circle <?php echo $class; ?>">
                <?php echo number_format($percentage, 0); ?>%
            </div>
            
            <div class="result-message">
                <?php echo $message; ?>
            </div>
            
            <div class="result-details">
                <p><strong>Score:</strong> <?php echo $result['score']; ?> sur <?php echo $result['total_questions']; ?> questions</p>
                <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($result['completed_at'])); ?></p>
            </div>

            <?php if ($attempts->num_rows > 1): ?>
            <div class="attempts-history">
                <h2>Historique des tentatives</h2>
                <?php while ($attempt = $attempts->fetch_assoc()): ?>
                    <div class="attempt-item">
                        <span>Score: <?php echo number_format(($attempt['score'] / $attempt['total_questions']) * 100, 1); ?>%</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($attempt['completed_at'])); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

            <div class="buttons">
                <a href="quiz.php?id=<?php echo $result['quiz_id']; ?>" class="btn btn-primary">Réessayer le Quiz</a>
                <a href="available_quizzes.php" class="btn btn-secondary">Retour aux Quiz</a>
            </div>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
