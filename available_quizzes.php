<?php
session_start();
require_once 'includes/db.php';
include 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de l'utilisateur
$user_id = $_SESSION['user_id'];

// Récupérer tous les quiz disponibles avec les statistiques de l'utilisateur et les permissions de reprise
$stmt = $conn->prepare("
    SELECT 
        q.*,
        u.username as professor_name,
        COUNT(DISTINCT qu.id) as question_count,
        COUNT(DISTINCT r.id) as attempts,
        COALESCE(MAX(CAST((r.score * 100 / r.total_questions) as DECIMAL(5,2))), 0) as best_score,
        qrp.id as retake_permission_id,
        qrp.used as permission_used
    FROM quizzes q
    JOIN users u ON q.professor_id = u.id
    LEFT JOIN questions qu ON q.id = qu.quiz_id
    LEFT JOIN results r ON r.user_id = ? AND r.quiz_id = q.id
    LEFT JOIN quiz_retake_permissions qrp ON qrp.quiz_id = q.id 
        AND qrp.user_id = ? 
        AND qrp.used = 0
    GROUP BY q.id
    ORDER BY q.created_at DESC
");

$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quiz Disponibles - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .quiz-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .quiz-card:hover {
            transform: translateY(-5px);
        }
        .quiz-title {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #333;
        }
        .quiz-info {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        .quiz-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .quiz-stat {
            text-align: center;
        }
        .stat-value {
            font-size: 1.2em;
            font-weight: bold;
            color: #4CAF50;
        }
        .stat-label {
            font-size: 0.8em;
            color: #666;
        }
        .start-quiz {
            display: block;
            width: 100%;
            padding: 10px;
            text-align: center;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
            text-decoration: none;
            transition: background 0.3s ease;
            cursor: pointer;
        }
        .start-quiz.enabled {
            background: #4CAF50;
            color: white;
        }
        .start-quiz.enabled:hover {
            background: #45a049;
        }
        .start-quiz.disabled {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
            pointer-events: none;
        }
        .no-quizzes {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        .quiz-category {
            display: inline-block;
            padding: 3px 8px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 12px;
            font-size: 0.8em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1 class="page-title">Quiz Disponibles</h1>
        
        <div class="quiz-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($quiz = $result->fetch_assoc()): ?>
                    <div class="quiz-card">
                        <h2 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h2>
                        <span class="quiz-category"><?php echo htmlspecialchars($quiz['category']); ?></span>
                        <div class="quiz-info">
                            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <p>Par: <?php echo htmlspecialchars($quiz['professor_name']); ?></p>
                        </div>
                        <div class="quiz-stats">
                            <div class="quiz-stat">
                                <div class="stat-value"><?php echo $quiz['question_count']; ?></div>
                                <div class="stat-label">Questions</div>
                            </div>
                            <div class="quiz-stat">
                                <div class="stat-value"><?php echo $quiz['attempts']; ?></div>
                                <div class="stat-label">Tentatives</div>
                            </div>
                            <?php if ($quiz['attempts'] > 0): ?>
                            <div class="quiz-stat">
                                <div class="stat-value"><?php echo number_format($quiz['best_score'], 0); ?>%</div>
                                <div class="stat-label">Meilleur Score</div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $buttonClass = 'start-quiz ';
                        $buttonText = 'Commencer';
                        $isDisabled = false;

                        if ($quiz['attempts'] > 0) {
                            $buttonText = 'Recommencer';
                            if (!$quiz['retake_permission_id']) {
                                $buttonClass .= 'disabled';
                                $isDisabled = true;
                            }
                        }
                        
                        if (!$isDisabled) {
                            $buttonClass .= 'enabled';
                        }
                        ?>
                        
                        <?php if ($isDisabled): ?>
                            <button class="<?php echo $buttonClass; ?>" disabled><?php echo $buttonText; ?></button>
                        <?php else: ?>
                            <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="<?php echo $buttonClass; ?>">
                                <?php echo $buttonText; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-quizzes">
                    <h2>Aucun quiz disponible pour le moment</h2>
                    <p>Revenez plus tard pour voir les nouveaux quiz.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
