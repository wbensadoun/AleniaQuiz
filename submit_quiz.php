<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

// Vérification des données POST
if (!isset($_POST['quiz_id']) || !isset($_POST['answers'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

$quiz_id = (int)$_POST['quiz_id'];
$answers = json_decode($_POST['answers'], true);

if (!is_array($answers)) {
    echo json_encode(['success' => false, 'message' => 'Format de réponses invalide']);
    exit();
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "quizzapp");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion']);
    exit();
}

try {
    // Commencer une transaction
    $conn->begin_transaction();

    // Récupérer les réponses correctes
    $question_ids = array_map(function($answer) {
        return $answer['questionId'];
    }, $answers);
    
    $question_ids_str = implode(',', array_map('intval', $question_ids));
    
    $sql = "SELECT id, correct_answer FROM questions WHERE id IN ($question_ids_str)";
    $result = $conn->query($sql);
    
    $correct_answers = [];
    while ($row = $result->fetch_assoc()) {
        $correct_answers[$row['id']] = $row['correct_answer'];
    }

    // Calculer le score
    $score = 0;
    $total_questions = count($answers);
    
    foreach ($answers as $answer) {
        if (isset($correct_answers[$answer['questionId']]) && 
            $answer['answer'] === $correct_answers[$answer['questionId']]) {
            $score++;
        }
    }

    // Insérer le résultat
    $stmt = $conn->prepare("INSERT INTO results (user_id, quiz_id, score, total_questions, completed_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiii", $_SESSION['user_id'], $quiz_id, $score, $total_questions);
    
    if (!$stmt->execute()) {
        throw new Exception("Erreur lors de l'enregistrement du résultat");
    }
    
    $result_id = $conn->insert_id;

    // Valider la transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'result_id' => $result_id,
        'score' => $score,
        'total' => $total_questions
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
