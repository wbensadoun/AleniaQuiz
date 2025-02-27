<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_professor = isset($_SESSION['role']) && $_SESSION['role'] === 'professeur';

// Récupérer tous les résultats
if ($is_professor) {
    $stmt = $conn->prepare("
        SELECT r.*, q.title as quiz_title, u.username as student_name
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        JOIN users u ON r.user_id = u.id
        ORDER BY r.completed_at DESC
    ");
    $stmt->execute();
} else {
    $stmt = $conn->prepare("
        SELECT r.*, q.title as quiz_title
        FROM results r 
        JOIN quizzes q ON r.quiz_id = q.id 
        WHERE r.user_id = ? 
        ORDER BY r.completed_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}
$all_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des résultats</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .results-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .result-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .result-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .result-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 0;
        }
        .result-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .score {
            font-weight: bold;
            color: #2c3e50;
            font-size: 1.2rem;
            margin-top: 10px;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="results-container">
        <h1>Historique des résultats</h1>
        
        <?php foreach ($all_results as $result): ?>
            <div class="result-card">
                <div class="result-header">
                    <h3 class="result-title"><?php echo htmlspecialchars($result['quiz_title']); ?></h3>
                    <div class="result-meta">
                        Date : <?php echo date('d/m/Y H:i', strtotime($result['completed_at'])); ?>
                        <div class="score">
                            Score : <?php echo $result['score']; ?> sur <?php echo $result['total_questions']; ?>
                            (<?php echo round(($result['score'] / $result['total_questions']) * 100); ?>%)
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <a href="index.php" class="back-button">Retour aux Quiz</a>
    </div>
</body>
</html>
