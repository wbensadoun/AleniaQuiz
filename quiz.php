<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour logger les messages
function quizLog($message) {
    if (!isset($_SESSION['quiz_logs'])) {
        $_SESSION['quiz_logs'] = [];
    }
    $_SESSION['quiz_logs'][] = date('Y-m-d H:i:s') . ' - ' . $message;
}

// Fonction pour lire les questions depuis le fichier CSV
function readQuestionsFromCSV() {
    quizLog("=== Début de la lecture du fichier CSV ===");
    $questions = [];
    $csvFile = __DIR__ . '/secure_admin/uploads/questions.csv';
    
    if (!file_exists($csvFile)) {
        quizLog("ERREUR: Le fichier questions.csv n'existe pas dans " . __DIR__ . '/secure_admin/uploads/');
        die("Erreur: Le fichier questions.csv n'existe pas.");
    }
    
    $file = fopen($csvFile, 'r');
    if (!$file) {
        quizLog("ERREUR: Impossible d'ouvrir le fichier CSV");
        die("Erreur: Impossible d'ouvrir le fichier CSV.");
    }
    
    // Ignorer la première ligne (en-têtes)
    fgetcsv($file, 0, ';');
    
    $lineNumber = 1;
    while (($line = fgetcsv($file, 0, ';')) !== FALSE) {
        $lineNumber++;
        quizLog("Lecture ligne $lineNumber: " . json_encode($line, JSON_UNESCAPED_UNICODE));
        
        // Vérifier la validité de la ligne
        if (empty($line[0])) {
            quizLog("ERREUR: Question vide à la ligne $lineNumber");
            continue;
        }
        
        if (count($line) < 7) {
            quizLog("ERREUR: Pas assez d'informations à la ligne $lineNumber (7 colonnes requises)");
            continue;
        }
        
        // Nettoyer les données
        $questionText = trim($line[0]);
        $options = [];
        
        // Traiter les 4 options (colonnes 1 à 4)
        for ($i = 1; $i <= 4; $i++) {
            $option = trim($line[$i]);
            if (preg_match('/([A-D]):(.+)/', $option, $matches)) {
                $letter = $matches[1];
                $text = $matches[2];
                $options[$letter] = trim($text);
            }
        }
        
        $correct_letter = trim($line[5]);
        $scenario = trim($line[6]);
        
        if (count($options) !== 4) {
            quizLog("ERREUR: Nombre incorrect d'options à la ligne $lineNumber");
            continue;
        }
        
        $questions[] = [
            'question' => $questionText,
            'options' => $options,
            'correct_letter' => $correct_letter,
            'scenario' => $scenario
        ];
        
        quizLog("Question ajoutée avec succès: " . json_encode([
            'question' => $questionText,
            'correct_letter' => $correct_letter,
            'optionsCount' => count($options),
            'scenario' => $scenario
        ], JSON_UNESCAPED_UNICODE));
    }
    
    fclose($file);
    
    if (empty($questions)) {
        quizLog("ERREUR: Aucune question n'a été chargée");
        die("Erreur: Aucune question n'a été chargée du fichier CSV.");
    }
    
    quizLog("=== Fin de la lecture du fichier CSV ===");
    quizLog("Nombre total de questions chargées: " . count($questions));
    
    return $questions;
}

// Initialisation ou réinitialisation du quiz
if (!isset($_SESSION['quiz_id']) || isset($_POST['restart'])) {
    quizLog("=== Initialisation/Réinitialisation du quiz ===");
    $_SESSION['quiz_logs'] = []; // Réinitialiser les logs
    
    require_once 'db_connect.php';
    
    // Si un chemin de quiz est spécifié dans l'URL
    if (isset($_GET['path'])) {
        $file_path = $_GET['path'];
        $sql = "SELECT * FROM quizzes WHERE file_path = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $file_path);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $questions = json_decode($row['questions_json'], true);
            $_SESSION['quiz_id'] = $row['id'];
            quizLog("Quiz '{$row['file_name']}' chargé depuis la base de données");
        } else {
            die("Quiz non trouvé");
        }
    }
    // Si un ID de quiz est spécifié dans l'URL
    else if (isset($_GET['id'])) {
        $quiz_id = intval($_GET['id']);
        $sql = "SELECT * FROM quizzes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $questions = json_decode($row['questions_json'], true);
            $_SESSION['quiz_id'] = $row['id'];
            quizLog("Quiz #{$row['id']} chargé depuis la base de données");
        } else {
            die("Quiz non trouvé");
        }
    } else {
        // Créer un nouveau quiz
        $questions = readQuestionsFromCSV();
        shuffle($questions);
        quizLog("Nouveau quiz créé avec questions mélangées");
        
        // Créer une nouvelle instance de quiz dans la base de données
        $questions_json = json_encode($questions, JSON_UNESCAPED_UNICODE);
        $total_questions = count($questions);
        $file_name = "quiz-" . date("Y-m-d-H-i-s") . ".csv";
        $file_path = "quiz-" . date("Y-m-d-H-i-s");
        
        $sql = "INSERT INTO quizzes (file_name, file_path, questions_json, total_questions) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $file_name, $file_path, $questions_json, $total_questions);
        $stmt->execute();
        
        $_SESSION['quiz_id'] = $conn->insert_id;
        quizLog("Nouveau quiz créé avec ID: " . $_SESSION['quiz_id']);
    }
    
    $_SESSION['questions'] = $questions;
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
        quizLog("Réponse correcte à la question " . ($current + 1));
    } else {
        quizLog("Réponse incorrecte à la question " . ($current + 1) . " (Répondu: $answer, Correct: $correct)");
    }
    
    $_SESSION['current_question']++;
}

$current = $_SESSION['current_question'];
$total_questions = count($_SESSION['questions']);
$quiz_completed = $current >= $total_questions;

// Si le quiz est terminé, enregistrer le score
if ($quiz_completed && !isset($_SESSION['score_saved'])) {
    require_once 'db_connect.php';
    
    $user_id = $_SESSION['user_id'];
    $score = $_SESSION['score'];
    $quiz_id = $_SESSION['quiz_id'];
    
    $sql = "INSERT INTO results (user_id, score, total_questions, quiz_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $score, $total_questions, $quiz_id);
    $stmt->execute();
    
    $_SESSION['score_saved'] = true;
    quizLog("Score final enregistré: $score/$total_questions pour le quiz ID: $quiz_id");
    
    // Réinitialiser quiz_id pour forcer la création d'un nouveau quiz la prochaine fois
    unset($_SESSION['quiz_id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Alenia</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php if (!$quiz_completed): ?>
            <div class="progress">
                Question <?php echo $current + 1; ?> sur <?php echo $total_questions; ?>
            </div>
            
            <div class="scenario">
                <?php echo htmlspecialchars($_SESSION['questions'][$current]['scenario']); ?>
            </div>
            
            <div class="question">
                <?php echo htmlspecialchars($_SESSION['questions'][$current]['question']); ?>
            </div>
            
            <form method="post" class="options">
                <?php foreach ($_SESSION['questions'][$current]['options'] as $letter => $text): ?>
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
            <div class="score">
                Score final : <?php echo $_SESSION['score']; ?> / <?php echo $total_questions; ?>
            </div>
            
            <div class="buttons">
                <form method="post">
                    <button type="submit" name="restart" class="btn btn-success">Recommencer le quiz</button>
                </form>
                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>