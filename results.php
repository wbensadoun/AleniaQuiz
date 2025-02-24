<?php
session_start();
require_once 'db_connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer le dernier score
$last_score = isset($_SESSION['last_score']) ? $_SESSION['last_score'] : null;
$total_questions = isset($_SESSION['total_questions']) ? $_SESSION['total_questions'] : null;

// Récupérer l'historique des scores
$stmt = $conn->prepare("SELECT * FROM results WHERE user_id = ? ORDER BY completed_at DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Résultats du Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .results-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .score-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .score {
            font-size: 48px;
            color: #2196F3;
            margin: 20px 0;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .history-table th,
        .history-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .history-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="results-container">
        <?php if ($last_score !== null && $total_questions !== null): ?>
        <div class="score-card">
            <h1>Résultats du Quiz</h1>
            <div class="score"><?php echo $last_score; ?> / <?php echo $total_questions; ?></div>
            <p>Score: <?php echo round(($last_score / $total_questions) * 100); ?>%</p>
        </div>
        <?php endif; ?>

        <?php if (!empty($history)): ?>
        <h2>Historique des scores</h2>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Score</th>
                    <th>Total</th>
                    <th>Pourcentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $result): ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($result['completed_at'])); ?></td>
                    <td><?php echo $result['score']; ?></td>
                    <td><?php echo $result['total_questions']; ?></td>
                    <td><?php echo round(($result['score'] / $result['total_questions']) * 100); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="buttons">
            <a href="quiz.php" class="button">Refaire le quiz</a>
            <?php if ($_SESSION['role'] == 'professor'): ?>
            <a href="secure_admin/upload.php" class="button">Gérer les questions</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
