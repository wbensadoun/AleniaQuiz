<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$quiz_id) {
    header('Location: index.php');
    exit();
}

// Vérifier si l'utilisateur a déjà fait ce quiz
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM results WHERE user_id = ? AND quiz_id = ?");
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$has_taken_quiz = $result['count'] > 0;

// Si l'utilisateur a déjà fait le quiz, vérifier s'il a une autorisation de reprise
if ($has_taken_quiz) {
    $stmt = $conn->prepare("
        SELECT id, used 
        FROM quiz_retake_permissions 
        WHERE user_id = ? AND quiz_id = ? AND used = 0
        ORDER BY granted_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $quiz_id);
    $stmt->execute();
    $permission = $stmt->get_result()->fetch_assoc();

    if (!$permission) {
        $_SESSION['error'] = "Vous avez déjà passé ce quiz. Attendez l'autorisation du professeur pour le repasser.";
        header('Location: my_results.php');
        exit();
    }

    // Marquer l'autorisation comme utilisée
    $stmt = $conn->prepare("UPDATE quiz_retake_permissions SET used = 1 WHERE id = ?");
    $stmt->bind_param("i", $permission['id']);
    $stmt->execute();
}

// Récupérer les informations du quiz
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header('Location: index.php');
    exit();
}

// Récupérer les questions du quiz
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY RAND()");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($questions)) {
    $_SESSION['error'] = "Ce quiz ne contient aucune question.";
    header('Location: index.php');
    exit();
}

// Récupérer le dernier score si disponible
$stmt = $conn->prepare("
    SELECT score, total_questions 
    FROM results 
    WHERE user_id = ? AND quiz_id = ? 
    ORDER BY completed_at DESC 
    LIMIT 1
");
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$last_score = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .quiz-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .quiz-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }

        .quiz-description {
            color: #6c757d;
            font-size: 1rem;
        }

        .last-score {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .question-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .question-text {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .options-container {
            display: grid;
            gap: 10px;
        }

        .option-label {
            display: block;
            padding: 15px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option-label:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }

        input[type="radio"] {
            display: none;
        }

        input[type="radio"]:checked + .option-label {
            background: #007bff;
            border-color: #0056b3;
            color: white;
        }

        .submit-button {
            display: block;
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-button:hover {
            background: #218838;
        }

        .scenario {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-style: italic;
            color: #495057;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="quiz-container">
        <div class="quiz-header">
            <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
            <?php if ($last_score): ?>
                <div class="last-score">
                    Dernier score : <?php echo $last_score['score']; ?>/<?php echo $last_score['total_questions']; ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="submit_quiz.php" method="post">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-container">
                    <?php if ($question['scenario']): ?>
                        <div class="scenario">
                            <?php echo htmlspecialchars($question['scenario']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="question-text">
                        <?php echo htmlspecialchars($question['question']); ?>
                    </div>

                    <div class="options-container">
                        <?php
                        $options = [
                            'a' => $question['option_a'],
                            'b' => $question['option_b'],
                            'c' => $question['option_c'],
                            'd' => $question['option_d']
                        ];
                        foreach ($options as $letter => $option): ?>
                            <div class="option">
                                <input type="radio" 
                                       id="q<?php echo $question['id'] . $letter; ?>" 
                                       name="answers[<?php echo $question['id']; ?>]" 
                                       value="<?php echo $letter; ?>" 
                                       required>
                                <label class="option-label" for="q<?php echo $question['id'] . $letter; ?>">
                                    <?php echo strtoupper($letter) . ') ' . htmlspecialchars($option); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="submit-button">Soumettre le Quiz</button>
        </form>
    </div>
</body>
</html>
