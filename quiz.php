<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour lire les questions depuis le fichier CSV
function readQuestionsFromCSV() {
    $questions = [];
    $csvFile = __DIR__ . '/secure_admin/uploads/questions.csv';
    
    if (!file_exists($csvFile)) {
        die("Erreur: Le fichier questions.csv n'existe pas.");
    }
    
    $file = fopen($csvFile, 'r');
    if (!$file) {
        die("Erreur: Impossible d'ouvrir le fichier CSV.");
    }
    
    // Ignorer la première ligne (en-têtes)
    fgetcsv($file, 0, ';');
    
    while (($line = fgetcsv($file, 0, ';')) !== FALSE) {
        if (empty($line[0]) || count($line) < 8) {
            continue; // Ignorer les lignes invalides
        }
        
        $questionText = trim($line[0]);
        $options = [];
        
        // Traiter les 4 options (colonnes 1 à 4)
        for ($i = 1; $i <= 4; $i++) {
            $option = trim($line[$i]);
            if (preg_match('/([A-D]):(.+)/', $option, $matches)) {
                $options[$matches[1]] = trim($matches[2]);
            }
        }
        
        $correct_letter = trim($line[5]);
        $scenario = trim($line[6]);
        $timer = intval(trim($line[7])); // Temps alloué pour la question
        
        $questions[] = [
            'question' => $questionText,
            'options' => $options,
            'correct_letter' => $correct_letter,
            'scenario' => $scenario,
            'timer' => $timer
        ];
    }
    
    fclose($file);
    return $questions;
}

// Initialisation ou réinitialisation du quiz
if (!isset($_SESSION['quiz_id']) || isset($_POST['restart'])) {
    $_SESSION['questions'] = readQuestionsFromCSV();
    shuffle($_SESSION['questions']); // Mélanger les questions
    $_SESSION['quiz_id'] = uniqid();
    $_SESSION['current_question'] = 0;
    $_SESSION['score'] = 0;
}

// Traiter la réponse soumise
if (isset($_POST['answer'])) {
    $current = $_SESSION['current_question'];
    $answer = $_POST['answer'];
    $correct = $_SESSION['questions'][$current]['correct_letter'];
    
    if ($answer === $correct) {
        $_SESSION['score']++;
    }
    
    $_SESSION['current_question']++;
}

$current = $_SESSION['current_question'];
$total_questions = count($_SESSION['questions']);
$quiz_completed = $current >= $total_questions;
$current_question = $_SESSION['questions'][$current] ?? null;
$question_timer = $current_question['timer'] ?? 30; // Temps par défaut : 30 secondes
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Alenia</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="quiz-app">
        <div class="container">
            <?php if (!$quiz_completed && $current_question): ?>
                <div class="timer" id="timer">Temps restant : <span id="time"><?php echo $question_timer; ?></span> secondes</div>
                
                <div class="progress">
                    Question <?php echo $current + 1; ?> sur <?php echo $total_questions; ?>
                </div>
                
                <div class="scenario">
                    <?php echo htmlspecialchars($current_question['scenario']); ?>
                </div>
                
                <div class="question">
                    <?php echo htmlspecialchars($current_question['question']); ?>
                </div>
                
                <form method="post" class="options" id="quiz-form">
                    <?php foreach ($current_question['options'] as $letter => $text): ?>
                        <label class="option">
                            <input type="radio" name="answer" value="<?php echo $letter; ?>" required>
                            <?php echo htmlspecialchars($text); ?>
                        </label>
                    <?php endforeach; ?>
                    
                    <div class="buttons">
                        <button type="submit" class="btn btn-primary">Répondre</button>
                    </div>
                </form>
                
            <?php else: ?>
                <div class="result">
                    <h2>Score final : <?php echo $_SESSION['score']; ?> / <?php echo $total_questions; ?></h2>
                    <form method="post">
                        <button type="submit" name="restart" class="btn btn-success">Recommencer le quiz</button>
                    </form>
                    <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="js/quiz.js"></script>
</body>
</html>
