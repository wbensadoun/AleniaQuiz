<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'ID du quiz est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$quiz_id = (int)$_GET['id'];

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
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Convertir les questions en JSON pour JavaScript
$questionsJson = json_encode($questions);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .scenario {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-style: italic;
            color: #495057;
        }

        .question-text {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 30px 0;
            font-weight: 500;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px auto;
            max-width: 600px;
        }

        .option-button {
            background: white;
            border: 2px solid #e9ecef;
            padding: 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .option-button:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .option-button.selected {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .question-number {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="quiz-container">
        <div id="questionContainer">
            <div id="scenario" class="scenario"></div>
            <div id="questionNumber" class="question-number"></div>
            <div id="questionText" class="question-text"></div>
            <div id="options" class="options-grid"></div>
        </div>
    </div>

    <script>
        const questions = <?php echo $questionsJson; ?>;
        let currentQuestionIndex = 0;
        let answers = {};

        function showQuestion(index) {
            const question = questions[index];
            
            document.getElementById('scenario').textContent = question.scenario || '';
            document.getElementById('questionNumber').textContent = `Question ${index + 1} sur ${questions.length}`;
            document.getElementById('questionText').textContent = question.question;
            
            const optionsContainer = document.getElementById('options');
            optionsContainer.innerHTML = '';
            
            ['A', 'B', 'C', 'D'].forEach(letter => {
                const button = document.createElement('button');
                button.className = 'option-button';
                button.onclick = () => selectOption(letter, index);
                button.textContent = question[`option_${letter.toLowerCase()}`];
                optionsContainer.appendChild(button);
            });
        }

        function selectOption(letter, questionIndex) {
            document.querySelectorAll('.option-button').forEach(button => {
                button.classList.remove('selected');
            });
            
            event.target.classList.add('selected');
            answers[questionIndex] = letter;
            
            // Passer à la question suivante après un court délai
            setTimeout(() => {
                if (questionIndex === questions.length - 1) {
                    submitQuiz();
                } else {
                    currentQuestionIndex++;
                    showQuestion(currentQuestionIndex);
                }
            }, 1000);
        }

        function submitQuiz() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'submit_quiz.php';
            
            const quizIdInput = document.createElement('input');
            quizIdInput.type = 'hidden';
            quizIdInput.name = 'quiz_id';
            quizIdInput.value = <?php echo $quiz_id; ?>;
            form.appendChild(quizIdInput);
            
            Object.keys(answers).forEach(index => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `answers[${index}]`;
                input.value = answers[index];
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }

        // Démarrer le quiz
        showQuestion(0);
    </script>
</body>
</html>
