<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}

require_once '../db_connect.php';

// Récupérer tous les quiz avec leurs statistiques
$sql = "SELECT q.*, 
        COUNT(DISTINCT r.user_id) as total_participants,
        AVG(r.score) as average_score,
        MAX(r.score) as highest_score
        FROM quizzes q
        LEFT JOIN results r ON q.id = r.quiz_id
        GROUP BY q.id
        ORDER BY q.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Quiz - Alenia</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .quiz-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .quiz-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .quiz-card h3 {
            margin-top: 0;
            color: #333;
        }
        .stats {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .stat-item {
            margin: 5px 0;
            color: #666;
        }
        .actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .no-quiz {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Liste des Quiz</h1>
            <a href="upload.php" class="btn">Créer un nouveau quiz</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="quiz-list">
                <?php while ($quiz = $result->fetch_assoc()): ?>
                    <div class="quiz-card">
                        <h3><?php echo htmlspecialchars($quiz['file_name']); ?></h3>
                        <div class="stats">
                            <div class="stat-item">
                                <strong>Questions:</strong> <?php echo $quiz['total_questions']; ?>
                            </div>
                            <div class="stat-item">
                                <strong>Chemin:</strong> <?php echo htmlspecialchars($quiz['file_path']); ?>
                            </div>
                            <div class="stat-item">
                                <strong>Créé le:</strong> <?php echo date('d/m/Y H:i', strtotime($quiz['created_at'])); ?>
                            </div>
                            <div class="stat-item">
                                <strong>Participants:</strong> <?php echo $quiz['total_participants']; ?>
                            </div>
                            <?php if ($quiz['total_participants'] > 0): ?>
                                <div class="stat-item">
                                    <strong>Score moyen:</strong> 
                                    <?php echo number_format($quiz['average_score'], 1); ?>/<?php echo $quiz['total_questions']; ?>
                                </div>
                                <div class="stat-item">
                                    <strong>Meilleur score:</strong> 
                                    <?php echo $quiz['highest_score']; ?>/<?php echo $quiz['total_questions']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="actions">
                            <a href="view_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn">Voir les détails</a>
                            <a href="../quiz.php?path=<?php echo urlencode($quiz['file_path']); ?>" class="btn">Passer le quiz</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-quiz">
                <h2>Aucun quiz n'a encore été créé</h2>
                <p>Commencez par créer votre premier quiz en cliquant sur le bouton "Créer un nouveau quiz"</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
