<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eleve') {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les statistiques de l'élève
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT r.quiz_id) as completed_quizzes,
        AVG(r.score) as average_score
    FROM quizzes q
    LEFT JOIN results r ON q.id = r.quiz_id AND r.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Récupérer l'historique des quiz
$stmt = $conn->prepare("
    SELECT 
        q.title,
        r.score,
        r.completion_date,
        r.total_questions,
        r.correct_answers
    FROM results r
    JOIN quizzes q ON r.quiz_id = q.id
    WHERE r.user_id = ?
    ORDER BY r.completion_date DESC
    LIMIT 10
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$history = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Alenia Quiz</title>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            color: #1a237e;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        .history-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .history-table th,
        .history-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .history-table th {
            background: #1a237e;
            color: white;
        }
        .score-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .score-high {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .score-medium {
            background: #fff3e0;
            color: #f57c00;
        }
        .score-low {
            background: #ffebee;
            color: #c62828;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #1a237e;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        .section-title {
            color: #1a237e;
            margin: 40px 0 20px;
            font-size: 1.5em;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .action-btn {
            display: inline-block;
            padding: 8px 15px;
            background: #1a237e;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            transition: background 0.3s ease;
        }
        .action-btn:hover {
            background: #0d47a1;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <h1 class="section-title">Mon Tableau de Bord</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_quizzes']; ?> / <?php echo $stats['total_quizzes']; ?></div>
                <div class="stat-label">Quiz Complétés</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo ($stats['total_quizzes'] > 0 ? ($stats['completed_quizzes'] / $stats['total_quizzes'] * 100) : 0); ?>%"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['average_score'], 1); ?>%</div>
                <div class="stat-label">Score Moyen</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_quizzes'] - $stats['completed_quizzes']; ?></div>
                <div class="stat-label">Quiz Disponibles</div>
                <a href="available_quizzes.php" class="action-btn">Voir les quiz</a>
            </div>
        </div>

        <h2 class="section-title">Historique des Quiz</h2>
        <?php if ($history->num_rows > 0): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Quiz</th>
                        <th>Score</th>
                        <th>Questions</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <?php
                                $score_percentage = ($row['correct_answers'] / $row['total_questions']) * 100;
                                $score_class = $score_percentage >= 80 ? 'score-high' : ($score_percentage >= 50 ? 'score-medium' : 'score-low');
                                ?>
                                <span class="score-badge <?php echo $score_class; ?>">
                                    <?php echo $row['correct_answers']; ?>/<?php echo $row['total_questions']; ?>
                                    (<?php echo number_format($score_percentage, 1); ?>%)
                                </span>
                            </td>
                            <td><?php echo $row['total_questions']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['completion_date'])); ?></td>
                            <td>
                                <a href="quiz_result.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="action-btn">
                                    Voir détails
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <p>Vous n'avez pas encore passé de quiz.</p>
                <a href="available_quizzes.php" class="action-btn">Commencer un quiz</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
