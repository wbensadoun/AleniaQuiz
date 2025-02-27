<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer les informations du quiz
$stmt = $conn->prepare("
    SELECT q.*, u.username as professor_name, COUNT(qu.id) as question_count
    FROM quizzes q
    JOIN users u ON q.professor_id = u.id
    LEFT JOIN questions qu ON q.id = qu.quiz_id
    WHERE q.id = ?
    GROUP BY q.id
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header('Location: professor_dashboard.php');
    exit();
}

// Récupérer toutes les questions du quiz
$stmt = $conn->prepare("
    SELECT *
    FROM questions
    WHERE quiz_id = ?
    ORDER BY id
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir le Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .quiz-header {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .quiz-title {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 10px;
        }
        .quiz-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        .question-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .question-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        .question-item:last-child {
            border-bottom: none;
        }
        .question-text {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 15px;
        }
        .options-list {
            list-style: none;
            padding: 0;
        }
        .option-item {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .correct-answer {
            background: #d4edda;
            color: #155724;
        }
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .action-button {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
        }
        .edit-button {
            background: #28a745;
        }
        .back-button {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="quiz-container">
        <div class="quiz-header">
            <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <div class="quiz-meta">
                <p>Catégorie : <?php echo htmlspecialchars($quiz['category']); ?></p>
                <p>Description : <?php echo htmlspecialchars($quiz['description']); ?></p>
                <p>Créé par : <?php echo htmlspecialchars($quiz['professor_name']); ?></p>
                <p>Date de création : <?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></p>
                <p>Nombre de questions : <?php echo $quiz['question_count']; ?></p>
            </div>
            <div class="action-buttons">
                <a href="edit_quiz.php?id=<?php echo $quiz_id; ?>" class="action-button edit-button">Modifier</a>
                <a href="professor_dashboard.php" class="action-button back-button">Retour</a>
            </div>
        </div>

        <div class="question-list">
            <h2>Questions</h2>
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-item">
                    <div class="question-text">
                        Question <?php echo $index + 1; ?> : <?php echo htmlspecialchars($question['question']); ?>
                    </div>
                    <?php if ($question['scenario']): ?>
                        <div class="scenario">
                            <strong>Scénario :</strong>
                            <p><?php echo htmlspecialchars($question['scenario']); ?></p>
                        </div>
                    <?php endif; ?>
                    <ul class="options-list">
                        <?php
                        $options = ['a', 'b', 'c', 'd'];
                        foreach ($options as $option):
                            $isCorrect = $question['correct_answer'] === strtoupper($option);
                        ?>
                            <li class="option-item <?php echo $isCorrect ? 'correct-answer' : ''; ?>">
                                <?php echo strtoupper($option) . '. ' . htmlspecialchars($question['option_' . $option]); ?>
                                <?php if ($isCorrect) echo ' ✓'; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
