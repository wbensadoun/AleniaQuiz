<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}

// Vérifier si un ID de quiz est fourni
if (!isset($_GET['id'])) {
    header('Location: list_quizzes.php');
    exit();
}

require_once '../db_connect.php';

$quiz_id = intval($_GET['id']);

// Récupérer les informations du quiz
$sql = "SELECT q.*, 
        COUNT(DISTINCT r.user_id) as total_participants,
        AVG(r.score) as average_score,
        MAX(r.score) as highest_score
        FROM quizzes q
        LEFT JOIN results r ON q.id = r.quiz_id
        WHERE q.id = ?
        GROUP BY q.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();

if (!$quiz) {
    die("Quiz non trouvé");
}

// Décoder les questions
$questions = json_decode($quiz['questions_json'], true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Quiz #<?php echo $quiz_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            color: #666;
        }
        .question-list {
            list-style: none;
            padding: 0;
        }
        .question-item {
            background: #fff;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .question-header {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 10px;
        }
        .scenario {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-left: 3px solid #28a745;
            color: #666;
        }
        .options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .option {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .option.correct {
            background: #d4edda;
            border-color: #c3e6cb;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quiz #<?php echo $quiz_id; ?></h1>
            <div class="actions">
                <a href="list_quizzes.php" class="btn">Retour à la liste</a>
                <a href="../quiz.php?id=<?php echo $quiz_id; ?>" class="btn">Passer le quiz</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-value"><?php echo $quiz['total_questions']; ?></div>
                <div class="stat-label">Questions</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $quiz['total_participants']; ?></div>
                <div class="stat-label">Participants</div>
            </div>
            <?php if ($quiz['total_participants'] > 0): ?>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($quiz['average_score'], 1); ?></div>
                <div class="stat-label">Score Moyen</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $quiz['highest_score']; ?></div>
                <div class="stat-label">Meilleur Score</div>
            </div>
            <?php endif; ?>
            <div class="stat-item">
                <div class="stat-value"><?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></div>
                <div class="stat-label">Date de création</div>
            </div>
        </div>

        <h2>Questions</h2>
        <div class="question-list">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-item">
                    <div class="question-header">
                        Question <?php echo $index + 1; ?> : <?php echo htmlspecialchars($question['question']); ?>
                    </div>
                    
                    <div class="scenario">
                        Scénario : <?php echo htmlspecialchars($question['scenario']); ?>
                    </div>
                    
                    <div class="options">
                        <?php foreach ($question['options'] as $letter => $text): ?>
                            <div class="option <?php echo $letter === $question['correct_letter'] ? 'correct' : ''; ?>">
                                <?php echo $letter; ?> : <?php echo htmlspecialchars($text); ?>
                                <?php if ($letter === $question['correct_letter']): ?>
                                    ✓
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
