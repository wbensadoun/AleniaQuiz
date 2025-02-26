<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérification du paramètre quiz_id
if (!isset($_GET['id'])) {
    header("Location: available_quizzes.php");
    exit();
}

$quiz_id = (int)$_GET['id'];

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les informations du quiz et l'historique des tentatives
$stmt = $conn->prepare("
    SELECT q.*, 
           COUNT(r.id) as attempt_count,
           MAX(r.score) as best_score,
           GROUP_CONCAT(r.score ORDER BY r.completed_at DESC) as recent_scores
    FROM quizzes q
    LEFT JOIN results r ON q.id = r.quiz_id AND r.user_id = ?
    WHERE q.id = ?
    GROUP BY q.id");
$stmt->bind_param("ii", $_SESSION['user_id'], $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();
$quiz = $quiz_result->fetch_assoc();

if (!$quiz) {
    header("Location: available_quizzes.php");
    exit();
}

// Récupérer l'historique détaillé des tentatives
$stmt = $conn->prepare("
    SELECT score, total_questions, completed_at
    FROM results
    WHERE user_id = ? AND quiz_id = ?
    ORDER BY completed_at DESC
    LIMIT 5");
$stmt->bind_param("ii", $_SESSION['user_id'], $quiz_id);
$stmt->execute();
$attempts = $stmt->get_result();

// Récupérer toutes les questions du quiz
$stmt = $conn->prepare("SELECT id, question, option_a, option_b, option_c, option_d, timer, scenario FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = [];
while ($row = $questions_result->fetch_assoc()) {
    $questions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Alenia Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .timer {
            font-size: 2em;
            text-align: center;
            margin: 20px 0;
            color: #333;
        }
        .timer.warning {
            color: #ff9800;
        }
        .timer.danger {
            color: #f44336;
        }
        .question-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .scenario {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-style: italic;
        }
        .options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .option {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .option:hover {
            background: #f0f0f0;
            border-color: #666;
        }
        .option.selected {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #eee;
            border-radius: 5px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress {
            height: 100%;
            background: #4CAF50;
            transition: width 0.3s ease;
        }
        .question-number {
            text-align: center;
            color: #666;
            margin-bottom: 10px;
        }
        #submitAnswer {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #submitAnswer:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .previous-attempts {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 600px;
        }
        .attempts-history {
            margin: 15px 0;
        }
        .attempt-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .attempt-item:last-child {
            border-bottom: none;
        }
        .attempt-score {
            font-weight: bold;
        }
        .attempt-date {
            color: #666;
        }
        .best-score {
            font-size: 1.2em;
            color: #4CAF50;
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <main class="container fade-in">
        <h1 class="page-title"><?php echo htmlspecialchars($quiz['title']); ?></h1>
        
        <div class="question-container" id="questionContainer" style="display: none;">
            <div class="progress-bar">
                <div class="progress" id="progressBar"></div>
            </div>
            
            <div class="timer" id="timer">--:--</div>
            
            <div class="question-number" id="questionNumber"></div>
            
            <div class="scenario" id="scenario"></div>
            
            <div class="question" id="question"></div>
            
            <div class="options" id="options">
                <div class="option" data-option="A"></div>
                <div class="option" data-option="B"></div>
                <div class="option" data-option="C"></div>
                <div class="option" data-option="D"></div>
            </div>
            
            <button id="submitAnswer" disabled>Question suivante</button>
        </div>

        <div id="startContainer" class="text-center">
            <h2>Êtes-vous prêt à commencer ?</h2>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
            <p><strong>Nombre de questions :</strong> <?php echo count($questions); ?></p>
            
            <?php if ($quiz['attempt_count'] > 0): ?>
            <div class="previous-attempts">
                <h3>Vos tentatives précédentes</h3>
                <div class="attempts-history">
                    <?php while ($attempt = $attempts->fetch_assoc()): ?>
                    <div class="attempt-item">
                        <span class="attempt-score">Score: <?php echo number_format(($attempt['score'] / $attempt['total_questions']) * 100, 1); ?>%</span>
                        <span class="attempt-date"><?php echo date('d/m/Y H:i', strtotime($attempt['completed_at'])); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php if ($quiz['best_score'] > 0): ?>
                <p class="best-score">Meilleur score : <?php echo number_format(($quiz['best_score'] / count($questions)) * 100, 1); ?>%</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <button id="startQuiz" class="btn btn-primary">
                <?php echo $quiz['attempt_count'] > 0 ? 'Réessayer le Quiz' : 'Commencer le Quiz'; ?>
            </button>
        </div>
    </main>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        let currentQuestionIndex = 0;
        let timer;
        let selectedAnswer = null;
        let answers = [];

        document.querySelectorAll('.option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                selectedAnswer = option.dataset.option;
                document.getElementById('submitAnswer').disabled = false;
            });
        });

        document.getElementById('startQuiz').addEventListener('click', () => {
            document.getElementById('startContainer').style.display = 'none';
            document.getElementById('questionContainer').style.display = 'block';
            showQuestion(0);
        });

        document.getElementById('submitAnswer').addEventListener('click', () => {
            clearInterval(timer);
            answers.push({
                questionId: questions[currentQuestionIndex].id,
                answer: selectedAnswer
            });

            currentQuestionIndex++;
            if (currentQuestionIndex < questions.length) {
                showQuestion(currentQuestionIndex);
            } else {
                submitQuiz();
            }
        });

        function showQuestion(index) {
            const question = questions[index];
            document.getElementById('questionNumber').textContent = `Question ${index + 1}/${questions.length}`;
            document.getElementById('scenario').textContent = question.scenario;
            document.getElementById('question').textContent = question.question;
            document.querySelector('.option[data-option="A"]').textContent = question.option_a;
            document.querySelector('.option[data-option="B"]').textContent = question.option_b;
            document.querySelector('.option[data-option="C"]').textContent = question.option_c;
            document.querySelector('.option[data-option="D"]').textContent = question.option_d;
            
            document.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
            document.getElementById('submitAnswer').disabled = true;
            selectedAnswer = null;

            // Mise à jour de la barre de progression
            const progress = ((index + 1) / questions.length) * 100;
            document.getElementById('progressBar').style.width = `${progress}%`;

            // Démarrer le timer
            startTimer(question.timer);
        }

        function startTimer(duration) {
            const timerDisplay = document.getElementById('timer');
            let timeLeft = duration;
            
            updateTimerDisplay();
            timer = setInterval(() => {
                timeLeft--;
                updateTimerDisplay();
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    if (!selectedAnswer) {
                        answers.push({
                            questionId: questions[currentQuestionIndex].id,
                            answer: null
                        });
                    }
                    currentQuestionIndex++;
                    if (currentQuestionIndex < questions.length) {
                        showQuestion(currentQuestionIndex);
                    } else {
                        submitQuiz();
                    }
                }
            }, 1000);

            function updateTimerDisplay() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                // Mise à jour des couleurs du timer
                timerDisplay.className = 'timer';
                if (timeLeft <= 10) {
                    timerDisplay.classList.add('danger');
                } else if (timeLeft <= 30) {
                    timerDisplay.classList.add('warning');
                }
            }
        }

        function submitQuiz() {
            const formData = new FormData();
            formData.append('quiz_id', <?php echo $quiz_id; ?>);
            formData.append('answers', JSON.stringify(answers));

            fetch('submit_quiz.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `quiz_result.php?id=${data.result_id}`;
                } else {
                    alert('Une erreur est survenue lors de la soumission du quiz.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la soumission du quiz.');
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
