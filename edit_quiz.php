<?php
// Afficher les erreurs en développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'includes/header.php';

// Vérifier si l'utilisateur est connecté et est un professeur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

// Vérifier si l'ID du quiz est fourni
if (!isset($_GET['id'])) {
    header('Location: manage_quizzes.php');
    exit();
}

$quiz_id = (int)$_GET['id'];

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        // Mise à jour des informations du quiz
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        
        $stmt = $conn->prepare("UPDATE quizzes SET title = ?, description = ?, category = ? WHERE id = ? AND professor_id = ?");
        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête quiz : " . $conn->error);
        }
        $stmt->bind_param("sssii", $title, $description, $category, $quiz_id, $_SESSION['user_id']);
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de la mise à jour du quiz : " . $stmt->error);
        }

        // Récupérer les IDs existants des questions
        $stmt = $conn->prepare("SELECT id FROM questions WHERE quiz_id = ?");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_questions = [];
        while ($row = $result->fetch_assoc()) {
            $existing_questions[] = $row['id'];
        }

        // Traiter chaque question soumise
        foreach ($_POST['questions'] as $q_id => $question) {
            if (strpos($q_id, 'new_') === 0) {
                // Nouvelle question
                $stmt = $conn->prepare("
                    INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, 
                                        correct_answer, scenario, timer)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if (!$stmt) {
                    throw new Exception("Erreur de préparation de l'insertion : " . $conn->error);
                }
                $stmt->bind_param("isssssssi",
                    $quiz_id,
                    $question['question'],
                    $question['option_a'],
                    $question['option_b'],
                    $question['option_c'],
                    $question['option_d'],
                    $question['correct_answer'],
                    $question['scenario'],
                    $question['timer']
                );
            } else {
                // Question existante
                $q_id = (int)$q_id;
                $stmt = $conn->prepare("
                    UPDATE questions 
                    SET question = ?, 
                        option_a = ?, 
                        option_b = ?, 
                        option_c = ?, 
                        option_d = ?, 
                        correct_answer = ?,
                        scenario = ?,
                        timer = ?
                    WHERE id = ? AND quiz_id = ?
                ");
                if (!$stmt) {
                    throw new Exception("Erreur de préparation de la mise à jour : " . $conn->error);
                }
                $stmt->bind_param(
                    "ssssssssii",
                    $question['question'],
                    $question['option_a'],
                    $question['option_b'],
                    $question['option_c'],
                    $question['option_d'],
                    $question['correct_answer'],
                    $question['scenario'],
                    $question['timer'],
                    $q_id,
                    $quiz_id
                );
                
                // Retirer cet ID de la liste des questions existantes
                $key = array_search($q_id, $existing_questions);
                if ($key !== false) {
                    unset($existing_questions[$key]);
                }
            }
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la mise à jour/insertion de la question : " . $stmt->error);
            }
        }

        // Supprimer les questions qui n'existent plus
        if (!empty($existing_questions)) {
            $ids_to_delete = implode(',', array_map('intval', $existing_questions));
            $stmt = $conn->prepare("DELETE FROM questions WHERE id IN ($ids_to_delete) AND quiz_id = ?");
            $stmt->bind_param("i", $quiz_id);
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la suppression des questions : " . $stmt->error);
            }
        }

        $conn->commit();
        header('Location: manage_quizzes.php?success=1');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Une erreur est survenue : " . $e->getMessage());
    }
}

// Récupérer les informations du quiz
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND professor_id = ?");
$stmt->bind_param("ii", $quiz_id, $_SESSION['user_id']);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header('Location: manage_quizzes.php');
    exit();
}

// Récupérer toutes les questions du quiz
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-form {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .quiz-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .questions-container {
            display: grid;
            gap: 20px;
        }
        .question-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        .delete-question {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #dc3545;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.2em;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select,
        .form-group input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        .btn-container {
            text-align: center;
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .correct-answer {
            background-color: #e8f5e9;
        }
        .add-question {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 0;
            width: 100%;
            font-size: 1em;
        }
        .add-question:hover {
            background: #45a049;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <main class="edit-form">
        <h1>Modifier le Quiz : <?php echo htmlspecialchars($quiz['title']); ?></h1>

        <form method="POST" action="" id="quizForm">
            <div class="quiz-info">
                <h2>Informations générales</h2>
                <div class="form-group">
                    <label for="title">Titre du Quiz</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($quiz['category']); ?>" required>
                </div>
            </div>

            <div class="questions-container" id="questionsContainer">
                <h2>Questions</h2>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card">
                        <div class="question-header">
                            <h3>Question <?php echo $index + 1; ?></h3>
                            <?php if (count($questions) > 1): ?>
                                <button type="button" class="delete-question" onclick="deleteQuestion(this)">&times;</button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Question</label>
                            <input type="text" name="questions[<?php echo $question['id']; ?>][question]" 
                                   value="<?php echo htmlspecialchars($question['question']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Scénario (optionnel)</label>
                            <textarea name="questions[<?php echo $question['id']; ?>][scenario]" rows="2"><?php echo htmlspecialchars($question['scenario']); ?></textarea>
                        </div>

                        <div class="options-grid">
                            <div class="form-group">
                                <label>Option A</label>
                                <input type="text" name="questions[<?php echo $question['id']; ?>][option_a]" 
                                       value="<?php echo htmlspecialchars($question['option_a']); ?>" required
                                       class="<?php echo $question['correct_answer'] === 'A' ? 'correct-answer' : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Option B</label>
                                <input type="text" name="questions[<?php echo $question['id']; ?>][option_b]" 
                                       value="<?php echo htmlspecialchars($question['option_b']); ?>" required
                                       class="<?php echo $question['correct_answer'] === 'B' ? 'correct-answer' : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Option C</label>
                                <input type="text" name="questions[<?php echo $question['id']; ?>][option_c]" 
                                       value="<?php echo htmlspecialchars($question['option_c']); ?>" required
                                       class="<?php echo $question['correct_answer'] === 'C' ? 'correct-answer' : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Option D</label>
                                <input type="text" name="questions[<?php echo $question['id']; ?>][option_d]" 
                                       value="<?php echo htmlspecialchars($question['option_d']); ?>" required
                                       class="<?php echo $question['correct_answer'] === 'D' ? 'correct-answer' : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Réponse correcte</label>
                            <select name="questions[<?php echo $question['id']; ?>][correct_answer]" required>
                                <option value="A" <?php echo $question['correct_answer'] === 'A' ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo $question['correct_answer'] === 'B' ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo $question['correct_answer'] === 'C' ? 'selected' : ''; ?>>C</option>
                                <option value="D" <?php echo $question['correct_answer'] === 'D' ? 'selected' : ''; ?>>D</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Temps limite (en secondes)</label>
                            <input type="number" name="questions[<?php echo $question['id']; ?>][timer]" 
                                   value="<?php echo htmlspecialchars($question['timer']); ?>" required min="0">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="add-question" onclick="addQuestion()">+ Ajouter une nouvelle question</button>

            <div class="btn-container">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="manage_quizzes.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </main>

    <script>
    function addQuestion() {
        const container = document.getElementById('questionsContainer');
        const questionCount = container.getElementsByClassName('question-card').length;
        const newQuestionId = 'new_' + Date.now(); // Identifiant temporaire unique

        const questionHtml = `
            <div class="question-card">
                <div class="question-header">
                    <h3>Question ${questionCount + 1}</h3>
                    <button type="button" class="delete-question" onclick="deleteQuestion(this)">&times;</button>
                </div>
                
                <div class="form-group">
                    <label>Question</label>
                    <input type="text" name="questions[${newQuestionId}][question]" required>
                </div>

                <div class="form-group">
                    <label>Scénario (optionnel)</label>
                    <textarea name="questions[${newQuestionId}][scenario]" rows="2"></textarea>
                </div>

                <div class="options-grid">
                    <div class="form-group">
                        <label>Option A</label>
                        <input type="text" name="questions[${newQuestionId}][option_a]" required>
                    </div>
                    <div class="form-group">
                        <label>Option B</label>
                        <input type="text" name="questions[${newQuestionId}][option_b]" required>
                    </div>
                    <div class="form-group">
                        <label>Option C</label>
                        <input type="text" name="questions[${newQuestionId}][option_c]" required>
                    </div>
                    <div class="form-group">
                        <label>Option D</label>
                        <input type="text" name="questions[${newQuestionId}][option_d]" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Réponse correcte</label>
                    <select name="questions[${newQuestionId}][correct_answer]" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Temps limite (en secondes)</label>
                    <input type="number" name="questions[${newQuestionId}][timer]" value="30" required min="0">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', questionHtml);
    }

    function deleteQuestion(button) {
        const questionCard = button.closest('.question-card');
        const container = document.getElementById('questionsContainer');
        
        if (container.getElementsByClassName('question-card').length > 1) {
            questionCard.remove();
            // Mettre à jour les numéros des questions
            const questions = container.getElementsByClassName('question-card');
            Array.from(questions).forEach((q, index) => {
                q.querySelector('h3').textContent = `Question ${index + 1}`;
            });
        } else {
            alert('Le quiz doit avoir au moins une question !');
        }
    }

    // Mettre en évidence la réponse correcte lors du changement
    document.addEventListener('change', function(e) {
        if (e.target.name.includes('[correct_answer]')) {
            const questionCard = e.target.closest('.question-card');
            const options = questionCard.querySelectorAll('input[type="text"]');
            options.forEach(input => input.classList.remove('correct-answer'));
            
            const selectedOption = e.target.value;
            const correctInput = questionCard.querySelector(`input[name$="[option_${selectedOption.toLowerCase()}]"]`);
            if (correctInput) {
                correctInput.classList.add('correct-answer');
            }
        }
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>